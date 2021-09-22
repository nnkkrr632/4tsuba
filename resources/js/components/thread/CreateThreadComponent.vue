<template>
    <div>
        <headline-component v-bind:headline="headline"></headline-component>
        <create-component v-bind:thread_or_post="'thread'" @receiveInput="create"></create-component>
    </div>
</template>

<script>
// コンポーネントをインポート
import HeadlineComponent from "../common/HeadlineComponent.vue";
import CreateComponent from "../common/CreateComponent.vue";

export default {
    data: function() {
        return {
            headline: "スレッドを作成する",
            thread_id: 0,
        };
    },
    methods: {
        create(emitted_form_data) {
            const form_data = emitted_form_data;
            console.log('this is post');
            for (let value of form_data.entries()) { 
                console.log(value);
            }

            axios
                .post("/api/threads", form_data, {
                    headers: { "content-type": "multipart/form-data" }
                })
                .then(response => {
                    this.thread_id = response.data;
                    console.log(response);
                    console.log(this.thread_id);
                    console.log('スレッド作成');
                    this.$router.push({ name: 'thread.show', params: { thread_id: this.thread_id }})
                })
                .catch(error => {
                    console.log(error.response.data);
                });
        }

    },
    components: {
        HeadlineComponent,
        CreateComponent,
    },
};
</script>
