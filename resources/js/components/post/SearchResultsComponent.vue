<template>
    <div>
        <template v-if="unique_word_list">
        <headline-component v-bind:headline="'検索結果：'+ posts.length + '件  「' + unique_word_list.join('」OR「') + '」'">
        </headline-component>
        </template>

        <!-- LightBox -->
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
                v-bind:need_thread="true"
                v-bind:search="true"
                @igniteLightBox="showImages"
            >
            </post-object-component>
        </div>

        <v-divider></v-divider>
    </div>
</template>

<script>
import HeadlineComponent from "../common/HeadlineComponent.vue";
import PostObjectComponent from "./PostObjectComponent.vue";
import LightBox from 'vue-image-lightbox';

export default {
    //このpropsは親コンポーネントではなく、router-linkのparam
    props: {
        search_string: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            my_info: {},
            thread: {},
            posts: {},
            unique_word_list: null,
            media: [],
        };
    },
    methods: {
        getMyInfo() {
            console.log("this is getMyInfo");
            axios.get("/api/users/me/info").then(res => {
                this.my_info = res.data;
            });
        },
        getSearchedPosts() {
            if(Boolean(this.search_string)) {
                console.log("this is getSearchedPosts");
                console.log(this.search_string);
                //const trimed_search_string = this.search_string.trim()
                const search_word_list = this.search_string.trim().split(/[(\s)|(\t)]+/);
                console.log(search_word_list);
                //重複削除
                const set_word_list = new Set(search_word_list);
                this.unique_word_list = Array.from(set_word_list);
                console.log(this.unique_word_list);
                axios
                    .get("/api/posts/", {
                        params: {
                            where: "search",
                            value: this.unique_word_list
                        }
                    })
                    .then(res => {
                        this.posts = res.data;
                        this.highlightSearchWord();
                        this.getImagesForLightBox();
                    });
            }   
        },
        highlightSearchWord() {
            console.log("this is highlightSearchWord");
            const unique_word_string = this.unique_word_list.join('|');
            let regular_expressiion = new RegExp('(' + unique_word_string + ')', 'gi');
            console.log(regular_expressiion);

            this.posts.forEach(function(post) {
                post['body_for_search'] = post['body'].replace(regular_expressiion, '<span class="green lighten-3 font-weight-bold">$&</span>');
            })
        },
        getImagesForLightBox() {
            console.log('this is getImagesForLightBox');
            axios
                .get("/api/images/search/", {
                    params: {
                        unique_word_list: this.unique_word_list
                    }
                })
                .then(res => {
                    this.media = res.data;
                });
        },
        showImages(emitted_lightbox_index) {
            this.$refs.lightbox.showImage(emitted_lightbox_index);
        }
    },
    watch: {
        search_string: function() {
            this.getSearchedPosts();
            this.getImagesForLightBox();
        }
    },
    components: {
        HeadlineComponent,
        PostObjectComponent,
        LightBox,
    },
    mounted() {
        this.getMyInfo();
        this.getSearchedPosts();
    },
};
</script>
