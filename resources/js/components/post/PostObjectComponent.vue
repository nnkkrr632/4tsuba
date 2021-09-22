<template>
    <!-- 【１】普通のポスト -->
    <v-card
        v-if="post.deleted_at === null && post.has_mute_words === false && post.posted_by_mute_users === false"
        color="light-green lighten-5"
        outlined
        class="my-3"
    >
        <div>
            <!-- 0行目 プロフィールページではスレッド情報を表示 -->
            <router-link 
                style="text-decoration: none;"
                onMouseOut="this.style.textDecoration='none';" 
                onMouseOver="this.style.textDecoration='underline';"
                class="green--text text--lighten-1"
                v-bind:to="{name: 'thread.show', params: {thread_id: post.thread_id}}"
            >            
                <div v-if="need_thread" class=" ml-1 mb-n2">
                    【スレッド】{{post.thread.title}}
                </div>
            </router-link>

            <!-- １行目 -->
            <v-card-text class="d-flex">
                <span v-text="post.displayed_post_id"></span>
                <router-link
                    v-if="post.user"
                    style="text-decoration: none;"
                    onMouseOut="this.style.textDecoration='none';" 
                    onMouseOver="this.style.textDecoration='underline';"                    
                    class="green--text text--lighten-1"
                    v-bind:to="{name: 'user.posts', params: {user_id: post.user_id}}"
                >
                    <span v-text="post.user.name" class="ml-3"></span>
                </router-link>
                <span v-else class="ml-3">退会済みユーザー</span>

                <span v-text="post.created_at" class="ml-3"></span>
                <span v-if="post.is_edited" class=" ml-3 blue-grey--text">(編集済み)</span>

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
                <template v-if="!need_thread">
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <span v-on="on"
                                  @click="emitForAnchor()"
                                ><v-icon class="ml-3 mt-n2"
                                    >mdi-message-arrow-left-outline</v-icon
                                ></span
                            >
                        </template>
                        <span>返信</span>
                    </v-tooltip>
                </template>
                <template v-if="post.user_id === my_info.id || my_info.role === 'staff'">
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <span v-on="on"
                                ><v-icon class="ml-3 mt-n2" @click="editBody"
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
                <template v-if="post.image" >
                    <v-avatar class="ma-3" size="250" tile>
                        <!-- vueのルート publicディレクトリからの相対パスを記入する この場合 public/storage/images/example.png -->
                        <img
                            :src="'/storage/images/' + post.image.image_name"
                            style="object-fit:contain; cursor:zoom-in;"
                            @click="emitForLightBox"
                        />
                    </v-avatar>
                </template>
                <v-card-text>
                    <div v-if="!is_editing">
                        <span v-if="!search" style="white-space:pre-wrap; word-wrap:break-word;" v-text="post.body"></span>
                        <span v-else style="white-space:pre-wrap; word-wrap:break-word;" v-html="post.body_for_search"></span>
                    </div>
                    <!-- 編集中 -->
                    <template v-else>
                        <v-form ref="form" v-model="valid" class="pa-4 ">
                            <v-textarea
                                class="mt-6"
                                v-model="post.body"
                                :counter="limit.body"
                                color="green lightten-2"
                                outlined
                                auto-grow
                                :hint="'必須 & 最大' + limit.body + '文字'"
                                persistent-hint
                                :rules="[rules.required, rules.length_body]"
                            ></v-textarea>
                            <!-- 画像 -->
                            <template v-if="post.image">
                            <v-file-input 
                                v-model="post.image"
                                color="green lightten-2"
                                accept="image/png, image/gif, image/jpg, image/jpeg"
                                label="画像を変更"
                            ></v-file-input>
                            <v-checkbox
                            label="画像を削除する"
                            color="green lighten-2"
                            v-model="post.delete_image"
                            ></v-checkbox>
                            </template>
                            <v-file-input v-else
                                v-model="post.image"
                                color="green lightten-2"
                                accept="image/png, image/gif, image/jpg, image/jpeg"
                                label="画像を追加"
                            ></v-file-input>
                        </v-form>
                        <div class="d-flex justify-end mr-4">
                            <v-btn
                                class="white--text mr-2"
                                color="blue-grey lighten-3"
                                depressed
                                @click="editCancel"
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
                            <span @click="emitForResponses">{{ post.responded_count }}件の返信</span>
                        </router-link>
                    </template>
                </v-card-text>
            </div>
        </div>
    </v-card>
    <!-- 【２】 ミュートワードを含むときのポスト -->
    <v-card v-else-if="post.deleted_at === null && post.has_mute_words === true" color="blue-grey lighten-5" outlined class="my-3">
        <!-- 1行目 -->
        <v-card-text class="d-flex">
            <span v-text="post.displayed_post_id"></span>
            <span class="ml-3">
                <router-link
                    v-bind:to="{
                        name: 'setting.mute_words',
                    }"
                >
                    <span>ミュートワード</span>
                </router-link>
                <span>が含まれています。</span>
            </span>
            <v-btn
                class="grey--text mt-n1"
                color="blue-grey lighten-4"
                depressed
                small
                @click="displayMutedPost"
            >
                書込を表示する
            </v-btn>
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
                        <span @click="emitForResponses">{{ post.responded_count }}件の返信</span>
                    </router-link>
                </template>
            </v-card-text>
        </div>
    </v-card>
    <!-- 【3】 ミュートユーザーによるポスト -->
    <v-card v-else-if="post.deleted_at === null && post.posted_by_mute_users === true" color="blue-grey lighten-5" outlined class="my-3">
        <!-- 1行目 -->
        <v-card-text class="d-flex">
            <span v-text="post.displayed_post_id"></span>
            <span class="ml-3">
                <router-link
                    v-bind:to="{
                        name: 'setting.mute_users',
                    }"
                >
                    <span>ミュートユーザー</span>
                </router-link>
                <span>による書き込みです。</span>
            </span>
            <v-btn
                class="grey--text mt-n1"
                color="blue-grey lighten-4"
                depressed
                small
                @click="displayMutedPost"
            >
                書込を表示する
            </v-btn>
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
                        <span @click="emitForResponses">{{ post.responded_count }}件の返信</span>
                    </router-link>
                </template>
            </v-card-text>
        </div>
    </v-card>
    <!-- 【4】 コメントが削除されたときのポスト -->
    <v-card v-else color="blue-grey lighten-5" outlined class="my-3">
        <!-- 1行目 -->
        <v-card-text class="d-flex">
            <span v-text="post.displayed_post_id"></span>
            <span class="ml-3">書込者が削除しました。</span>
            <!-- 削除済みコメントにはいいねボタンを表示しない。 
            ただし、いいねした後削除したときのみいいねボタンを表示する(はずせるようにするため)。 -->
            <template v-if="post.login_user_liked">
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
            </template>
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
                        <span @click="emitForResponses">{{ post.responded_count }}件の返信</span>
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
        my_info: {
            type: Object,
            required: true
        },
        need_thread: {
            type: Boolean,
            default: false
        },
        search: {
            tyep:Boolean,
            default: false
        }
    },
    data() {
        return {
            true_or_false: Boolean(this.post.login_user_liked),
            is_editing: false,
            before_edit: {},
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
                console.log('thrad_id:' + this.post.thread_id);
                console.log('post_id:' + this.post.id);
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
                        this.$emit("receiveUpdate");
                    })
                    .catch(error => {
                        console.log(error.response.data);
                    });
                //いいね解除
            } else {
                console.log("this is unLike");
                console.log('thrad_id:' + this.post.thread_id);
                console.log('post_id:' + this.post.id);
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
                        this.$emit("receiveUpdate");
                    })
                    .catch(error => {
                        console.log(error.response.data);
                    });

            }
            //Profileページ「いいね」側での変更をemit
            console.log('emitted_post_id:' + this.post.id);
            this.$emit("receiveTrueOrFalse", this.post.id);
            
        },
        //Profileページ「書込」側のいいねマーク変更を反映
        updateTrueOrFalse() {
            console.log('this is updateTrueOrFalse');
            this.true_or_false = !this.true_or_false;
        },
        editBody() {
            console.log("this is editBody");
            this.is_editing = true;
            this.before_edit.body = this.post.body;
            this.before_edit.image = this.post.image;
        },
        editPost() {
            console.log("this is editPost");
            this.is_editing = false;
            const form_data = new FormData();
            form_data.append("thread_id", this.post.thread_id);
            form_data.append("id", this.post.id);
            form_data.append("displayed_post_id", this.post.displayed_post_id);
            form_data.append("body", this.post.body);
            if(this.post.delete_image ) {
                form_data.append("delete_image", this.post.delete_image)
            }
            if(this.post.image !== null) {
                form_data.append("image", this.post.image);
            }
            console.log(form_data);
            axios
                .post("/api/posts/edit", form_data, {
                    headers: { "content-type": "multipart/form-data" }
                })
                .then(response => {
                        if(response.data == 'bad_user') {
                            console.log(response);
                            alert('書込者以外は編集できません。');
                        } else {
                            this.post.is_edited = 1;
                        }
                        this.$emit("receiveUpdate");

                })
                .catch(error => {
                    console.log(error.response.data);
                });

            
        },
        editCancel() {
            console.log("this is editCancel");
            this.post.body = this.before_edit.body;
            this.post.image = this.before_edit.image;
            this.is_editing = false;
        },
        displayMutedPost() {
            console.log('this is displayMutedPost');
            if(this.post.has_mute_words === true) {
                this.post.has_mute_words = false;
            }
            if(this.post.posted_by_mute_users === true) {
                this.post.posted_by_mute_users = false;
            }


        },
        deletePost() {
            console.log("this is deletePost");

            if (confirm("書込を削除しますか？")) {
                axios
                    .delete("/api/posts", {
                        data: {
                            id: this.post.id,
                            thread_id: this.post.thread_id,
                            user_id: this.post.user_id,
                        }
                    })
                    .then(response => {
                        console.log(response.data);
                        if(response.data === 'bad_user') {
                            alert('書込者以外は削除できません。');
                        } else {
                            this.post.deleted_at = 'deleted';
                            this.$emit("receiveUpdate");
                        }
                    })
                    .catch(error => {
                        console.log(error.response.data);
                    });
            }
        },
        emitForResponses() {
            console.log("this is emitForResponses");
            this.$emit("receiveForResponses", this.post.displayed_post_id);
        },
        emitForAnchor() {
            console.log('this is emitForAnchor★'+ this.post.displayed_post_id);
            this.$emit("receiveForAnchor", this.post.displayed_post_id);
        },
        emitForLightBox() {
            console.log('this is emitForLightBox emit ' + this.post.lightbox_index);
            this.$emit("receiveForLightBox", this.post.lightbox_index);
        }
    },
};
</script>
