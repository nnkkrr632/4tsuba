<template>
    <div>
        <headline-component v-bind:headline="'レポート：総合'" />
        <date-picker-component 
            @receiveEmittedMonth="getOverview"
        />
        <v-data-table
            :headers="headers"
            :items="dates"
            :items-per-page="dates.length"
            hide-default-footer
        >
            <!-- アクティブユーザー数のツールチップ -->
            <template v-slot:[`item.active_users_count`]="{ item }" >
                <v-tooltip right >
                <template v-slot:activator="{ on }">
                    <span v-on="on" v-if="item.active_users_info.length" style="cursor: pointer;" class="green--text text--lighten-2">{{ item.active_users_info.length }}</span>
                </template>
                    <div v-for="each_info in item.active_users_info" :key="each_info.user_id">
                        <v-list-item-avatar class="ml-n3" size="30" tile>
                            <img
                                :src="'/storage/icons/' + each_info.icon_name"
                                style="object-fit: cover;"
                            />
                        </v-list-item-avatar>
                        <span>{{ '【ID:'+ each_info.user_id + '】' + each_info.name}}</span>
                    </div>
                </v-tooltip>
            </template>
            <!-- 書込数のツールチップ -->            
            <template v-slot:[`item.posts_count`]="{ item }">
                <v-tooltip right >
                <template v-slot:activator="{ on }">
                    <span v-on="on" v-if="item.posts_count_info.length" style="cursor: pointer;" class="green--text text--lighten-2">
                        <span v-if="item.daily_total_posts_count > 0">+</span>{{item.daily_total_posts_count}}
                    </span>
                </template>
                    <div v-for="each_info in item.posts_count_info" :key="each_info.user_id">
                        <v-list-item-avatar class="ml-n3" size="30" tile>
                            <img
                                :src="'/storage/icons/' + each_info.icon_name"
                                style="object-fit: cover;"
                            />
                        </v-list-item-avatar>
                        <span>{{ '<' + each_info.posts_count + '回> ' }}</span>
                        <span>{{ '【ID:'+ each_info.user_id + '】' + each_info.name}}</span>

                    </div>
                </v-tooltip>
            </template>
            <!-- いいね数のツールチップ -->            
            <template v-slot:[`item.likes_count`]="{ item }">
                <v-tooltip right >
                <template v-slot:activator="{ on }">
                    <span v-on="on" v-if="item.likes_count_info.length" style="cursor: pointer;" class="green--text text--lighten-2">
                        <span v-if="item.daily_total_likes_count > 0">+</span>{{item.daily_total_likes_count}}
                    </span>
                </template>
                    <div v-for="each_info in item.likes_count_info" :key="each_info.user_id">
                        <v-list-item-avatar class="ml-n3" size="30" tile>
                            <img
                                :src="'/storage/icons/' + each_info.icon_name"
                                style="object-fit: cover;"
                            />
                        </v-list-item-avatar>
                        <span>{{ '<' + each_info.likes_count + '回> ' }}</span>
                        <span>{{ '【ID:'+ each_info.user_id + '】' + each_info.name}}</span>

                    </div>
                </v-tooltip>
            </template>
        </v-data-table>
    </div>
</template>

<style scoped>
a {text-decoration: none;}
</style>
<script>
import HeadlineComponent from "../common/HeadlineComponent.vue";
import DatePickerComponent from "./DatePickerComponent.vue";
export default {
    data() {
        return {
            links: {
                active_user: '/report/active_user',
                posts: '/report/posts',
                likes: '/report/likes',
            },
            headers: [
                {
                    text: "西暦",
                    align: "start",
                    sortable: true,
                    value: "date"
                },
                { text: "アクティブユーザー数", value: "active_users_count" },
                { text: "書込増減", value: "posts_count" },
                { text: "いいね増減", value: "likes_count" },
            ],
            dates: [],
            monthly_active_users_set: [],
        };
    },
    methods: {
        getOverview(emitted_year_month) {
            console.log("this is getOverview");
            //最初のページ表示時はemitされてないので今月にセットしておく
            if(!emitted_year_month) {
                emitted_year_month = new Date().toISOString().substr(0, 7);
            }
            console.log(emitted_year_month);
            axios
                .get("/api/report/overview/" + emitted_year_month)
                .then(res => {
                    this.dates = res.data;
                })
                .catch(error => {
                    console.log(error.response);
                    if (error.response.status === 422) {
                        let alert_array = Object.values(
                            error.response.data.errors
                        );
                        alert(
                            alert_array
                                .flat()
                                .join()
                                .replace(/,/g, "\n")
                        );
                    }
                });
        },
    },
    components: {
        HeadlineComponent,
        DatePickerComponent,
    },
    mounted() {
        this.getOverview();
    }
};
</script>
