<template>
  <v-row>
    <v-col
      cols="11"
      sm="5"
    >
      <v-menu
        ref="menu"
        v-model="menu"
        :close-on-content-click="false"
        transition="scale-transition"
        offset-y
        max-width="290px"
        min-width="auto"
      >
        <template v-slot:activator="{ on, attrs }">
          <v-text-field
            v-model="date"
            prepend-icon="mdi-calendar"
            readonly
            v-bind="attrs"
            v-on="on"
            color="green lighten-2"
          ></v-text-field>
        </template>
        <v-date-picker
          v-model="date"
          locale="jp-ja"
          type="month"
          no-title
          scrollable
          color="green lighten-2"
        >
          <v-spacer></v-spacer>
          <v-btn
            text
            color="green lighten-2"
            @click="emitMonth"
          >
            OK
          </v-btn>
        </v-date-picker>
      </v-menu>
    </v-col>
  </v-row>
</template>

<script>
  export default {
    data: () => ({
      date: new Date().toISOString().substr(0, 7),
      menu: false,
      modal: false,
    }),
    methods: {
        emitMonth() {
            console.log('this is emitMonth');
            this.menu = false;
            this.$emit('receiveEmittedMonth', this.date);

        }
    }
  }
</script>