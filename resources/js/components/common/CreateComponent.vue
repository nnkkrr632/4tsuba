<template>
    <div>

        <!-- 入力フォーム -->
        <v-form ref="form" class="pa-4 ">
            <!-- スレッドタイトル -->
            <v-textarea v-if="thread_or_post === 'thread'"
                v-model="input.title"
                :counter="limit.title"
                color="green lightten-2"
                outlined
                rows="1"
                label="スレッドタイトル"
                auto-grow
                :hint="'必須 & 最大' + limit.title + '文字'"
                persistent-hint
            ></v-textarea>

            <!-- 本文 -->
            <v-textarea
                class="mt-6"
                v-model="input.body"
                :counter="limit.body"
                color="green lightten-2"
                outlined
                :label="body_label[0]"
                auto-grow
                :hint="'必須 & 最大' + limit.body + '文字'"
                persistent-hint
                ref="focusBody"
            ></v-textarea>

            <!-- 画像 -->
            <v-file-input
                v-model="input.image"
                color="green lightten-2"
                placeholder="JPG, JPEG, PNG, GIF"
                accept="image/png, image/gif, image/jpg, image/jpeg" 
                :hint="hint[0]"
                persistent-hint
                chips
                show-size
            ></v-file-input>
            <!--                 accept="image/png, image/gif, image/jpg, image/jpeg"  -->

            <template v-if="thread_or_post === 'thread'">
            <div  class="mt-16 grey--text text--darken-1">
                <v-icon class="mb-1" color="green lighten-3"
                    >mdi-information-outline</v-icon
                >
                スレッドは削除できません(スレッド内の書き込みは編集・削除可)。
            </div>
            </template>
        </v-form>

        <v-divider></v-divider>
        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
                class="white--text"
                color="green lighten-2"
                depressed
                @click="store"
            >
                {{button_message[0]}}
            </v-btn>
        </v-card-actions>
    </div>
</template>

<script>

export default {
    props: {
        thread_or_post: {
            type: String,
            default: "post",
        },
        thread_id: {
            type:Number,
            default: null,
        },
        anchor: {
            type: String,
            default: null
        },
    },
    data: function() {
        return {
            //入力項目
            input: {title: null, body: null, image: null},
            //バリデーション関連
            limit: { title: 20, body: 200 },
            hint: ["", "スレッドのサムネイル(※)を設定できます。 ※スレッド内で最も若い番号(書き込み順)の画像が自動登録"],
            button_message: ["書き込む", "スレッドを作成する"],
            body_label: ["書き込む", "本文"],
            created_thread_id: null,
        };
    },
    methods: {
        store() {
            console.log('this is store')

            const form_data = new FormData();
            form_data.append("body", this.input.body);
            //スレッドならタイトル、ポストならスレッドidを追加
            if(this.thread_or_post === 'thread') {
                form_data.append("title", this.input.title);
            } else {
                form_data.append("thread_id", this.thread_id);
            }
            //画像があるなら追加
            if(this.input.image) {
                form_data.append("image", this.input.image);
            }
            //確認
            for (let value of form_data.entries()) {
                console.log(value);
            }
            //ポスト
            if(this.thread_or_post === 'post') {
                console.log('this is store post');
                axios
                    .post("/api/posts", form_data, {
                        headers: { "content-type": "multipart/form-data" }
                    })
                    .then(response => {
                        console.log(response);
                        this.$emit("re_get_mainly_posts");
                        this.input.body = null;
                        this.input.image = null;
                    })
                    .catch(error => {
                        console.log(error.response);
                        if(error.response.status === 422) {
                            let alert_array = Object.values(error.response.data.errors);
                            alert(alert_array.flat().join().replace(/,/g, '\n'));
                        }
                    });
            }
            //スレッド
            else {
                console.log('this is store thread');
                axios
                    .post("/api/threads", form_data, {
                        headers: { "content-type": "multipart/form-data" }
                    })
                    .then(response => {
                        console.log(response);
                        this.created_thread_id = response.data;
                        console.log(this.created_thread_id);
                        this.$router.push({ name: 'thread.show', params: { thread_id: this.created_thread_id }})
                    })
                    .catch(error => {
                        console.log(error.response);
                        if(error.response.status === 422) {
                            let alert_array = Object.values(error.response.data.errors);
                            alert(alert_array.flat().join().replace(/,/g, '\n'));
                        }
                    });
            }
        },
        switchWords() {
            if(this.thread_or_post === 'thread') {
                this.hint = this.hint.reverse();
                this.button_message = this.button_message.reverse();
                this.body_label = this.body_label.reverse();
            }
        },
        writeAnchor() {
            this.input.body = this.anchor;
            this.$refs.focusBody.focus();
        }
    },
    watch: {
        anchor: function() {
            this.writeAnchor();
        }
    },
    mounted() {
        this.switchWords();
    },
};
</script>
