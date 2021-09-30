<template>

    <div>
        <!-- スレッドタイトル部分 -->
        <div @click="getPosts" style="cursor: pointer;">
        <thread-object-component
            v-bind:thread="thread"
        ></thread-object-component>
        </div>
        <v-btn @click="showImages"></v-btn>
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
                @re_get_mainly_posts="updateEntry"
                @receiveForResponses="getResponses"
                @receiveForAnchor="transferAnchor"
                @igniteLightBox="showImages"
            >
            </post-object-component>
        </div>

        <v-divider></v-divider>
        <!-- 書き込み部分 -->
        <create-component
            @re_get_mainly_posts="updateEntry"
            v-bind:anchor="anchor"
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
        transferAnchor(emitted_displayed_post_id) {
            console.log("this is transferAnchor");
            this.anchor = '>>' + emitted_displayed_post_id + " ";
        },
        updateEntry() {
            console.log('this is updateEntry');
            this.getPosts();
            this.getThread();
            this.scrollToEnd();
        },
        scrollToEnd() {
            console.log('this is scrollToEnd');
            const el = this.$refs.bottom;
            el.scrollIntoView({behavior: 'smooth'});
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
        //宛先表示 一旦中止
        // getResponseMap() {
        //     console.log('this is getResponseMap');
        //     axios
        //         .get("/api/threads/" + this.thread_id + "/responses")
        //         .then(res => {
        //             this.response_map = res.data;
        //             this.InsertResponseMapIntoPosts();
        //         });
        // },
        // InsertResponseMapIntoPosts() {
        //     console.log('this is InsertResponseMapIntoPosts');

        //     for(let i = 0; i<this.response_map.length; i++) {
        //         // this.posts.forEach(function(post) {
        //         for(let j = 0; j<this.posts.length; j++) {
        //             let to_body = this.response_map[i]['to_body'];
        //             let from = this.response_map[i]['from'];
        //             let to = this.response_map[i]['to'];
        //             //from側にtoリストを作成
        //             if(this.posts[j]['displayed_post_id'] == from) {
        //                 if(!this.posts[j]['to_list']) {
        //                 this.posts[j]['to_list'] = {[to]:to_body};
        //                 }else {
        //                     Object.assign(this.posts[j]['to_list'], {[to]:to_body});
        //                 }
        //             }

        //         }
        //             //returnBodyがなぜか呼び出せない。これできれば最高なのに
        //             // //from側にtoリストを作成
        //             // if(post['displayed_post_id'] == from) {
        //             //     if(!post['to_list']) {
        //             //     post['to_list'] = {to: this.returnBody(to)};
        //             //     }else {
        //             //         post['to_list'].to = this.returnBody(to);
        //             //     }
        //             // }
        //             // //to側にfromリストを作成
        //             // if(post['displayed_post_id'] == to) {
        //             //     if(!post['from_list']) {
        //             //     post['from_list'] = {from: this.returnBody(from)};
        //             //     }else {
        //             //         post['from_list'].from = this.returnBody(from);
        //             //     }
        //             // }
        //         }
        //    },
        // returnBody(displayed_post_id) {
        //     posts.forEach(function(post) {
        //         if(post['displayed_post_id'] == displayed_post_id) {
        //             return post['body'];
        //         } else {
        //             return null;
        //         }
        //     }) 
        // },

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
