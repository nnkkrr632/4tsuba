<template>
    <div>
        <v-app-bar  color="white" app flat outlined clipped-left height="80" class="green--text" >
            <v-toolbar-title>
                <span class="d-none d-sm-inline">よつば</span>
                <v-icon class="d-sm-none  mr-3 mb-2" color="green lighten-2">mdi-clover</v-icon>
            </v-toolbar-title>
            <v-spacer class="d-none d-sm-inline"></v-spacer>
            <!-- 検索バー PC用 -->
                <v-text-field
                    class="mt-5"
                    dense
                    color="green lighten-2"
                    label="書き込みに含まれる単語を検索"
                    placeholder="単語1 単語2"
                    hint="複数単語はOR検索になります"
                    outlined
                    v-model="search_string"
                >
                </v-text-field>
                <router-link 
                    style="text-decoration: none;"
                    v-bind:to="'/posts/' + search_string"
                >            
                    <v-btn
                        icon
                        class="white--text"
                        color="green lighten-2"
                        depressed
                    >
                        <v-icon>mdi-magnify</v-icon>
                    </v-btn>
                </router-link>
            <v-toolbar-items >
                <v-btn text to="/login" class="grey--text">
                <v-icon>mdi-login</v-icon>
                <span class="d-none d-sm-inline">ログイン</span>
            </v-btn>
                <v-menu offset-y>
                    <template v-slot:activator="{ on }">
                        <v-btn v-on="on" text class="grey--text">
                            <span class="d-none d-sm-inline">その他</span>
                            <v-icon>mdi-menu-down</v-icon></v-btn
                        >
                    </template>
                    <v-list>
                        <v-list-item
                            v-for="other in others"
                            :key="other.name"
                            :to="other.link"
                        >
                            <v-list-item-icon>
                                <v-icon>{{ other.icon }}</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>{{
                                    other.name
                                }}</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list>
                </v-menu>
            </v-toolbar-items>
        </v-app-bar>
    </div>
</template>

<script>
export default {
    data() {
        return {
            search_string: null,
            others: [
                {
                    name: "よつばとは？",
                    icon: "mdi-vuetify",
                    link: "/introduction"
                },
                {
                    name: "ユーザー登録",
                    icon: "mdiaccount-plus",
                    link: "/register"
                },
                {
                    name: "作者Github",
                    icon: "mdi-github-face",
                    link: "/guthub-issue-board"
                },
            ],
        };
    },
    methods: {
    },
};
</script>
