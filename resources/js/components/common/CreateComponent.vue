<template>
    <div>

        <!-- 入力フォーム -->
        <v-form ref="form" v-model="valid" class="pa-4 ">
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
                :rules="[rules.required, rules.length_title]"
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
                :rules="[rules.required, rules.length_body]"
                ref="focusBody"
            ></v-textarea>

            <!-- 画像 -->
            <v-file-input
                v-model="input.image"
                color="green lightten-2"
                accept="image/png, image/gif, image/jpg, image/jpeg"
                placeholder="JPG, JPEG, PNG, GIF"
                :hint="hint[0]"
                persistent-hint
                chips
                show-size
            ></v-file-input>

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
                :disabled="!valid"
                class="white--text"
                color="green lighten-2"
                depressed
                @click="emit"
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
        anchor: {
            type: String,
            default: null
        }
    },
    data: function() {
        return {
            //入力項目
            input: {title: null, body: null, image: null},
            //バリデーション関連
            limit: { title: 10, body: 20 },
            valid: null,
            rules: {
                required: value => !!value || "必ず入力してください",
                //「value &&」がないと初期状態(すなわちvalue = null)のとき、valueが読み取れませんとエラーが出る
                length_title: value =>
                    (value && value.length <= this.limit.title) ||
                    this.limit.title + "文字以内で入力してください",
                length_body: value =>
                    (value && value.length <= this.limit.body) ||
                    this.limit.body + "文字以内で入力してください"
            },
            hint: ["", "スレッドのサムネイル(※)を設定できます。 ※スレッド内で最も若い番号(書き込み順)の画像"],
            button_message: ["書き込む", "スレッドを作成する"],
            body_label: ["書き込む", "本文"],
        };
    },
    methods: {
        emit() {
            const result = this.$refs.form.validate();
            console.log("入力内容バリデーション " + result);
            console.log(this.input);

            const form_data = new FormData();
            form_data.append("body", this.input.body);
            form_data.append("image", this.input.image);

            if(this.thread_or_post === 'thread') {
                form_data.append("title", this.input.title);
            }

            this.$emit("receiveInput", form_data);
            this.input.body = null;
            this.input.image = null;
            
            
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
    }
};
</script>
