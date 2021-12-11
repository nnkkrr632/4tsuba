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
                @updateEntry="getSearchedPosts"                
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
import jaconv from 'jaconv';

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
            if(this.search_string) {
                console.log("this is getSearchedPosts");
                console.log(this.search_string);
                console.log(typeof this.search_string);
                const search_word_list = this.search_string.trim().split(/[(\s)|(\t)]+/);
                console.log(search_word_list);
                //重複削除
                const set_word_list = new Set(search_word_list);
                this.unique_word_list = Array.from(set_word_list);
                console.log(this.unique_word_list);
                if(this.unique_word_list != null) {
                axios
                    .get("/api/posts", {
                        params: {
                            where: "search",
                            value: this.unique_word_list
                        }
                    })
                    .then(res => {
                        this.posts = res.data;
                        this.highlightSearchWord();
                        this.getImagesForLightBox();
                    })
                    .catch(error => {
                        console.log(error.response);
                        if(error.response.status === 422) {
                            let alert_array = Object.values(error.response.data.errors);
                            alert(alert_array.flat().join().replace(/,/g, '\n'));
                        }
                    });
                }
            }   
        },
        highlightSearchWord() {
            console.log("this is highlightSearchWord");
            let unique_all_kana_list = this.exchangeUniqueWordListIntoAllKanaList(this.unique_word_list);
            const unique_word_string = unique_all_kana_list.join('|');
            let regular_expressiion = new RegExp('(' + unique_word_string + ')', 'gi');
            console.log(regular_expressiion);

            this.posts.forEach(function(post) {
                post['body_for_search'] = post['body'].replace(regular_expressiion, '<span class="green lighten-3 font-weight-bold">$&</span>');
            })
        },
        getImagesForLightBox() {
            console.log('this is getImagesForLightBox');
            axios
                .get("/api/images/search", {
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
        },
        exchangeUniqueWordListIntoAllKanaList(unique_word_list) {
            console.log('this is exchangeUniqueWordListIntoAllKanaList');
            let all_kana_list = [];
            unique_word_list.forEach( function (unique_word) {
                let hiragana = jaconv.toHiragana(unique_word);
                let katakana = jaconv.toKatakana(unique_word);
                let han_katakana = jaconv.toHanKana(unique_word);
                let han_katakana_2 = jaconv.toHanKana(jaconv.toKatakana(unique_word));
                let array = [hiragana, katakana, han_katakana, han_katakana_2];
                console.log(array);
                all_kana_list.push(array);
            });
            all_kana_list = all_kana_list.flat();
            console.log(all_kana_list);
            //重複削除
                let set_list = new Set(all_kana_list);
                let unique_all_kana_list = Array.from(set_list);
                console.log(unique_all_kana_list);
                return unique_all_kana_list;
        },
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
