<template>
<div>
    <v-form ref="form" >
        <v-text-field
            outlined
            color="green lightten-3"
            :counter="word_count"
            :hint="'必須 & 最大' + word_count + '文字'"
            name="mute_word"
            type="text"
            v-model="mute_word"
        />
    </v-form>
        <v-btn
            class="white--text"
            color="green lighten-2"
            depressed
            @click="storeMuteWord"
        >
             登録する
        </v-btn>
</div>
</template>
<script>
export default {
    props: {
        my_id: {
            tyep: Number,
            required: true
        },
    },
    data() {
        return {
            mute_word: null,
            word_count: 10,
        }
    },
    methods: {
        storeMuteWord() {
            //console.log('this is storeMuteWord');
            const form_data = new FormData();
            form_data.append('mute_word', this.mute_word);
            axios
            .post("/api/mute_words", form_data)
            .then(res => {
                //console.log(res);
                this.$emit("receiveUpdate");
                this.mute_word = null;
            })
            .catch(error => {
                //console.log(error.response);
                if(error.response.status === 422) {
                    let alert_array = Object.values(error.response.data.errors);
                    alert(alert_array.flat().join().replace(/,/g, '\n'));
                }
            });
        },
    }
}
</script>