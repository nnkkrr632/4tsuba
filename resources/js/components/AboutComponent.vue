<template>
    <div>
        <p>{{ user.name }}</p>
        <p>{{ user.email }}</p>
        <button type="button" @click="logout">ログアウト</button>
    </div>
</template>
 
<script>
export default {
    data() {
        return {
            user: ""
        };
    },
    mounted() {
        axios.get("/api/user").then(response => {
            this.user = response.data;
        });
    },
    methods: {
        logout() {
            axios
                .post("api/logout")
                .then(response => {
                    console.log('ログアウトボタン押した');
                    console.log(response);
                    localStorage.removeItem("auth");
                    this.$router.push("/login");
                })
                .catch(error => {
                    console.log('ログアウトメソッド時エラーキャッチ');
                    console.log(error);
                });
        }
    }
};
</script>