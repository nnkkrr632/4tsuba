<template>
    <div class="p-5">

    <v-card flat >
        <v-row>
            <v-col cols="1"></v-col>
        <v-card-title class="grey--text text--darken-2">ログアウトしますか？</v-card-title>
        </v-row>
        <v-row>
            <v-col cols="6"></v-col>
        <v-btn
            class="white--text"
            color="green lighten-2"
            depressed
            @click="logout"
        >
        はい
        </v-btn>
        </v-row>
    </v-card>

    </div>
</template>

<script>
export default {
    data() {
        return {};
    },
    methods: {
        logout() {
            axios.post("/api/logout", {})
            .then(response => {
                console.log(response);
                localStorage.removeItem("auth");
                this.$router.push("/login");
                this.$router.go({path: "/login", force: true});
            })
            .catch(error => {
                console.log(error.response.data);
                alert(error.response.data.message);
            });
        }
    }
};
</script>
