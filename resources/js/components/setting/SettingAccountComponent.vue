<template>
    <div>
        <headline-component v-bind:headline="'メールアドレス/パスワード 変更'"></headline-component>

        <v-form ref="form">
            <v-text-field
                outlined
                label="メールアドレス"
                color="green lightten-3"
                name="email"
                prepend-icon="email"
                type="text"
                :counter="word_counts[0]"
                :hint="'必須 & 最大' + word_counts[0] + '文字'"
                v-model="my_info.email"
            />
            <v-text-field
                outlined
                id="current_password"
                label="現在のパスワード"
                color="green lightten-3"
                name="password"
                prepend-icon="lock"
                type="password"
                :counter="word_counts[1]"
                :hint="'必須 & 最低' + word_counts[1] + '文字 & 半角英字と数字を含む'"
                v-model="current_password"
                
            />
            <v-text-field
                outlined
                id="new_password"
                label="新しいパスワード"
                color="green lightten-3"
                name="password"
                prepend-icon="lock"
                type="password"
                :counter="word_counts[1]"
                :hint="'必須 & 最低' + word_counts[1] + '文字 & 半角英字と数字を含む'"
                v-model="password"
                
            />
            <v-text-field
                outlined
                label="新しいパスワード(確認)"
                color="green lightten-3"
                prepend-icon="lock"
                type="password"
                :counter="word_counts[1]"
                :hint="'必須 & 最低' + word_counts[1] + '文字 & 半角英字と数字を含む'"
                v-model="password_confirm"
            />
        </v-form>
        <div class="d-flex justify-end">
        <v-btn v-if="my_info.role !== 'guest'"
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
            link_profile: '/setting/account/profile',
            link_delete_account: '/setting/account/delete',
            message: 'メールアドレス/パスワードを変更する',
            message_for_guest: 'ゲストユーザーは変更できません',
            word_counts:[100, 8],
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
                    alert('ゲストユーザーはメールアドレス/パスワードを変更できません。');
                    }
                    else if(response.data === 'bad_password'){
                        alert('現在のパスワードが違います。');
                    } else {
                        if (confirm('メールアドレス/パスワードを変更しました。')) {
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
