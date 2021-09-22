<template>
    <v-card flat >
        <v-card-title class="green--text text--lighten-1">{{headline}}</v-card-title>
    </v-card>
</template>

<script>
export default {
    props: {
        headline: {
            type: String,
            default: "default headline",
            required: true
        }
    }
};
</script>
