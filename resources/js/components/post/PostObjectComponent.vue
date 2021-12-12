<template>
    <!-- 【１】普通のポスト -->
    <v-card
        v-if="
            post.deleted_at === null &&
                post.has_mute_words === false &&
                post.posted_by_mute_users === false
        "
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
                v-bind:to="{
                    name: 'thread.show',
                    params: { thread_id: post.thread_id }
                }"
            >
                <div v-if="need_thread" class=" ml-1 mb-n2">
                    【スレッド】{{ post.thread.title }}
                </div>
            </router-link>

            <!-- １行目 -->
            <v-card-text class="d-block d-sm-flex">
                <template>
                    <span v-text="post.displayed_post_id"></span>
                    <router-link
                        v-if="post.user"
                        style="text-decoration: none;"
                        onMouseOut="this.style.textDecoration='none';"
                        onMouseOver="this.style.textDecoration='underline';"
                        class="green--text text--lighten-1"
                        v-bind:to="{
                            name: 'user.posts',
                            params: { user_id: post.user_id }
                        }"
                    >
                        <span
                            v-text="post.user.name"
                            class="ml-3"
                        ></span>
                    </router-link>
                    <span v-else class="ml-3"
                        >退会済みユーザー</span
                    >

                    <span
                        v-text="post.created_at"
                        class="d-none d-sm-inline ml-3"
                    ></span>
                    <span v-if="post.is_edited" class=" ml-3 blue-grey--text"
                        >(編集済み)</span
                    >
                </template>
                <v-spacer></v-spacer>
                <!-- 1行目4アイコン -->
                <v-layout justify-end>
                <template class="d-inline">
                    <template v-if="post.login_user_liked">
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <span v-on="on"
                                    ><v-icon
                                        class="ml-2 mt-n2"
                                        color="green lighten-2"
                                        @click="dislike()"
                                        >mdi-heart</v-icon
                                    ></span
                                >
                            </template>
                            <span>いいね解除</span>
                        </v-tooltip>
                    </template>
                    <template v-else>
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <span v-on="on"
                                    ><v-icon class="ml-2 mt-n2" @click="like()"
                                        >mdi-heart-outline</v-icon
                                    ></span
                                >
                            </template>
                            <span>いいね</span>
                        </v-tooltip>
                    </template>
                    <span class="mt-n1 ml-1">{{ post.likes_count }}</span>
                    <template v-if="!need_thread">
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <span v-on="on"
                                    ><v-icon
                                        class="ml-2 mt-n2"
                                        @click="emitForAnchor()"
                                        >mdi-message-arrow-left-outline</v-icon
                                    ></span
                                >
                            </template>
                            <span>返信</span>
                        </v-tooltip>
                    </template>
                    <template
                        v-if="
                            post.user_id === my_info.id ||
                                my_info.role === 'staff'
                        "
                    >
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <span v-on="on"
                                    ><v-icon
                                        class="ml-2 mt-n2"
                                        @click="editBody"
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
                                        class="ml-2 mt-n2"
                                        @click="deletePost"
                                        >mdi-delete</v-icon
                                    ></span
                                >
                            </template>
                            <span>削除</span>
                        </v-tooltip>
                    </template>
                </template>
                </v-layout>
            </v-card-text>

            <!-- ２行目 -->
            <div class="d-block d-sm-flex mt-n5">
                <template v-if="post.image">
                    <v-avatar class="ma-3" size="170" tile>
                        <!-- vueのルート publicディレクトリからの相対パスを記入する この場合 public/storage/images/example.png -->
                        <img
                            :src="'/storage/images/' + post.image.image_name"
                            style="object-fit:contain; cursor:zoom-in;"
                            @click="emitLightBoxIndex"
                        />
                    </v-avatar>
                </template>
                <v-card-text>
                    <div v-if="!is_editing">
                        <span
                            v-if="!search"
                            style="white-space:pre-wrap; word-wrap:break-word;"
                            v-text="post.body"
                        ></span>
                        <span
                            v-else
                            style="white-space:pre-wrap; word-wrap:break-word;"
                            v-html="post.body_for_search"
                        ></span>
                    </div>
                    <!-- 編集中 -->
                    <template v-else>
                        <v-form ref="form" class="pa-4 ">
                            <v-textarea
                                class="mt-6"
                                v-model="post.body"
                                :counter="limit.body"
                                color="green lightten-2"
                                outlined
                                auto-grow
                                :hint="'必須 & 最大' + limit.body + '文字'"
                                persistent-hint
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
                            <v-file-input
                                v-else
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
                            <span @click="emitForResponses"
                                >{{ post.responded_count }}件の返信</span
                            >
                        </router-link>
                    </template>
                </v-card-text>
            </div>
        </div>
    </v-card>
    <!-- 【２】 ミュートワードを含むときのポスト -->
    <v-card
        v-else-if="post.deleted_at === null && post.has_mute_words === true"
        color="blue-grey lighten-5"
        outlined
        class="my-3"
    >
        <!-- 1行目 -->
        <v-card-text class="d-block d-sm-flex">
            <span v-text="post.displayed_post_id"></span>
            <span class="ml-3">
                <router-link
                    v-bind:to="{
                        name: 'setting.mute_words'
                    }"
                >
                    <span>ミュートワード</span> </router-link
                >が含まれています。
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
                        <span @click="emitForResponses"
                            >{{ post.responded_count }}件の返信</span
                        >
                    </router-link>
                </template>
            </v-card-text>
        </div>
    </v-card>
    <!-- 【3】 ミュートユーザーによるポスト -->
    <v-card
        v-else-if="
            post.deleted_at === null && post.posted_by_mute_users === true
        "
        color="blue-grey lighten-5"
        outlined
        class="my-3"
    >
        <!-- 1行目 -->
        <v-card-text class="d-block d-sm-flex">
            <span v-text="post.displayed_post_id"></span>
            <span class="ml-3">
                <router-link
                    v-bind:to="{
                        name: 'setting.mute_users'
                    }"
                >
                    <span>ミュートユーザー</span> </router-link
                >による書き込みです。
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
                        <span @click="emitForResponses"
                            >{{ post.responded_count }}件の返信</span
                        >
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
            <v-spacer></v-spacer>
            <!-- 削除済みコメントにはいいねボタンを表示しない。 
            ただし、いいねした後削除したときのみいいねボタンを表示する(はずせるようにするため)。 -->
            <template v-if="post.login_user_liked">
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }">
                        <span v-on="on"
                            ><v-icon
                                class="ml-3 mt-n2"
                                color="green lighten-2"
                                @click="dislike()"
                                >mdi-heart</v-icon
                            ></span
                        >
                    </template>
                    <span>いいね解除</span>
                </v-tooltip>
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
                        <span @click="emitForResponses"
                            >{{ post.responded_count }}件の返信</span
                        >
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
            tyep: Boolean,
            default: false
        }
    },
    data() {
        return {
            is_editing: false,
            before_edit: {},
            limit: { body: 200 }
        };
    },
    methods: {
        like() {
            console.log("this is like");
            this.post.login_user_liked = 1;
            this.post.likes_count++;
            axios
                .put("/api/like", {
                    thread_id: this.post.thread_id,
                    post_id: this.post.id
                })
                .then(response => {
                    console.log(response);
                })
                .catch(error => {
                    console.log(error.response);
                    if (error.response.status === 422) {
                        let alert_array = Object.values(
                            error.response.data.errors
                        );
                        alert(
                            alert_array
                                .flat()
                                .join()
                                .replace(/,/g, "\n")
                        );
                        this.$emit("updateEntry");
                    }
                });
        },
        dislike() {
            console.log("this is dislike");
            this.post.login_user_liked = 0;
            this.post.likes_count--;
            axios
                .delete("/api/like", {
                    data: {
                        thread_id: this.post.thread_id,
                        post_id: this.post.id
                    }
                })
                .then(response => {
                    console.log(response);
                    this.$emit("re_get_posts_at_my_profile_like");
                })
                .catch(error => {
                    console.log(error.response);
                    if (error.response.status === 422) {
                        let alert_array = Object.values(
                            error.response.data.errors
                        );
                        alert(
                            alert_array
                                .flat()
                                .join()
                                .replace(/,/g, "\n")
                        );
                        this.$emit("updateEntry");
                    }
                });
        },
        editBody() {
            console.log("this is editBody");
            this.is_editing = true;
            this.before_edit.body = this.post.body;
            this.before_edit.image = this.post.image;
        },
        editPost() {
            console.log("this is editPost");
            const form_data = new FormData();
            form_data.append("thread_id", this.post.thread_id);
            form_data.append("id", this.post.id);
            form_data.append("displayed_post_id", this.post.displayed_post_id);
            form_data.append("body", this.post.body);
            if (this.post.delete_image) {
                form_data.append("delete_image", this.post.delete_image);
            }
            if (this.post.image !== null) {
                form_data.append("image", this.post.image);
            }
            //確認
            for (let value of form_data.entries()) {
                console.log(value);
            }
            axios
                .post("/api/posts/edit", form_data, {
                    headers: { "content-type": "multipart/form-data" }
                })
                .then(response => {
                    console.log(response);
                    this.post.is_edited = 1;
                    this.is_editing = false;
                    this.$emit("updateEntry", 'edit');
                })
                .catch(error => {
                    console.log(error.response);
                    if (error.response.status === 422) {
                        let alert_array = Object.values(
                            error.response.data.errors
                        );
                        alert(
                            alert_array
                                .flat()
                                .join()
                                .replace(/,/g, "\n")
                        );
                    }
                });
        },
        editCancel() {
            console.log("this is editCancel");
            this.post.body = this.before_edit.body;
            this.post.image = this.before_edit.image;
            this.is_editing = false;
        },
        displayMutedPost() {
            console.log("this is displayMutedPost");
            if (this.post.has_mute_words === true) {
                this.post.has_mute_words = false;
            }
            if (this.post.posted_by_mute_users === true) {
                this.post.posted_by_mute_users = false;
            }
        },
        deletePost() {
            console.log("this is deletePost");

            if (confirm("書込を削除しますか？")) {
                axios
                    .delete("/api/posts", {
                        data: {
                            id: this.post.id
                        }
                    })
                    .then(response => {
                        console.log(response.data);
                        this.post.deleted_at = "deleted";
                        this.$emit("updateEntry", 'delete');
                    })
                    .catch(error => {
                        console.log(error.response);
                        if (error.response.status === 422) {
                            let alert_array = Object.values(
                                error.response.data.errors
                            );
                            alert(
                                alert_array
                                    .flat()
                                    .join()
                                    .replace(/,/g, "\n")
                            );
                        }
                    });
            }
        },
        emitForResponses() {
            console.log("this is emitForResponses");
            this.$emit("receiveForResponses", this.post.displayed_post_id);
        },
        emitForAnchor() {
            console.log("this is emitForAnchor★" + this.post.displayed_post_id);
            this.$emit("receiveForAnchor", this.post.displayed_post_id);
        },
        emitLightBoxIndex() {
            console.log("this is emitLightBoxIndex emit / emit post_id:" + this.post.id);
            this.$emit("igniteLightBox",this.post.id);
        }
    }
};
</script>
