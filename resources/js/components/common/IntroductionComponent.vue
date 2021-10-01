<template>
  <v-card
    class="mx-auto"
    flat
    max-width="700"
  >
    <v-card-text class="text-center">
      <p class="text-h3 green--text text--lighten-2">
        よつばとは
      </p>
      <div class="text-h4">
        twitterっぽく使える掲示板です。
      </div>
      <div class="text-subtitle-1 mt-6">
          twitterや他の掲示板を参考に、使いやすい掲示板をつくりました。<br>
          ぜひ、書き込みしてみてください！
      </div>
    </v-card-text>
    <v-layout justify-center>
      <v-btn
        text
        large
        color="green lighten-2"
        class="text-h4 mt-6"
        :to=link
      >
        はじめよう
      </v-btn>
    </v-layout>
  </v-card>
</template>
<script>

export default {
    data() {
        return {
            link: "/threads"
        }
    },
    methods: {
        checkLogin() {
            console.log('this is checkLogin');
            axios.get("/api/check/login").then(res => {
                if(res.data == false)
                this.link = "/login"
            });
        },
    },
    mounted() {
        this.checkLogin();
    },

}
</script>
