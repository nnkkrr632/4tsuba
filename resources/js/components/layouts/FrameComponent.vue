<template>
    <v-app>
        <!-- サイドドロワー -->
        <drawer-component></drawer-component>
        <!-- ヘッダー -->
        <header-component></header-component>

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
    components: {
        ConfirmLoginComponent,
        HeaderComponent,
        DrawerComponent,
    }
};
</script>
