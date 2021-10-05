<template>
    <div>
        <headline-component v-bind:headline="'アカウント削除'"></headline-component>
    <div class="grey--text text--darken-1 my-8 ml-4">
        <div>
            <v-icon class="mb-1" color="green lighten-3"
                >mdi-information-outline</v-icon
            >
            <span>作成したスレッドは削除されません。</span>
        </div>
        <div>
            <v-icon class="mb-1" color="green lighten-3"
                >mdi-information-outline</v-icon
            >
            <span>スレッドの書込は削除されません(ユーザー名「退会済みユーザー]として残ります)。</span>
        </div>
        <div>
            <v-icon class="mb-1" color="green lighten-3"
                >mdi-information-outline</v-icon
            >
            <span>スレッドの書込を削除したい場合は、お手数ですが、書込のゴミ箱アイコンより削除してください。</span>
        </div>
    </div>

        <v-form ref="form">
            <v-text-field
                outlined
                id="new_password"
                label="パスワード確認"
                color="green lightten-3"
                name="password"
                prepend-icon="lock"
                type="password"
                :counter="word_counts[0]"
                :hint="'必須 & 最低' + word_counts[1] + '文字 & 半角英字と数字を含む'"               
                v-model="password"
            />
        </v-form>
        <div class="d-flex justify-end">
            <v-card v-if="my_info.role === 'guest'"
            color="grey lighten-2"
            outlined
            class="my-3 d-flex"
            > <span class="grey--text">{{message_for_guest}}</span>
            </v-card>
            <v-btn v-else
                class="white--text"
                color="green lighten-2"
                depressed
                @click="deleteMyAccount"
            >
                {{message}}
            </v-btn>
        </div>
        <div class="mt-8 grey--text text--darken-1 d-flex justify-end">
            <v-icon class="mb-1" color="green lighten-3"
                >mdi-information-outline</v-icon
            >
            <router-link :to="link_threads">アカウントを削除せず続ける</router-link>
        </div>

    </div>
</template>

<script>
import HeadlineComponent from "../common/HeadlineComponent.vue";
export default {
    data() {
        return {
            my_info: {},
            current_password: null,
            password: null,
            message: 'アカウントを削除する',
            message_for_guest: 'ゲストユーザーは削除できません',
            link_threads: '/threads',
            word_counts: [24, 8],
        };
    },
    components: {
        HeadlineComponent,
    },
    methods: {
        getMyInfo() {
            console.log("this is getMyInfo");
            axios.get("/api/users/me/info").then(res => {
                this.my_info = res.data;
            });
        },
        deleteMyAccount() {
            console.log('this is deleteMyAccount');
            axios
                .delete("/api/users/me/", {data:{
                    password: this.password,
                }})
                .then(response => {
                    console.log(response.data);
                    if(response.message == "This action is unauthorized.") {
                    console.log(response);
                    alert('ゲストユーザーはメールアドレス/パスワードを変更できません。');
                    }
                    else if(response.data === 'bad_password'){
                        alert('パスワードが違います。');
                    } else {
                        localStorage.removeItem("auth");
                        if (confirm('アカウントを削除しました。ご利用ありがとうございました。')) {
                            this.$router.go({path: "/", force: true});
                        }
                    }
                })
                .catch(error => {
                    console.log(error.response);
                    if(error.response.status === 422) {
                        let alert_array = Object.values(error.response.data.errors);
                        alert(alert_array.flat().join().replace(/,/g, '\n'));
                    }
                    if(error.response.status === 403) {
                        let error_message = error.response.data.message;
                        if(error_message == 'This action is unauthorized.') {
                            alert('ゲストユーザーはアカウントを削除できません。');
                        }
                    }
                });
                
        },
    },
    mounted() {
        this.getMyInfo();
    }
};
</script>
