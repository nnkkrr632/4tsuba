<template>
    <div>
        <!-- スレッドタイトル部分 -->
        <div @click="getPosts" style="cursor: pointer;">
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
        <div v-for="(post, index) in posts" :key="post.id">
            <post-object-component
                v-bind:post="post"
                v-bind:index="index"
                v-bind:my_info="my_info"
                @re_get_mainly_posts="updateEntry('update_or_destroy')"
                @receiveForResponses="getResponses"
                @receiveForAnchor="callWriteAnchor"
                @igniteLightBox="showImages"
            >
            </post-object-component>
        </div>

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
            default: 1,
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
            posts: {},
            anchor: null,
            media: [],
            response_map: {},
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
        getPostsOrResponses() {
            console.log('this is getPostsOrResponses');
            let path = this.$route.path;
            let displayed_post_id = path.match(/\d+$/)[0];
            console.log(path);
            console.log(displayed_post_id);
            if(!path.match(/responses/)) {
                this.getPosts();
            } else {
                this.getResponses(displayed_post_id);
            }
        },
        getPosts() {
            console.log("this is getPosts");
            axios
                .get("/api/posts/", {
                    params: {
                        where: "thread_id",
                        value: this.thread_id
                    }
                })
                .then(res => {
                    this.posts = res.data;
                    this.getThreadImagesForLightBox();
                    //this.getResponseMap();
                });
        },
        getResponses(emitted_displayed_post_id) {
            console.log("this is getResponses");
            axios
                .get("/api/posts/", {
                    params: {
                        where: "responses",
                        value: [this.thread_id, emitted_displayed_post_id],
                    }
                })
                .then(res => {
                    this.posts = res.data;
                    this.getResponseImagesForLightBox(emitted_displayed_post_id);
                });
        },
        callWriteAnchor(emitted_displayed_post_id) {
            console.log("this is callWriteAnchor");
            this.anchor = '>>' + emitted_displayed_post_id + " ";
            this.$refs.create.writeAnchor(this.anchor);
        },
        updateEntry(driver) {
            console.log('this is updateEntry');
            this.getPosts();
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
    mounted() {
        this.getMyInfo();
        this.getThread();
        this.getPosts();
    },
};
</script>
