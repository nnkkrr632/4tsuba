<template>
    <div color="red">
        <headline-component
            v-bind:headline="
                'ミュートユーザー設定：  設定中' +
                    mute_users.length +
                    '人'
            "
        >
        </headline-component>

        <v-spacer />
        <v-container>
            <v-row no-gutters>
                <v-col
                    v-for="mute_user in mute_users"
                    :key="mute_user.id"
                    cols="12"
                    md="6"
                    class="ml-1"
                >
                    <mute-user-object-component
                        v-bind:mute_user="mute_user"
                        @receiveUpdate="getMuteUsers"
                    >
                    </mute-user-object-component>
                </v-col>
            </v-row>
        </v-container>
    </div>
</template>
<script>
import HeadlineComponent from "../common/HeadlineComponent.vue";
import MuteUserObjectComponent from "./MuteUserObjectComponent.vue";

export default {
    data() {
        return {
            my_id: 0,
            mute_users: {},
        };
    },
    methods: {
        getMyId() {
            //console.log("this is getMyId");
            axios.get("/api/users/me").then(res => {
                this.my_id = res.data;
            });
        },
        getMuteUsers() {
            //console.log("this is getMuteUsers");
            axios.get("/api/mute_users").then(res => {
                this.mute_users = res.data;
                //console.log(this.mute_users);
            });
        },
    },
    components: {
        HeadlineComponent,
        MuteUserObjectComponent
    },
    mounted() {
        this.getMyId();
        this.getMuteUsers();
    }
};
</script>
