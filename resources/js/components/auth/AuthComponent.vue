<template>
    <div class="p-5">
        <v-card flat>
            <v-toolbar class="green--text text--lighten-1" flat>
                <v-toolbar-title>{{ title[0] }}</v-toolbar-title>
                <v-spacer />
            </v-toolbar>
            <v-card-text>
                <v-form ref="form">
                    <v-text-field
                        v-if="register_or_login === 'register'"
                        outlined
                        label="表示名"
                        placeholder="よつば"
                        color="green lightten-3"
                        name="name"
                        prepend-icon="mdi-account"
                        :counter="word_counts[0]"
                        :hint="'必須 & 最大' + word_counts[0] + '文字'"
                        v-model="name"
                    />
                    <v-text-field
                        outlined
                        label="メールアドレス"
                        placeholder="example@4tsuba.site"
                        color="green lightten-3"
                        name="email"
                        prepend-icon="email"
                        type="text"
                        :counter="word_counts[1]"
                        :hint="'必須 & 最大' + word_counts[1] + '文字'"
                        v-model="email"
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
                        :counter="word_counts[2]"
                        :hint="'必須 & 最低' + word_counts[3] + '文字 & 半角英字と数字を含む'"
                        v-model="password"
                    />
                    <v-text-field
                        outlined
                        name="password"
                        v-if="register_or_login === 'register'"
                        label="パスワード(確認)"
                        placeholder="p@ssw0rd"
                        color="green lightten-3"
                        prepend-icon="lock"
                        type="password"
                        :counter="word_counts[2]"
                        :hint="'必須 & 最低' + word_counts[3] + '文字 & 半角英字と数字を含む'"
                        v-model="password_confirm"
                    />
                </v-form>
            </v-card-text>
            <v-card-actions>
                <v-spacer />
                <v-btn
                    class="white--text mr-3"
                    color="green lighten-2"
                    depressed
                    @click="submit"
                >
                    {{ title[0] }}
                </v-btn>
            </v-card-actions>
            <div class="mt-8 mr-3 grey--text text--darken-1 d-flex justify-end">
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
            password_confirm: null,
            word_counts:[20, 50, 24, 8],
            title: ["ログイン", "ユーザー登録"],
            link: ["register", "login"]
        };
    },
    methods: {
        submit() {
            axios.get("/sanctum/csrf-cookie").then(response => {
                if (this.register_or_login === "register") {
                    axios
                        .post("/register", {
                            name: this.name,
                            email: this.email,
                            password: this.password,
                            password_confirm:this.password_confirm,
                        })
                        .then(response => {
                            console.log(response);
                            localStorage.setItem("auth", "ture");
                            this.$router.push("/threads");
                            this.$router.go({ path: "/threads", force: true });
                        })
                    .catch(error => {
                        console.log(error.response);
                        if(error.response.status === 422) {
                            let alert_array = Object.values(error.response.data.errors);
                            alert(alert_array.flat().join().replace(/,/g, '\n'));
                        }
                    });
                } else {
                    axios
                        .post("/login", {
                            email: this.email,
                            password: this.password
                        })
                        .then(response => {
                            console.log(response);
                            console.log(response.data.message)
                            if(response.data.message === 'login_success') {
                                localStorage.setItem("auth", "ture");
                            }
                            this.$router.push("/threads");
                            this.$router.go({ path: "/threads", force: true });
                        })
                    .catch(error => {
                        console.log(error.response);
                        if(error.response.status === 422) {
                            let alert_array = Object.values(error.response.data.errors);
                            alert(alert_array.flat().join().replace(/,/g, '\n'));
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
