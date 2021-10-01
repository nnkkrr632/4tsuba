<template>
    <div>
        <!-- ログイン -->
        <auth-component></auth-component>

        <!-- ゲストログイン -->
        <v-card flat>
            <v-card-title class="green--text text--lighten-1"
                >ゲストログイン</v-card-title
            >
        </v-card>
        <v-container>
            <v-row >
                <v-col v-for="n in 3" :key="n" cols="12" md="4">
                    <v-btn
                        class="white--text mt-3 ml-3"
                        color="green lighten-2"
                        depressed
                        @click="loginAsGuest(n)"
                        >ゲストユーザー{{ n }}でログイン
                    </v-btn>
                </v-col>
            </v-row>
        </v-container>
    </div>
</template>

<script>
import AuthComponent from "./AuthComponent.vue";

export default {
    data() {
        return {};
    },
    components: {
        AuthComponent
    },
    methods: {
        loginAsGuest($user_id) {
            console.log("this is loginAsGuest");
            console.log($user_id);
            axios
                .get("/login/guest/" + $user_id)
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
        }
    }
};
</script>
