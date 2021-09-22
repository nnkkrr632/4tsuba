<template>
<div>
    <auth-component :register_or_login="'register'"></auth-component>
</div>
</template>

<script>
import AuthComponent from "./AuthComponent.vue";

export default {
    components: {
        AuthComponent,
    },
};
</script>
