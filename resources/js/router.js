import Vue from "vue";
import VueRouter from 'vue-router';

Vue.use(VueRouter);

//ルーティングに必要なコンポーネントのimport
//threads
import CreateThreadComponent from "./components/thread/CreateThreadComponent";
import ThreadsComponent from "./components/thread/ThreadsComponent";
//posts
import PostsComponent from "./components/post/PostsComponent";
import SearchResultsComponent from "./components/post/SearchResultsComponent";
//users
import ProfileComponent from "./components/users/ProfileComponent.vue";
//Auth
import RegisterComponent from "./components/auth/RegisterComponent";
import LoginComponent from "./components/auth/LoginComponent";
import LogoutComponent from "./components/auth/LogoutComponent";
//setting account
import SettingProfileComponent from "./components/setting/SettingProfileComponent";
import SettingAccountComponent from "./components/setting/SettingAccountComponent";
import SettingDeleteComponent from "./components/setting/SettingDeleteComponent";
//setting mute_words
import MuteWordsComponent from "./components/mute_word/MuteWordsComponent";
//setting mute_users
import MuteUsersComponent from "./components/mute_user/MuteUsersComponent";
//introduction
import IntroductionComponent from "./components/common/IntroductionComponent";
//not found
import NotFoundComponent from "./components/common/NotFoundComponent";

import axios from "axios";

//URLと↑でimportしたコンポーネントをマッピングする（ルーティング設定
const router = new VueRouter({
    mode: 'history',
    routes: [
      {
         path: '/register',
         name: 'register',
         component: RegisterComponent,
         meta: {forGuest: true }
      },      
      {
         path: '/login',
         name: 'login',
         component: LoginComponent,
         meta: {forGuest: true }
      },
      {
         path: '/logout',
         name: 'logout',
         component: LogoutComponent,
      },
      {
         path: '/',
         name: 'introduction',
         component: IntroductionComponent,
         alias: '/introduction',
         meta: {forBoth: true }
      },

        //threads
         {
            path: '/threads/create',
            name: 'threads/create',
            component: CreateThreadComponent,
         },
         {
            path: '/threads',
            name: 'threads',
            component: ThreadsComponent,
         },
         {
            path: '/threads/:thread_id/responses/:displayed_post_id',
            name: 'thread.responses',
            component: PostsComponent,
            props: true,
            //ページ遷移前に存在チェックしてエラーハンドリング
            beforeEnter: (to, from, next) => {
               axios.get("/api/exists/threads/" + to.params.thread_id + "/responses/" + to.params.displayed_post_id).then((res) => {
                  //console.log('this is beforeEnter(threads/:thread_id/responses/displayed_post_id)');
                  //console.log(res.data);
                  if(res.data !== 0) {
                     next();
                  } else {
                  next({path: '/404'});
                  }
                  next();
               }).catch((error) =>{
                  //console.log(error);
                  next({path: '/404'});
               });
            },
         },
         {
            path: '/threads/:thread_id',
            name: 'thread.show',
            component: PostsComponent,
            //propsの型をNumberで指定
            props: (to) => {
               const thread_id = Number.parseInt(to.params.thread_id, 10);
               return { thread_id };
             },
            //ページ遷移前に存在チェックしてエラーハンドリング
            beforeEnter: (to, from, next) => {
               axios.get("/api/exists/threads/" + to.params.thread_id).then((res) => {
                  //console.log('this is beforeEnter(threads/:thread_id)');
                  //console.log(res.data);
                  if(res.data !== 0) {
                     next();
                  } else {
                  next({path: '/404'});
                  }
                  next();
               }).catch((error) =>{
                  //console.log(error);
                  next({path: '/404'});
               });
            },
         },
         //posts
         {
            path: '/posts/:search_string',
            name: 'posts.search',
            component: SearchResultsComponent,
            props: true,
         },

         //users
         {
            path: '/users/:user_id/posts',
            name: 'user.posts',
            component: ProfileComponent,
            props: true,
            //ページ遷移前に存在チェックしてエラーハンドリング
            beforeEnter: (to, from, next) => {
               axios.get("/api/exists/users/" + to.params.user_id).then((res) => {
                  //console.log('this is beforeEnter(users/:user_id/)');
                  //console.log(res.data);
                  if(res.data !== 0) {
                     next();
                  } else {
                  next({path: '/404'});
                  }
                  next();
               }).catch((error) =>{
                  //console.log(error);
                  next({path: '/404'});
               });
            },
         },
         {
            path: '/users/:user_id/likes',
            name: 'user.likes',
            component: ProfileComponent,
            props: true,
            //ページ遷移前に存在チェックしてエラーハンドリング
            beforeEnter: (to, from, next) => {
               axios.get("/api/exists/users/" + to.params.user_id).then((res) => {
                  //console.log('this is beforeEnter(users/:user_id/)');
                  //console.log(res.data);
                  if(res.data !== 0) {
                     next();
                  } else {
                  next({path: '/404'});
                  }
                  next();
               }).catch((error) =>{
                  //console.log(error);
                  next({path: '/404'});
               });
            },
         },
         //setting
         {
            path: '/setting/account/profile',
            name: 'setting.profile',
            component: SettingProfileComponent,
         },
         {
            path: '/setting/account/account',
            name: 'setting.account',
            component: SettingAccountComponent,
         },
         {
            path: '/setting/account/delete',
            name: 'setting.delete',
            component: SettingDeleteComponent,
         },
         //ミュートワード
         {
            path: '/setting/mute_words',
            name: 'setting.mute_words',
            component: MuteWordsComponent,
         },
         //ミュートユーザー
         {
            path: '/setting/mute_users',
            name: 'setting.mute_users',
            component: MuteUsersComponent,
         },
         //github
         {
            path: '/github',
            meta: {forBoth: true },
            beforeEnter() {
               let github = 'https://github.com/nnkkrr632/4tsuba';
               window.open(github, '_blank');
            }
         },
         //not found
         {
            path: '*',
            name: 'not_found',
            component: NotFoundComponent,
         }
   ] 
   
});

function isLoggedIn() {
   return localStorage.getItem("auth");
}


router.beforeEach((to, from, next) => {
   if (to.matched.some(record => (record.meta.forBoth))) {
      next();
   } 
  //forGuestがついてないURLへのアクセス
   else if (to.matched.some(record => !(record.meta.forGuest))) {
       if (!isLoggedIn()) {
          alert('ログインが必要です。');
           next("/login");
       } else {
          next();
       }
   } 
   //forGuestがついているURLへのアクセス
   else if (to.matched.some(record => record.meta.forGuest)) {
       if (isLoggedIn()) {
           alert('すでにログイン済みです。');
           next("/logout");
       } else {
           next();
       }
   } else {
           next();
       }
});




export default router;