/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import router from './router';
import vuetify from './vuetify';
import vueLazyLoad from './vueLazyLoad';
// import jaconv from './jaconv';
import './../sass/app.scss'

import FrameComponent from "./components/layouts/FrameComponent";

//おまじない？
window.Vue = require('vue').default;

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))


//Vue.component('example-component', require('./components/ExampleComponent.vue').default);

//Vueコンポーネントをグローバル登録している。
//グローバルに登録されたコンポーネントは、その後に作成されたルート Vue インスタンス(new Vue)のテンプレートで使用できます。
//さらに、その Vue インスタンスのコンポーネントツリーのすべてのサブコンポーネント内でも使用できます。
Vue.component('frame-component', FrameComponent);


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// プラグインとしての使用を宣言することで、router.jsを他のコンポーネントにおいて
//インポートすることなくthis.$routerによってVue Routerにアクセス可能となる。
const app = new Vue({
    el: '#app',
    router,
    vuetify,
    vueLazyLoad,
    // jaconv,
});
