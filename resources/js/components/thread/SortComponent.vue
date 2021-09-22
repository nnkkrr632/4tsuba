<template>
    <v-app-bar flat max-width="180" color="white">
        <v-autocomplete
            color="green lighten-2"
            item-color="green lighten-2"
            append-icon="mdi-menu"
            v-model="selected_sort"
            :items="sorts"
            hint="並び順"
            persistent-hint
            @change="changeSort()"
        ></v-autocomplete>

        <v-checkbox
            color="green lighten-2"
            on-icon="mdi-arrow-down-bold"
            off-icon="mdi-arrow-up-bold"
            :hint="order[0]"
            persistent-hint
            v-model="true_false"
            class="ml-3"
            @click="
                switchHint();
                changeSort();
            "
        ></v-checkbox>
    </v-app-bar>
</template>

<script>
export default {
    data() {
        return {
            sorts: ["最終更新", "作成日時", "書込数", "いいね数"],
            //バインドする変数
            selected_sort: "最終更新",
            true_false: true,
            order: ["desc", "asc"],
            //メソッドでAPIとして送信する変数
            sort_object: {}
        };
    },
    methods: {
        switchHint() {
            this.order = this.order.reverse();
            console.log(this.order);
        },
        changeSort() {
            this.$set(this.sort_object, 'sort', this.selected_sort);
            this.$set(this.sort_object, 'order', this.order[0]);
            console.log(this.sort_object);

            //親コンポーネントへのemit  第一引数は親コンポーネントで受け取るためのイベント名
            this.$emit("receiveSortObject", this.sort_object);
        }
    }
};
</script>
