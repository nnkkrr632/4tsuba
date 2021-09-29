<template>
    <div>
        <headline-component v-bind:headline="'メールアドレス・パスワード 変更'"></headline-component>

        <v-form ref="form" v-model="valid">
            <v-text-field
                outlined
                label="メールアドレス"
                color="green lightten-3"
                name="email"
                prepend-icon="email"
                type="text"
                v-model="my_info.email"
            />
            <!-- :rules="[rules.required, rules.email]" -->
            <v-text-field
                outlined
                id="current_password"
                label="現在のパスワード"
                color="green lightten-3"
                name="password"
                prepend-icon="lock"
                type="password"
                v-model="current_password"
                
            />
            <!-- :rules="[rules.required, rules.password]" -->
            <v-text-field
                outlined
                id="new_password"
                label="新しいパスワード"
                color="green lightten-3"
                name="password"
                prepend-icon="lock"
                type="password"
                v-model="password"
                
            />
            <!-- :rules="[rules.required, rules.password]" -->
            <v-text-field
                outlined
                label="新しいパスワード(確認)"
                color="green lightten-3"
                prepend-icon="lock"
                type="password"
                v-model="password_confirm"
                
            />
            <!--  :rules="[rules.required, rules.password_confirm]" -->
        </v-form>
        <div class="d-flex justify-end">
        <v-btn v-if="my_info.role !== 'guest'"
            :disabled="!valid"
            class="white--text"
            color="green lighten-2"
            depressed
            @click="editAccount"
        >
             {{message}}
        </v-btn>
        <v-card v-else
        color="grey lighten-2"
        outlined
        class="my-3 d-flex"
        > <span class="grey--text">{{message_for_guest}}</span>
        </v-card>

        </div>
        <div class="mt-8 grey--text text--darken-1 d-flex justify-end">
            <v-icon class="mb-1" color="green lighten-3"
                >mdi-information-outline</v-icon
            >
            <router-link :to="link_profile">表示プロフィールの変更はこちら</router-link>
        </div>
        <div class="mt-8 grey--text text--darken-1 d-flex justify-end">
            <v-icon class="mb-1" color="green lighten-3"
                >mdi-information-outline</v-icon
            >
            <router-link :to="link_delete_account">アカウント削除はこちら</router-link>
        </div>

    </div>
</template>

<script>
import HeadlineComponent from "../common/HeadlineComponent.vue";
export default {
    data() {
        return {
            my_info: {},
            current_password: null,
            password: null,
            password_confirm: null,
            valid: null,
            rules: {
                required: value => !!value || "入力必須です。",
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
            link_profile: '/setting/account/profile',
            link_delete_account: '/setting/account/delete',
            message: 'メールアドレス/パスワードを変更する',
            message_for_guest: 'ゲストユーザーは変更できません',
        };
    },
    components: {
        HeadlineComponent,
    },
    methods: {
        getMyInfo() {
            console.log("this is getMyInfo");
            axios.get("/api/users/me/info").then(res => {
                this.my_info = res.data;
            });
        },
        editAccount() {
            console.log('this is editAccount');
            axios
                .patch("/api/users/me", {
                    email: this.my_info.email,
                    current_password: this.current_password,
                    password: this.password,
                    password_confirm: this.password_confirm,
                })
                .then(response => {
                    console.log(response.data);
                    if(response.message == "This action is unauthorized.") {
                    console.log(response);
                    alert('ゲストユーザーはメールアドレス・パスワードを変更できません。');
                    }
                    else if(response.data === 'bad_password'){
                        alert('現在のパスワードが違います。');
                    } else {
                        if (confirm('メールアドレス・パスワードを変更しました。')) {
                            this.$router.push("/users/" + this.my_info.id + "/posts");
                        }
                    }
                })
                .catch(error => {
                    console.log(error.response);
                    if(error.response.status === 422) {
                        let alert_array = Object.values(error.response.data.errors);
                        alert(alert_array.flat().join().replace(/,/g, '\n'));
                    }
                });
        },
    },
    mounted() {
        this.getMyInfo();
    }
};
</script>
