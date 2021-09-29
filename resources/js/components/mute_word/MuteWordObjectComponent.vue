<template>
    <v-card
        color="light-green lighten-5"
        outlined
        class="my-3 d-flex"
    >
        {{mute_word.mute_word}}
        <v-spacer />
        <v-btn
            icon
            class="white--text"
            color="red lighten-2"
            depressed
            @click="destroyMuteWord"
        >
            <v-icon>mdi-volume-off</v-icon>
        </v-btn>
    </v-card>

</template>

<script>

export default {
    props: {
        mute_word: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
        };
    },
    methods: {
        destroyMuteWord() {
            console.log('this is destroyMuteWord');
            if (confirm("ミュートを解除しますか？")) {
            axios
                .delete("/api/mute_words", {
                    data: {
                        id: this.mute_word.id,
                    }
                })
                .then(response => {
                    console.log(response);
                    this.$emit("receiveUpdate");
                })
                .catch(error => {
                    console.log(error.response);
                    if(error.response.status === 422) {
                        let alert_array = Object.values(error.response.data.errors);
                        alert(alert_array.flat().join().replace(/,/g, '\n'));
                    }
                });
            }
        }
        
    },
    components: {
    },
    mounted() {
    }
};
</script>
