<template>
    <div>
        <headline-component v-bind:headline="headline"></headline-component>

        <v-form ref="form" v-model="valid">
            <v-file-input
                v-model="icon"
                color="green lightten-2"
                accept="image/png, image/gif, image/jpg, image/jpeg"
                label="プロフィールアイコン"
                chips
                show-size
            ></v-file-input>
            <v-text-field
                outlined
                label="表示名"
                color="green lightten-3"
                name="name"
                prepend-icon="mdi-account"
                type="text"
                v-model="my_info.name"
                :rules="[rules.required]"
            />

        </v-form>
        <div class="d-flex justify-end">
        <v-btn
            :disabled="!valid"
            class="white--text"
            color="green lighten-2"
            depressed
            @click="editProfile"
        >変更する
        </v-btn>
        </div>
        <div v-if="my_info.role === 'guest'" class="d-flex justify-end">
        <v-btn
            class="white--text mt-3"
            color="green lighten-2"
            depressed
            @click="resetProfile"
        >デフォルト表示に戻す<br>(ゲストユーザー専用)
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
            headline: "表示プロフィール変更",
            my_info: {},
            icon: null,
            valid: null,
            rules: {
                required: value => !!value || "入力必須です。",
            },
            notice: 'メールアドレス・パスワードの変更 はこちら',
            link: '/setting/account/account',
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
        editProfile() {
            console.log('this is editProfile');
            const form_data = new FormData();
            form_data.append("name", this.my_info.name);
            if(this.icon !== null) {
            form_data.append("icon", this.icon);
            }
            console.log(form_data);
            axios
                .post("/api/users/me/profile", form_data, {
                    headers: { "content-type": "multipart/form-data" }
                })
                .then(response => {
                    console.log(response);
                    this.$router.push("/users/" + this.my_info.id + "/posts");
                    this.$router.go({path: "/users/" + this.my_info.id + "/posts", force: true});
                })

        },
        resetProfile() {
            console.log('this is resetProfile');
            axios.get("/api/users/me/profile")
            .then(response => {
                    console.log(response);
                    this.$router.push("/users/" + this.my_info.id + "/posts");
                    this.$router.go({path: "/users/" + this.my_info.id + "/posts", force: true});
            })
        }
    },
    mounted() {
        this.getMyInfo();
    }
};
</script>
