<template>
    <div>
        <!-- スレッドタイトル部分 -->
        <div @click="getPaginator(1)" style="cursor: pointer;">
        <thread-object-component
            v-bind:thread="thread"
        ></thread-object-component>
        </div>
        <!-- Light Box -->
        <light-box v-if="media.length > 0"
            ref="lightbox"
            :media="media"
            :show-light-box="false"
            :show-caption="true"
        ></light-box>

        <!-- ポスト部分 -->
        <div v-for="(post, index) in paginator.data" :key="post.id">
            <post-object-component
                v-bind:post="post"
                v-bind:index="index"
                v-bind:my_info="my_info"
                @re_get_mainly_posts="updateEntry('update_or_destroy')"
                @receiveForResponses="getPaginatorForResponses"
                @receiveForAnchor="callWriteAnchor"
                @igniteLightBox="showImages"
            >
            </post-object-component>
        </div>
        <!-- ページネーション -->
        <template>
        <div class="text-center">
        <v-pagination
            v-model="paginator.current_page"
            color="green lighten-5"
            :length="paginator.last_page"
        ></v-pagination>
        </div>
        </template>
        <v-divider></v-divider>
        <!-- 書き込み部分 -->
        <create-component
            ref="create"
            @re_get_mainly_posts="updateEntry('post')"
            v-bind:thread_id="thread_id"
        ></create-component>
        <span ref="bottom"></span>
    </div>
</template>

<style>
.v-pagination__navigation {
  box-shadow: none !important;
}

.v-pagination__item {
  box-shadow: none !important;
}
</style>
<script>
import ThreadObjectComponent from "../thread/ThreadObjectComponent.vue";
import PostObjectComponent from "./PostObjectComponent.vue";
import CreateComponent from "../common/CreateComponent.vue";
import LightBox from 'vue-image-lightbox';

export default {
    //このpropsは親コンポーネントではなく、router-linkのparam
    props: {
        thread_id: {
            type: Number,
            required: true
        },
        dest_d_post_id: {
            type: Number,
            default: 1
        }
    },
    data() {
        return {
            my_info: {},
            thread: {},
            // posts: {},
            paginator: {},
            anchor: null,
            media: [],
            page: 1,
        };
    },
    methods: {
        getMyInfo() {
            console.log("this is getMyInfo");
            axios.get("/api/users/me/info").then(res => {
                this.my_info = res.data;
            });
        },
        getThread() {
            console.log("this is getThread");
            axios.get("/api/threads/" + this.thread_id).then(res => {
                this.thread = res.data;
            });
        },
        // getPosts() {
        //     console.log("this is getPosts");
        //     axios
        //         .get("/api/posts", {
        //             params: {
        //                 where: "thread_id",
        //                 value: this.thread_id
        //             }
        //         })
        //         .then(res => {
        //             this.posts = res.data;
        //             this.getThreadImagesForLightBox();
        //         });
        // },
        getPaginator(page_number) {
            console.log("this is getPaginator");
            axios
                .get("/api/posts/paginated", {
                    params: {
                        where: "thread_id",
                        value: this.thread_id,
                        page: page_number,
                    }
                })
                .then(res => {
                    this.paginator = res.data;
                    this.getThreadImagesForLightBox();
                });
        },
        getPaginatorForResponses(emitted_displayed_post_id) {
            console.log("this is getPaginatorForResponses");
            axios
                .get("/api/posts/paginated", {
                    params: {
                        where: "responses",
                        value: [this.thread_id, emitted_displayed_post_id],
                        //pageはなしでOK ない場合リクエストクエリに入らないがLaravel側で自動で1が入ってる？
                    }
                })
                .then(res => {
                    this.paginator = res.data;
                    this.getResponseImagesForLightBox(emitted_displayed_post_id);
                });
        },
        callWriteAnchor(emitted_displayed_post_id) {
            console.log("this is callWriteAnchor");
            this.anchor = '>>' + emitted_displayed_post_id + " ";
            this.$refs.create.writeAnchor(this.anchor);
        },
        updateEntry(driver) {
            this.getPaginator(this.paginator.last_page);
            //スレッドオブジェクト内の書込数/いいね数更新
            this.getThread();
            if(driver == 'post') {
                setTimeout(this.scrollToBottom, 500)
            }
        },
        getThreadImagesForLightBox() {
            console.log('this is getThreadImagesForLightBox');
            axios
                .get("/api/images/threads/" + this.thread_id)
                .then(res => {
                    this.media = res.data;
                });
        },
        getResponseImagesForLightBox(displayed_post_id) {
            console.log('this is getResponseImagesForLightBox');
            axios
                .get("/api/images/threads/" + this.thread_id + '/responses/' + displayed_post_id)
                .then(res => {
                    this.media = res.data;
                });
        },
        showImages(emitted_lightbox_index) {
            this.$refs.lightbox.showImage(emitted_lightbox_index);
        },
        scrollToBottom() {
            console.log('this is scrollToBottom');
            var element = document.documentElement;
            var bottom = element.scrollHeight - element.clientHeight;
            window.scrollTo({top: bottom, behavior: 'smooth'});            
        }
    },
    components: {
        ThreadObjectComponent,
        PostObjectComponent,
        CreateComponent,
        LightBox,
    },
    watch: {
        //ページが変更されるとページネーターを再取得
        'paginator.current_page': function() {
            this.getPaginator(this.paginator.current_page);
        }
    },
    mounted() {
        this.getMyInfo();
        this.getThread();
        //this.getPosts();
        this.getPaginator(1);
    },
};
</script>
