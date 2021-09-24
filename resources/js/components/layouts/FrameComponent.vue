<template>
    <v-app>
        <!-- サイドドロワー -->
        <drawer-component v-bind:my_info="my_info"></drawer-component>
        <!-- ヘッダー -->
        <header-component v-bind:my_info="my_info"></header-component>

        <!-- コンテンツ表示位置 -->
        <v-main>
            <v-card max-width="800" class="mx-2 mt-6" flat>
            <confirm-login-component></confirm-login-component>
            <router-view />
            </v-card>
        </v-main>

    </v-app>
</template>

<script>
import ConfirmLoginComponent from "../auth/ConfirmLoginComponent.vue";
import HeaderComponent from "./header/HeaderComponent.vue";
import DrawerComponent from "./drawer/DrawerComponent.vue";

export default {
    data() {
        return {
            my_info: {},
        }
    },
    methods: {
        getMyInfo() {
            console.log("this is getMyInfo");
            axios.get("/api/users/me/info").then(res => {
                if(!res.data == false)
                this.my_info = res.data;
            });
        },
    },
    mounted() {
        this.getMyInfo();
    },
    components: {
        ConfirmLoginComponent,
        HeaderComponent,
        DrawerComponent,
    }
};
</script>
