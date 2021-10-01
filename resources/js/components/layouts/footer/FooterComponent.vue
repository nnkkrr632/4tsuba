<template>
  <v-footer
    app
    padless
    color="green lighten-2"
    v-bind:max_width="max_width"
  >

      <v-card-text class="white--text text-center">
        2021 — <strong>よつば</strong>
      </v-card-text>
  </v-footer>
</template>
<script>
export default {
    props: {
        max_width: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
        };
    },
};
</script>
