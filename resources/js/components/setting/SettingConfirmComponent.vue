<template>
    <div>
        <headline-component v-bind:headline="headline"></headline-component>
        <div class="ml-4 mb-3 green--text text--lighten-1">続けるにはパスワードをもう一度入力して下さい。</div>
        <v-form ref="form" v-model="valid">
            <v-text-field
                id="password"
                label="パスワード"
                color="green lightten-3"
                name="password"
                prepend-icon="lock"
                type="password"
                v-model="password"
                :rules="[rules.required, rules.password]"
            />
        </v-form>
        <div class="d-flex justify-end">
        <v-btn
            :disabled="!valid"
            class="white--text"
            color="green lighten-2"
            depressed
            @click="confirmPassword"
        >
             {{message}}
        </v-btn>
        </div>
        <div class="mt-8 grey--text text--darken-1 d-flex justify-end">
            <v-icon class="mb-1" color="green lighten-3"
                >mdi-information-outline</v-icon
            >
            <router-link :to="link">
                {{notice}}
            </router-link>
        </div>

    </div>
</template>

<script>
import HeadlineComponent from "../common/HeadlineComponent.vue";
export default {
    data() {
        return {
            headline: "パスワードの確認",
            password: null,
            is_verified: false,
            valid: null,
            rules: {
                required: value => !!value || "必ず入力してください",
                password: value => {
                    const pattern = /^(?=.*?[a-z])(?=.*?\d)(?=.*?[!-\/:-@[-`{-~])[!-~]{8,100}$/i;
                    return (
                        pattern.test(value) ||
                        "8文字以上 かつ 半角英数字記号を含む"
                    );
                },
            },
            notice: '表示プロフィールの変更はこちら',
            link: '/setting/account/profile',
            message: 'パスワードを送信する',
        };
    },
    components: {
        HeadlineComponent,
    },
    methods: {
        confirmPassword() {
            console.log('this is confirmPassword');
            axios
                .post("/api/users/me/confirm", {
                    password: this.password,
                })
                .then(response => {
                    console.log(response);
                    this.is_verified = Boolean(response.data);
                    if(this.is_verified) {
                    this.$router.push("/setting/account/account");
                    }
                    else {
                        alert('パスワードが違います。');
                    }
                })
        }
    },
};
</script>
