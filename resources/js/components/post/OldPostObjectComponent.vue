<template>
    <v-card
        v-if="post.deleted_at === null"
        color="light-green lighten-5"
        outlined
        class="my-3"
    >
        <div>
            <!-- １行目 -->
            <v-card-text class="d-flex">
                <span v-html="post.displayed_post_id"></span>
                <span v-html="post.user.name" class="ml-3"></span>
                <span v-html="post.created_at" class="ml-3"></span>

                <v-spacer></v-spacer>
                <v-checkbox
                    color="green lighten-2"
                    on-icon="mdi-heart"
                    off-icon="mdi-heart-outline"
                    v-model="true_or_false"
                    class="ml-4 mt-n2 d-inline"
                    :label="String(post.likes_count)"
                    @click="switchLike()"
                ></v-checkbox>
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }">
                        <span v-on="on"
                            ><v-icon class="ml-3 mt-n2"
                                >mdi-message-arrow-left-outline</v-icon
                            ></span
                        >
                    </template>
                    <span>返信</span>
                </v-tooltip>
                <template v-if="post.user_id === my_id">
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <span v-on="on"
                                ><v-icon class="ml-3 mt-n2" @click="editPost"
                                    >mdi-lead-pencil</v-icon
                                ></span
                            >
                        </template>
                        <span>編集</span>
                    </v-tooltip>
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <span v-on="on"
                                ><v-icon
                                    class="ml-3 mt-n2"
                                    @click="deletePost"
                                    >mdi-delete</v-icon
                                ></span
                            >
                        </template>
                        <span>削除</span>
                    </v-tooltip>
                </template>
            </v-card-text>

            <!-- ２行目 -->
            <div class="d-flex mt-n10">
                <template v-if="post.image">
                    <v-avatar class="ma-3" size="250" tile>
                        <!-- vueのルート publicディレクトリからの相対パスを記入する この場合 public/storage/images/example.png -->
                        <img
                            :src="'/storage/images/' + post.image.image_name"
                            style="object-fit: contain;"
                        />
                    </v-avatar>
                </template>
                <v-card-text>
                    <div v-if="!is_editing">{{ post.body }}</div>
                    <template v-else>
                        <v-form ref="form" v-model="valid" class="pa-4 ">
                            <v-textarea
                                class="mt-6"
                                :value="post.body"
                                :ref="post.id"
                                :counter="limit.body"
                                color="green lightten-2"
                                outlined
                                auto-grow
                                :hint="'必須 & 最大' + limit.body + '文字'"
                                persistent-hint
                                :rules="[rules.required, rules.length_body]"
                            ></v-textarea>
                        </v-form>
                        <div class="d-flex justify-end mr-4">
                            <v-btn
                                class="white--text mr-2"
                                color="blue-grey lighten-3"
                                depressed
                                @click="is_editing = false"
                            >
                                キャンセル
                            </v-btn>
                            <v-btn
                                :disabled="!valid"
                                class="white--text"
                                color="green lighten-2"
                                depressed
                                @click="editPost"
                            >
                                変更を保存
                            </v-btn>
                        </div>
                    </template>

                    <template v-if="post.responded_count">
                        <v-icon>mdi-message-arrow-left</v-icon>
                        <router-link
                            v-bind:to="{
                                name: 'thread.responses',
                                params: {
                                    thread_id: post.thread_id,
                                    displayed_post_id: post.displayed_post_id
                                }
                            }"
                        >
                            <span>{{ post.responded_count }}件の返信</span>
                        </router-link>
                    </template>
                </v-card-text>
            </div>
        </div>
    </v-card>
    <v-card v-else color="blue-grey lighten-5" outlined class="my-3">
        <!-- 1行目 -->
        <v-card-text class="d-flex">
            <span v-html="post.displayed_post_id"></span>
            <span class="ml-3">書込者が削除しました。</span>
        </v-card-text>
        <!-- 2行目 -->
        <div class="d-flex mt-n8">
            <v-card-text>
                <template v-if="post.responded_count">
                    <v-icon>mdi-message-arrow-left</v-icon>
                    <router-link
                        v-bind:to="{
                            name: 'thread.responses',
                            params: {
                                thread_id: post.thread_id,
                                displayed_post_id: post.displayed_post_id
                            }
                        }"
                    >
                        <span>{{ post.responded_count }}件の返信</span>
                    </router-link>
                </template>
            </v-card-text>
        </div>
    </v-card>
</template>

<script>
export default {
    props: {
        post: {
            type: Object,
            required: true
        },
        index: {
            type: Number,
            required: true
        },
        my_id: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            true_or_false: Boolean(this.post.login_user_liked),
            is_editing: false,
            limit: { body: 20 },
            valid: null,
            rules: {
                required: value => !!value || "必ず入力してください",
                //「value &&」がないと初期状態(すなわちvalue = null)のとき、valueが読み取れませんとエラーが出る
                length_body: value =>
                    (value && value.length <= this.limit.body) ||
                    this.limit.body + "文字以内で入力してください"
            }
        };
    },
    methods: {
        switchLike() {
            //チェックボックスを押すと false → true になってこの処理(いいね登録)が走る
            if (this.true_or_false) {
                console.log("this is Like");
                console.log(this.post.thread_id);
                console.log(this.post.id);
                axios
                    .put("/api/like", {
                        thread_id: this.post.thread_id,
                        post_id: this.post.id
                    })
                    .then(response => {
                        console.log(response);
                        console.log("いいね登録");
                        this.post.likes_count++;
                        this.post.login_user_liked++;
                    })
                    .catch(error => {
                        console.log(error.response.data);
                    });
                //いいね解除
            } else {
                console.log("this is unLike");
                console.log(this.post.thread_id);
                console.log(this.post.id);
                axios
                    .delete("/api/like", {
                        data: {
                            thread_id: this.post.thread_id,
                            post_id: this.post.id
                        }
                    })
                    .then(response => {
                        console.log(response);
                        console.log("いいね解除");
                        this.post.likes_count--;
                        this.post.login_user_liked--;
                    })
                    .catch(error => {
                        console.log(error.response.data);
                    });

                this.$emit("receiveFalse", false);
            }
        },
        async editPost() {
            console.log("this is editPost");
            this.post.body = this.$refs[this.post.id];
            console.log(this.post.body);
            this.is_editing = false;
        },
        deletePost() {
            console.log("this is deletePost");

            if (confirm("書込を削除しますか？")) {
                axios
                    .delete("/api/posts", {
                        data: {
                            id: this.post.id,
                            thread_id: this.post.thread_id,
                            user_id: this.post.user_id
                        }
                    })
                    .then(response => {
                        console.log(response);
                        console.log("書込削除");
                        this.post.deleted_at = 'deleted';
                    })
                    .catch(error => {
                        console.log(error.response.data);
                    });
            }
        }
    }
};
</script>

