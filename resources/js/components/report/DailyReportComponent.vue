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
            <template v-slot:[`item.active_users_count`]="{ item }" >
                <v-tooltip right >
                <template v-slot:activator="{ on }">
                    <span v-on="on" style="cursor: pointer;" class="green--text text--lighten-2">{{ item.active_users_count }}</span>
                </template>
                    <div v-for="user_info in item.users_info" :key="user_info.user_id">
                        <span>{{ '(ID:'+ user_info.user_id + ')' + user_info.name}}</span>
                    </div>
                </v-tooltip>
            </template>
            
            <template v-slot:[`item.posts_count`]="{ item }">
                <a :href="links.posts" class="green--text text--lighten-2"> {{ item.posts_count }}</a>
            </template>
            <template v-slot:[`item.likes_count`]="{ item }">
                <a :href="links.likes" class="green--text text--lighten-2"> {{ item.likes_count }}</a>
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
                { text: "アクティブユーザー", value: "active_users_count" },
                { text: "書込", value: "posts_count" },
                { text: "いいね", value: "likes_count" },
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
                    this.getMonthlyActiveUsersSet(emitted_year_month);
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
        getMonthlyActiveUsersSet(year_month) {
            console.log("this is getMonthlyActiveUsersSet");
            //最初のページ表示時はemitされてないので今月にセットしておく
            console.log(year_month);
            axios
                .get("/api/report/active_users/set/" + year_month)
                .then(res => {
                    this.monthly_active_users_set = res.data;
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
