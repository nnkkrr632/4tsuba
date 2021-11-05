<template>
    <v-card color="green lighten-4" outlined class="my-3">
        <div class="d-block d-sm-flex">
            <v-avatar class="ma-3" size="80" tile>
                <!-- vueのルート publicディレクトリからの相対パスを記入する この場合 public/storage/images/example.png -->
                <img
                    v-if="thread.image_name"
                    :src="'/storage/images/' + thread.image_name"
                    style="object-fit: cover;"
                />
                <img
                    v-else
                    src="/storage/images/noimage.jpg"
                    style="object-fit: cover;"
                />
            </v-avatar>
            <div>
                <v-card-title v-html="thread.title"></v-card-title>
                <v-card-text>
                    <v-icon>mdi-comment</v-icon>
                    <span v-html="thread.posts_count"></span>
                    <v-icon>mdi-heart</v-icon>
                    <span v-html="thread.likes_count"></span>
                    <span class="d-flex d-sm-inline">
                        <v-icon>mdi-update</v-icon>
                        <span v-html="thread.updated_at"></span>
                    </span>
                    <span
                        v-if="my_info.role === 'staff'"
                        class="d-flex d-sm-inline"
                    >
                        <v-icon @click="deleteThread">mdi-delete</v-icon>
                    </span>
                </v-card-text>
            </div>
        </div>
    </v-card>
</template>

<script>
export default {
    props: {
        thread: {
            type: Object,
            default: null,
            required: true
        },
        my_info: {
            type: Object,
            require: true,
            default: () => ({ count: 0 })
        }
    },
    methods: {
        getMyInfo() {
            //console.log("this is getMyInfo");
            axios.get("/api/users/me/info").then(res => {
                this.my_info = res.data;
            });
        },
        deleteThread() {
            //console.log("this is deletePost");

            if (confirm("スレッドを削除しますか？")) {
                axios
                    .delete("/api/threads", {
                        data: {
                            id: this.thread.id
                        }
                    })
                    .then(response => {
                        //console.log(response.data);
                        if (response.data === "bad_user") {
                            alert("スタッフ以外は削除できません。");
                        } else {
                            alert("削除しました。");
                        }
                    })
                    .catch(error => {
                        //console.log(error.response.data);
                    });
            }
        }
    }
};
</script>
