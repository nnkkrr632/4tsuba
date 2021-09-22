<template>
<div>
    <v-form ref="form" v-model="valid">
        <v-text-field
            outlined
            color="green lightten-3"
            name="mute_word"
            type="text"
            v-model="mute_word"
            :rules="[rules.required, rules.length]"
        />
    </v-form>
        <v-btn
            :disabled="!valid"
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
            valid: null,
            limit: 10,
            rules: {
                required: value => !!value || "必ず入力してください",
                //「value &&」がないと初期状態(すなわちvalue = null)のとき、valueが読み取れませんとエラーが出る
                length: value =>
                    (value && value.length <= this.limit) ||
                    this.limit + "文字以内で入力してください",
            },
        }
    },
    methods: {
        storeMuteWord() {
            console.log('this is storeMuteWord');
            const form_data = new FormData();
            form_data.append('mute_word', this.mute_word);
            axios
            .post("/api/mute_words", form_data)
            .then(res => {
                console.log(res);
                console.log(res.data);
                if(res.data == "is_already_stored") {
                    alert('既に登録されています。');
                }
                this.$emit("receiveUpdate");
                this.mute_word = null;
            })
            .catch(error => {
                        console.log(error.response.data);
                        
                    });
        },
    }
}
</script>