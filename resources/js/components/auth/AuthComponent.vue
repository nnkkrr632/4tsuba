<template>
    <div class="p-5">
        <v-card flat>
            <v-toolbar class="green--text text--lighten-1" flat>
                <v-toolbar-title>{{ title[0] }}</v-toolbar-title>
                <v-spacer />
            </v-toolbar>
            <v-card-text>
                <v-form ref="form" v-model="valid">
                    <v-text-field
                        v-if="register_or_login === 'register'"
                        outlined
                        label="表示名"
                        placeholder="よつば"
                        color="green lightten-3"
                        name="name"
                        prepend-icon="mdi-account"
                        type="text"
                        v-model="name"
                        :rules="[rules.required]"
                    />
                    <v-text-field
                        outlined
                        label="メールアドレス"
                        placeholder="4tsuba@test.com"
                        color="green lightten-3"
                        name="email"
                        prepend-icon="email"
                        type="text"
                        v-model="email"
                        :rules="[rules.required, rules.email]"
                    />

                    <v-text-field
                        outlined
                        id="password"
                        label="パスワード"
                        placeholder="p@ssw0rd"
                        color="green lightten-3"
                        name="password"
                        prepend-icon="lock"
                        type="password"
                        v-model="password"
                        :rules="[rules.required, rules.password]"
                    />

                    <v-text-field
                        outlined
                        v-if="register_or_login === 'register'"
                        label="パスワード(確認)"
                        placeholder="p@ssw0rd"
                        color="green lightten-3"
                        prepend-icon="lock"
                        type="password"
                        :rules="[rules.required, rules.password_confirm]"
                    />
                </v-form>
            </v-card-text>
            <v-card-actions>
                <v-spacer />
                <v-btn
                    :disabled="!valid"
                    class="white--text"
                    color="green lighten-2"
                    depressed
                    @click="submit"
                >
                    {{ title[0] }}
                </v-btn>
            </v-card-actions>
            <div class="mt-8 grey--text text--darken-1 d-flex justify-end">
                <v-icon class="mb-1" color="green lighten-3"
                    >mdi-information-outline</v-icon
                >
                <router-link v-bind:to="{ name: link[0] }">
                    {{ title[1] }}はこちら
                </router-link>
            </div>
        </v-card>
    </div>
</template>

<script>
export default {
    props: {
        register_or_login: {
            type: String,
            default: "login"
        }
    },
    data() {
        return {
            //入力項目
            name: null,
            email: null,
            password: null,
            //バリデーション項目
            valid: null,
            rules: {
                required: value => !!value || "必ず入力してください",
                email: value => {
                    const pattern = /^[a-zA-Z0-9_.+-]+@([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$/;
                    return (
                        pattern.test(value) || "メールアドレスを入力して下さい"
                    );
                },
                password: value => {
                    const pattern = /^(?=.*?[a-z])(?=.*?\d)(?=.*?[!-\/:-@[-`{-~])[!-~]{8,100}$/i;
                    return (
                        pattern.test(value) ||
                        "8文字以上 かつ 半角英数字記号を含む"
                    );
                },
                password_confirm: value => {
                    return (
                        value === this.password || "パスワードが一致しません"
                    );
                }
            },
            title: ["ログイン", "ユーザー登録"],
            link: ["register", "login"]
        };
    },
    methods: {
        submit() {
            axios.get("/sanctum/csrf-cookie").then(response => {
                if (this.register_or_login === "register") {
                    axios
                        .post("/api/register", {
                            name: this.name,
                            email: this.email,
                            password: this.password
                        })
                        .then(response => {
                            console.log(response);
                            localStorage.setItem("auth", "ture");
                            this.$router.push("/threads");
                            this.$router.go({ path: "/threads", force: true });
                        })
                        .catch(error => {
                            console.log(error.response.data);
                            alert(error.response.data.message);                            
                        });
                } else {
                    axios
                        .post("/api/login", {
                            email: this.email,
                            password: this.password
                        })
                        .then(response => {
                            console.log(response);
                            localStorage.setItem("auth", "ture");
                            this.$router.push("/threads");
                            this.$router.go({ path: "/threads", force: true });

                        })
                        .catch(error => {
                            console.log(error.response.data);
                            if(error.response.data.message == "The given data was invalid.") {
                                alert('メールアドレスもしくはパスワードが正しくありません。');
                            }
                        });
                }
            });
        },
        switchWords() {
            if (this.register_or_login === "register") {
                this.title = this.title.reverse();
                this.link = this.link.reverse();
            }
        }
    },
    mounted() {
        this.switchWords();
    }
};
</script>
