<template>
    <div>         
        <button @click="getUser">ユーザ情報取得</button>
        <button @click="checkLoginOrNot">ユーザーID取得</button>
    </div>
</template>
<script>
export default {
    data() {
        return {
            is_login: null
        }
    },
    methods: {
        getUser() {
            axios
                .get("/api/user")
                .then(response => {
                    console.log(response.data);
                })
                .catch(error => {
                    alert(error.response.data.message);
                });
        },
        checkLoginOrNot() {
            axios
            .get("/api/check")
            .then(function(response){
                this.is_login = response.data;
            })
            .catch(function(error){
                console.log(error);
            });

        }    }
}
</script>