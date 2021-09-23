<template>
    <div>
        <headline-component v-bind:headline="headline"></headline-component>

        <v-card flat class="ml-4">
            <v-toolbar color="green lighten-5" flat>
                <v-list-item-avatar class="ml-n3 mr-3" size="50" tile>
                    <img
                        :src="'/storage/icons/' + user_info.icon_name"
                        style="object-fit: cover;"
                    />
                </v-list-item-avatar>
                <v-toolbar-title class="green--text">{{ user_info.name }}</v-toolbar-title>
                <v-spacer></v-spacer>
                <!-- 自分のプロフィールはプロフィール編集ボタン -->
                <v-btn v-if="my_info.id == user_id"
                        class="white--text"
                        color="green lighten-2"
                        depressed
                        to=/setting/account/profile
                >
                        表示プロフィールを変更
                </v-btn>
                <!-- 他人のプロフィールはミュートボタン -->
                <template v-else>
                    <!-- ミュートしていない場合、ミュートボタン -->                    
                    <v-tooltip v-if="!user_info.is_login_user_mute" bottom>
                        <template v-slot:activator="{ on }">
                            <span v-on="on"
                                ><v-icon
                                    class="ml-3 mt-n2"
                                    color="green lighten-2"
                                    @click="switchMute()"
                                    >mdi-volume-high</v-icon
                                ></span
                            >
                        </template>
                        <span>ミュート</span>
                    </v-tooltip>
                    <!-- ミュートしている場合、ミュート解除ボタン -->                    
                    <v-tooltip v-else bottom>
                        <template v-slot:activator="{ on }">
                            <span v-on="on"
                                ><v-icon
                                    class="ml-3 mt-n2"
                                    color="red lighten-2"
                                    @click="switchMute()"
                                    >mdi-volume-off</v-icon
                                ></span
                            >
                        </template>
                        <span>ミュート解除</span>
                    </v-tooltip>
                </template>

                <template v-slot:extension>
                    <!-- タブ -->
                    <v-tabs
                        color="green lighten-2"
                        v-model="tab"
                        icons-and-text
                        light
                    >
                        <v-tabs-slider></v-tabs-slider>
                        <!-- @clickにtab.methodのようにしようとしたいができない -->
                        <v-tab :to="tabs[0].link" @click="updatePosts">
                            {{ tabs[0].name }}
                            <v-icon>{{ tabs[0].icon }}</v-icon>
                        </v-tab>
                        <v-tab :to="tabs[1].link" @click="updatePosts">
                            {{ tabs[1].name }}
                            <v-icon>{{ tabs[1].icon }}</v-icon>
                        </v-tab>
                    </v-tabs>
                </template>
            </v-toolbar>
            <!-- Light Box -->
            <light-box v-if="posted_media.length > 0"
                ref="lightbox_for_post"
                :media="posted_media"
                :show-light-box="false"
                :show-caption="true"
            ></light-box>
            <light-box v-if="liked_media.length > 0"
                ref="lightbox_for_like"
                :media="liked_media"
                :show-light-box="false"
                :show-caption="true"
            ></light-box>

            <!-- タブ中身s -->
            <v-tabs-items v-model="tab">
                <!-- タブ中身 書込 -->
                <v-tab-item value="posts">
                <div class="my-2 green--text text--lighten-1">{{user_info.posts_count}}件の書込</div>
                  
                    <div v-for="(post, index) in user_posts" :key="post.id">
                        <post-object-component
                            v-bind:post="post"
                            v-bind:index="index"
                            v-bind:my_info="my_info"
                            v-bind:need_thread="true"
                            @receiveForLightBox="showImagesForPost"
                            ref="child"
                        >
                        </post-object-component>
                    </div>
                </v-tab-item>

                <!-- タブ中身 いいね -->
                <v-tab-item value="likes">
                <div class="my-2 green--text text--lighten-1">{{user_info.likes_count}}件のいいね</div>                
                    <div
                        v-for="(post, index) in user_like_posts"
                        :key="post.id"
                    >
                        <post-object-component
                            v-bind:post="post"
                            v-bind:index="index"
                            v-bind:my_info="my_info"
                            v-bind:need_thread="true"
                            @receiveTrueOrFalse="callUpdateTrueOrFalse"
                            @receiveForLightBox="showImagesForLike"
                        >
                        </post-object-component>
                    </div>
                </v-tab-item>

                <!-- タブ中身 お気に入りスレッド -->
                <v-tab-item value="threads">
                    <v-card flat>
                        <v-card-text>ccc</v-card-text>
                    </v-card>
                </v-tab-item>
            </v-tabs-items>
        </v-card>
    </div>
</template>

<script>
import HeadlineComponent from "../common/HeadlineComponent.vue";
import PostObjectComponent from "../post/PostObjectComponent.vue";
import LightBox from 'vue-image-lightbox';

