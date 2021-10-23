<template>
  <v-card
    class="mx-auto"
    flat
    max-width="700"
  >
  <v-layout justify-center>
  <v-avatar size="400" tile class="mx-auto">
    <img
      src="/storage/materials/4tsuba_logo.png"
    />
  </v-avatar>
  </v-layout>
    <v-card-text class="text-center">
      <div class="text-h3 my-6">
        twitterぽく使える掲示板です。
      </div>
      <div class="text-subtitle-1 mt-6">
          twitterや他の掲示板を参考に、こんな機能の掲示板があればと思いつくりました。<br>
          よろしければ、書込をしていただけると嬉しいです！
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
