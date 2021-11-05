<template>
    <v-app-bar flat max-width="180" color="white">
        <v-autocomplete
            color="green lighten-2"
            item-color="green lighten-2"
            append-icon="mdi-menu"
            v-model="selected_column"
            :items="Object.values(columns)"
            hint="並び順"
            persistent-hint
            @change="changeSort()"
        ></v-autocomplete>

        <v-checkbox
            color="green lighten-2"
            on-icon="mdi-arrow-down-bold"
            off-icon="mdi-arrow-up-bold"
            :hint="desc_asc[0]"
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
            columns: {
                updated_at: "最終更新",
                created_at: "作成日時",
                posts_count: "書込数",
                likes_count: "いいね数"
            },
            //バインドする変数
            selected_column: "最終更新",
            true_false: true,
            desc_asc: ["desc", "asc"],
            //メソッドでAPIとして送信する変数
            order_by: { column: null, desc_asc: null }
        };
    },
    methods: {
        switchHint() {
            this.desc_asc = this.desc_asc.reverse();
            //console.log(this.desc_asc);
        },
        changeSort() {
            this.order_by["column"] = this.getKeyByValue(
                this.columns,
                this.selected_column
            );
            this.order_by["desc_asc"] = this.desc_asc[0];
            //console.log(this.order_by);

            //親コンポーネントへのemit  第一引数は親コンポーネントで受け取るためのイベント名
            this.$emit("update_order_by", this.order_by);
        },
        getKeyByValue(object, value) {
            return Object.keys(object).find(key => object[key] === value);
        }
    }
};
</script>