export default {
    data() {
        return {
            headline: "ユーザープロフィール",
            my_info: {},
            user_id: this.$route.params.user_id,
            user_info: {},
            user_posts: null,
            user_like_posts: null,
            posted_media: [],
            liked_media: [],
            tab: null,
            tabs: [
                {
                    name: "書込",
                    icon: "mdi-post",
                    link: "posts"
                },
                {
                    name: "いいね",
                    icon: "mdi-heart",
                    link: "likes"
                },
                {
                    name: "ブックマークスレッド",
                    icon: "mdi-star",
                    link: "threads"
                }
            ]
        };
    },
    components: {
        HeadlineComponent,
        PostObjectComponent,
        LightBox,
    },
    methods: {
        getMyInfo() {
            console.log("this is getMyInfo");
            axios.get("/api/users/me/info").then(res => {
                this.my_info = res.data;
            });
        },
        getUserInfo() {
            console.log("this is getUserInfo");
            console.log([this.user_id]);
            axios.get("/api/users/", {
                params: {
                    user_id_list: [this.user_id]
                }
            }).then(res => {
                this.user_info = res.data[0];
            });
        },
        updateUserId() {
            console.log('this is updateUserId');
            this.user_id = this.$route.params.user_id;
        },
        getUserPosts() {
            console.log("this is getUserPosts");
            axios
                .get("/api/posts/", {
                    params: {
                        where: "user_id",
                        value: this.user_id
                    }
                })
                .then(res => {
                    this.user_posts = res.data;
                    this.getPostedImagesForLightBox();
                });
        },
        getUserLikePosts() {
            console.log("this is getUserLikePosts");
            axios
                .get("/api/posts/", {
                    params: {
                        where: "user_like",
                        value: this.user_id
                    }
                })
                .then(res => {
                    this.user_like_posts = res.data;
                    this.getLikedImagesForLightBox();
                });
        },
        updatePosts(emitted_post_id) {
          this.getUserLikePosts();
          this.getUserPosts();
        },
        switchMute() {
            console.log('this is switchMute');

            //ミュートする場合
            if(!this.user_info.is_login_user_mute) {
                if(confirm("このユーザーをミュートしますか？")) {
                this.user_info.is_login_user_mute = 1;
                axios
                    .put("/api/mute_users", {
                        user_id: this.user_id
                    })
                    .then(response => {
                        console.log(response);
                        console.log("ユーザーミュート登録完了");
                        this.getUserPosts();
                    })
                    .catch(error => {
                        console.log(error.response.data);
                    });

                }
            } 
            //ミュート解除する場合
            else {
                if(confirm("ユーザーのミュートを解除しますか？")) {
                this.user_info.is_login_user_mute = 0;
                console.log(this.user_id);
                axios
                    .delete("/api/mute_users", {
                        data: {
                        user_id: this.user_id
                        }

                    })
                    .then(response => {
                        console.log(response);
                        console.log("ユーザーミュート解除完了");
                        this.getUserPosts();
                    })
                    .catch(error => {
                        console.log(error.response.data);
                    });

                }
            }
            
        },
        //自分のプロフィールだった場合、「いいね」側でのハートマークの変更を「書込」側のハートマークへ反映
        callUpdateTrueOrFalse(emitted_post_id) {
            if(this.my_info.id == this.user_id) {
                console.log('this is callUpdateTrueOrFalse');
                for(let i=0; i<this.$refs.child.length; i++) {
                    if(this.$refs.child[i].post.id == emitted_post_id) {
                        console.log('identified,' + this.$refs.child[i].post.id);
                        this.$refs.child[i].updateTrueOrFalse();
                    }
                }
                this.updatePosts();
            }
        },
        getPostedImagesForLightBox() {
            console.log('this is getPostedImagesForLightBox');
            axios
                .get("/api/images/users/" + this.user_id + "/post")
                .then(res => {
                    this.posted_media = res.data;
                });
        },
        getLikedImagesForLightBox() {
            console.log('this is getLikedImagesForLightBox');
            axios
                .get("/api/images/users/" + this.user_id + "/like")
                .then(res => {
                    this.liked_media = res.data;
                });
        },
        showImagesForPost(emitted_lightbox_index) {
            this.$refs.lightbox_for_post.showImage(emitted_lightbox_index);
        },
        showImagesForLike(emitted_lightbox_index) {
            this.$refs.lightbox_for_like.showImage(emitted_lightbox_index);
        }

    },
    mounted() {
        this.getMyInfo();
        this.getUserInfo();
        this.getUserPosts();
        this.getUserLikePosts();
    },
    watch: {
        $route(to,from) {
            this.updateUserId();
            this.getUserInfo();
            this.getUserPosts();
        }
    }
};
</script>
