<template>
    <v-card
        color="light-green lighten-5"
        outlined
        class="my-3 d-flex"
    >
        <v-list-item-avatar class=" mr-3" size="30" tile>
            <img
                :src="'/storage/icons/' + mute_user_info.icon_name"
                style="object-fit: cover;"
            />
        </v-list-item-avatar>
        <router-link 
            style="text-decoration: none;"
            onMouseOut="this.style.textDecoration='none';" 
            onMouseOver="this.style.textDecoration='underline';"                    
            class="green--text text--lighten-1"
            v-bind:to="{name: 'user.posts', params: {user_id: mute_user_info.id}}"
        >
            <span v-html="mute_user_info.name" class="ml-3"></span>
        </router-link>

        <v-spacer />
        <v-btn
            icon
            class="white--text"
            color="red lighten-2"
            depressed
            @click="destroyMuteUser"
        >
            <v-icon>mdi-volume-off</v-icon>
        </v-btn>
    </v-card>

</template>

<script>

export default {
    props: {
        mute_user_info: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
        };
    },
    methods: {
        destroyMuteUser() {
            console.log('this is destroyMuteUser');
            if (confirm("ミュートを解除しますか？")) {
            axios
                .delete("/api/mute_users", {
                    data: {
                        user_id: this.mute_user_info.id,
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
