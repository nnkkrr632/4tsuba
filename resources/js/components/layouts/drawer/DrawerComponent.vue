<template>
    <div>
    <template v-if="Object.keys(my_info).length">
        <v-navigation-drawer
            app
            nav
            permanent
            clipped
            :mini-variant="mini"
            mini-variant-width="60"
        >
            <v-container>
                <!-- アバター部分 -->
                <template>
                    <v-list-item color="green lighten-2" :to="'/users/' + my_info['id'] + '/posts'">
                            <v-list-item-avatar size="50" tile>
                                <img
                                    :src="'/storage/icons/' + my_info.icon_name"
                                    style="object-fit: cover;"
                                />
                            </v-list-item-avatar>
                            <v-list-item-content>
                                <v-list-item-title class="green-text">
                                    {{ my_info.name}}
                                </v-list-item-title>
                                <v-list-item-subtitle>
                                    {{my_info.email}}
                                </v-list-item-subtitle>
                            </v-list-item-content>
                    </v-list-item>
                </template>
                <v-divider></v-divider>

                <!-- リスト部分 -->
                <v-list dense>
                    <template v-for="nav_list in nav_lists">
                        <!-- リストがサブリストを持たない場合 -->
                        <v-list-item
                            color="green lighten-2"
                            v-if="!nav_list.lists"
                            :to="nav_list.link"
                            :key="nav_list.name"
                        >
                            <v-list-item-icon>
                                <v-icon>{{ nav_list.icon }}</v-icon>
                                <v-badge
                                    v-if="nav_list.notification"
                                    :content="nav_list.notification"
                                    :value="nav_list.notification"
                                    color="green lighten-2"
                                    overlap
                                >
                                </v-badge>

                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>
                                    {{ nav_list.name }}
                                </v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>

                        <!-- リストがサブリストを持つ場合 -->
                        <v-list-group
                            color="green lighten-2"
                            v-else
                            no-action
                            :prepend-icon="nav_list.icon"
                            :key="nav_list.name"
                        >
                            <template v-slot:activator>
                                <v-list-item-content>
                                    <v-list-item-title>
                                        {{ nav_list.name }}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </template>
                            <v-list-item
                                v-for="list in nav_list.lists"
                                :key="list.name"
                                :to="list.link"
                            >
                                <v-list-item-title>
                                    {{ list.name }}
                                </v-list-item-title>
                            </v-list-item>
                        </v-list-group>
                    </template>
                </v-list>
            </v-container>
        </v-navigation-drawer>
    </template>
    <template v-else class="red">
        <v-navigation-drawer
         width="60"
         permanent
        app
        nav
        clipped
        >
        </v-navigation-drawer>
    </template>
    </div>
</template>

<script>
export default {
    props: {
        my_info: {
            type: Object,
            default: () => {},
        },
    },
    data() {
        return {
            mini: true,
            nav_lists: [
                {
                    name: "スレッド一覧",
                    icon: "mdi-clipboard-text-multiple",
                    link: "/threads"
                },
                {
                    name: "スレッド作成",
                    icon: "mdi-plus-thick",
                    link: "/threads/create"
                },
                {
                    name: "設定",
                    icon: "mdi-cogs",
                    lists: [
                        {
                            name: "ミュートワード",
                            link: "/setting/mute_words"
                        },
                        {
                            name: "ミュートユーザー",
                            link: "/setting/mute_users"
                        },
                        {
                            name: "マイプロフィール",
                            link: "/setting/account/profile"
                        }
                    ]
                },
                {
                    name: "ログアウト",
                    icon: "mdi-logout",
                    link: "/logout"
                }
            ]
        };
    },
    methods: {
        handleResize: function() {
            if (window.innerWidth <= 960) {
                this.mini = true;
            } else {
                this.mini = false;
            }
        },
        switchNavLists() {
            if(this.mini) {
                //console.log(this.mini);
                //delete this.nav_lists[2];
                this.nav_lists.splice(2,2,
                    {
                    name: "ミュートワード",
                    icon: "mdi-alphabetical-variant-off",
                    link: "/setting/mute_words"
                    },
                    {
                    name: "ミュートユーザー",
                    icon: "mdi-account-off-outline",
                    link: "/setting/mute_users"
                    },
                    {
                    name: "マイプロフィール",
                    icon: "mdi-account-edit",
                    link: "/setting/account/profile"
                    },
                    {
                        name: "ログアウト",
                        icon: "mdi-logout",
                        link: "/logout"
                    }
                )
            } else {
                //console.log(this.mini);
                this.nav_lists.splice(2,4,
                    {
                        name: "設定",
                        icon: "mdi-cogs",
                        lists: [
                            {
                                name: "ミュートワード",
                                link: "/setting/mute_words"
                            },
                            {
                                name: "ミュートユーザー",
                                link: "/setting/mute_users"
                            },
                            {
                                name: "マイプロフィール",
                                link: "/setting/account/profile"
                            }
                        ]
                    },
                    {
                        name: "ログアウト",
                        icon: "mdi-logout",
                        link: "/logout"
                    }
                )
            }
        },
    },
    created() {
        window.addEventListener("resize", this.handleResize);
        this.handleResize();
    },
    destroyed() {
        window.removeEventListener("resize", this.handleResize);
    },
    watch: {
        mini: function() {
            this.switchNavLists();
        }
    },

    mounted() {
        this.switchNavLists();
    },
};
</script>

