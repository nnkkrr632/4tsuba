<template>
    <div>
        <headline-component v-bind:headline="'レポート：' + month + '月'" />
        <date-picker-component 
            @receiveEmittedMonth="getReport"
        />
        <v-data-table
            :headers="headers"
            :items="dates"
            :items-per-page="dates.length"
        >
            <template v-slot:[`item.active_users_count`]="{ item }">
                <a :href="link.active_user"> {{ item.active_users_count }}</a>
            </template>
            <template v-slot:[`item.posts_count`]="{ item }">
                <a :href="link.posts"> {{ item.posts_count }}</a>
            </template>
            <template v-slot:[`item.likes_count`]="{ item }">
                <a :href="link.likes"> {{ item.likes_count }}</a>
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
            month: 12,
            link: {
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
            dates: [
                {
                    date: "2020/12/3",
                    active_users_count: 159,
                    posts_count: 122,
                    likes_count: 24,
                },
                {
                    date: "2020/12/2",
                    active_users_count: 408,
                    posts_count: 331,
                    likes_count: 87,
                },
                {
                    date: "2020/12/1",
                    active_users_count: 42,
                    posts_count: 543,
                    likes_count: 51,
                },
                {
                    date: "2020/11/30",
                    active_users_count: 518,
                    posts_count: 243,
                    likes_count: 65,
                },
                {
                    date: "2020/11/29",
                    active_users_count: 518,
                    posts_count: 243,
                    likes_count: 65,
                },
                {
                    date: "2020/11/28",
                    active_users_count: 518,
                    posts_count: 243,
                    likes_count: 65,
                }
            ],
        };
    },
    methods: {
        getReport(emitted_month) {
            console.log("this is getReport");
            console.log(emitted_month);
            axios
                .get("/api/report/" + emitted_month)
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
        }
    },
    components: {
        HeadlineComponent,
        DatePickerComponent,
    }
};
</script>
