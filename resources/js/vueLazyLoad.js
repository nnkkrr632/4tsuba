import Vue from "vue";
import VueLazyLoad from 'vue-lazyload';
import LightBox from 'vue-image-lightbox';

 require('vue-image-lightbox/dist/vue-image-lightbox.min.css')
// Use only when you are using Webpack

Vue.use(VueLazyLoad);

//ドキュメントには↓とあるが、できなくてPostsComponent.vueでimport & export defaultしたらうまくいった
export default {
    components: {
        VueLazyLoad,
      LightBox,
    },
  }