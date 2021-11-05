<template>
    <div>
        <headline-component v-bind:headline="headline"></headline-component>

        <v-form ref="form">
            <!-- アイコン -->
            <v-file-input
                v-model="icon"
                color="green lightten-2"
                label="プロフィールアイコン(JPG, JPEG, PNG, GIF）"
                chips
                show-size
                accept="image/png, image/gif, image/jpg, image/jpeg" 
            ></v-file-input>
            <!-- 表示名 -->
            <v-text-field
                outlined
                label="表示名"
                color="green lightten-3"
                name="name"
                prepend-icon="mdi-account"
                :counter="max_word_count"
                :hint="'必須 & 最大' + max_word_count + '文字'"
                persistent-hint
                v-model="my_info.name"
            />

        </v-form>
        <div class="d-flex justify-end">
        <v-btn
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
            max_word_count: 20,
            notice: 'メールアドレス/パスワードの変更 はこちら',
            link: '/setting/account/account',
        };
    },
    components: {
        HeadlineComponent,
    },
    methods: {
        getMyInfo() {
            //console.log("this is getMyInfo");
            axios.get("/api/users/me/info").then(res => {
                this.my_info = res.data;
            });
        },
        editProfile() {
            //console.log('this is editProfile');
            const form_data = new FormData();
            form_data.append("name", this.my_info.name);
            if(this.icon !== null) {
            form_data.append("icon", this.icon);
            }
            //console.log(form_data);
            axios
                .post("/api/users/me/profile", form_data, {
                    headers: { "content-type": "multipart/form-data" }
                })
                .then(response => {
                    //console.log(response);
                    this.$router.push("/users/" + this.my_info.id + "/posts");
                    this.$router.go({path: "/users/" + this.my_info.id + "/posts", force: true});
                })
                .catch(error => {
                    //console.log(error.response);
                    if(error.response.status === 422) {
                        let alert_array = Object.values(error.response.data.errors);
                        //console.log(alert_array);
                        let flat_array = alert_array.flat();
                        //console.log(flat_array);
                        let alert_string = flat_array.join();
                        //console.log(alert_string);
                        let alert_message = alert_string.replace(/,/g, '\n');
                        //console.log(alert_message);
                        alert(alert_message);
                    }
                });                
        },
        resetProfile() {
            //console.log('this is resetProfile');
            axios.get("/api/users/me/profile")
            .then(response => {
                    //console.log(response);
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
