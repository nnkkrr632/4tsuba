<template>
    <v-app>
        <!-- サイドドロワー -->
        <drawer-component v-bind:my_info="my_info" ></drawer-component>
        <!-- ヘッダー -->
        <header-component v-bind:my_info="my_info"></header-component>

        <!-- コンテンツ表示位置 -->
        <v-main>
            <v-card max-width="800" class="mx-2 mt-6" flat>
            <router-view />
            </v-card>
        </v-main>
    </v-app>
</template>

<script>
import HeaderComponent from "./header/HeaderComponent.vue";
import DrawerComponent from "./drawer/DrawerComponent.vue";

export default {
    data() {
        return {
            my_info: {},
        }
    },
    methods: {
        checkLogin() {
            console.log('this is checkLogin');
            axios.get("/api/check/login").then(res => {
                if(!res.data == false)
                this.getMyInfo();
            });
        },
        getMyInfo() {
            console.log("this is getMyInfo");
            axios.get("/api/users/me/info").then(res => {
                this.my_info = res.data;
            });
        },
    },
    mounted() {
        this.checkLogin();
    },
    components: {
        HeaderComponent,
        DrawerComponent,
    },
};
</script>
