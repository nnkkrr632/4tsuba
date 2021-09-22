<template>
    <div color="red">
        <headline-component
            v-bind:headline="
                'ミュートユーザー設定：  設定中' +
                    mute_user_id_list.length +
                    '人'
            "
        >
        </headline-component>

        <v-spacer />
        <v-container>
            <v-row no-gutters>
                <v-col
                    v-for="mute_user_info in mute_users_info"
                    :key="mute_user_info.id"
                    cols="12"
                    md="6"
                    class="ml-1"
                >
                    <mute-user-object-component
                        v-bind:mute_user_info="mute_user_info"
                        @receiveUpdate="getMuteUsersList"
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
            mute_user_id_list: [],
            mute_users_info: {}
        };
    },
    methods: {
        getMyId() {
            console.log("this is getMyId");
            axios.get("/api/users/me").then(res => {
                this.my_id = res.data;
            });
        },
        getMuteUsersList() {
            console.log("this is getMuteUsersList");
            axios.get("/api/mute_users").then(res => {
                this.mute_user_id_list = res.data;
                console.log(this.mute_user_id_list);
                //if(this.mute_user_id_list.length) {
                this.getMuteUsersInfo();
                //}
            });
        },
        getMuteUsersInfo() {
            console.log("this is getMuteUsersInfo");
            axios
                .get("/api/users/", {
                    params: {
                        user_id_list: this.mute_user_id_list
                    }
                })
                .then(res => {
                    this.mute_users_info = res.data;
                    console.log(this.mute_users_info);
                });
        }
    },
    components: {
        HeadlineComponent,
        MuteUserObjectComponent
    },
    mounted() {
        this.getMyId();
        this.getMuteUsersList();
    }
};
</script>
