<template>
    <div>
        <div class="d-flex justify-end">
            <headline-component v-bind:headline="
                'スレッド一覧：' + threads.length + '件'"
            >
            </headline-component>
            <v-spacer></v-spacer>
            <!-- 子コンポーネントからのemit受け取り @イベント名="メソッド名" -->
            <sort-component @update_order_by="updateOrderBy"></sort-component>
        </div>

        <!-- スレッド一覧 -->
        <div v-for="thread in threads" :key="thread.id">
            <!-- 1つのスレッドを描画 -->
            <router-link
                style="text-decoration: none;"
                v-bind:to="{
                    name: 'thread.show',
                    params: { thread_id: thread.id }
                }"
            >
                <thread-object-component
                    v-bind:thread="thread"
                    v-bind:my_info="my_info"
                ></thread-object-component>
            </router-link>
        </div>
    </div>
</template>

<script>
import HeadlineComponent from "../common/HeadlineComponent.vue";
import SortComponent from "./SortComponent";
import ThreadObjectComponent from "./ThreadObjectComponent.vue";

export default {
    data() {
        return {
            my_info: {},
            threads: [],
            order_by: {'column':'updated_at', 'desc_asc':'desc'},
        };
    },
    methods: {
        getMyInfo() {
            console.log("this is getMyInfo");
            axios.get("/api/users/me/info").then(res => {
                this.my_info = res.data;
            });
        },
        updateOrderBy(emitted_order_by) {
            console.log("this is updateSort");
            this.order_by['column'] = emitted_order_by['column'];
            this.order_by['desc_asc'] = emitted_order_by['desc_asc'];
            console.log(this.order_by);
            //他のメソッドはthis.methodName()で実行できる
            this.getThreads();
        },
        getThreads() {
            console.log("this is getThreads");
            axios
                .get("/api/threads", {
                    params: {
                        column: this.order_by.column,
                        desc_asc: this.order_by.desc_asc
                    }
                })
                .then(res => {
                    this.threads = res.data;
                })
                .catch(error => {
                    console.log(error.response);
                    if(error.response.status === 422) {
                        let alert_array = Object.values(error.response.data.errors);
                        alert(alert_array.flat().join().replace(/,/g, '\n'));
                    }
                });

        }
    },
    components: {
        HeadlineComponent,
        SortComponent,
        ThreadObjectComponent
    },
    mounted() {
        this.getMyInfo();
        this.getThreads();
    }
};
</script>
