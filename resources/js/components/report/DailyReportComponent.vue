<template>
    <div>
        <headline-component v-bind:headline="'レポート：総合'" />
        <date-picker-component 
            @receiveEmittedMonth="getReport"
        />
        <v-data-table
            :headers="headers"
            :items="dates"
            :items-per-page="dates.length"
            hide-default-footer
        >
            <template v-slot:[`item.active_users_count`]="{ item }">
                <a :href="links.active_user" class="green--text text--lighten-2"> {{ item.active_users_count }}</a>
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
        };
    },
    methods: {
        getReport(emitted_month) {
            console.log("this is getReport");
            //最初のページ表示時はemitされてないので今月にセットしておく
            if(!emitted_month) {
                emitted_month = new Date().toISOString().substr(0, 7);
            }
            console.log(emitted_month);
            axios
                .get("/api/report/overview/" + emitted_month)
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
    },
    mounted() {
        this.getReport();
    }
};
</script>
