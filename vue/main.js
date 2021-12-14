//document.cookie = "XDEBUG_SESSION=PHPSTORM; path=/; domain=tms-api.scholasticus.local; Expires=Tue, 19 Jan 2038 03:14:07 GMT;";

// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import './polyfill'
// import cssVars from 'css-vars-ponyfill'

import './shared/utils'

/* Import prototype expansions */
import './shared/prototypes'

/* Import some necessary components and plugins globally */
import Vue from 'vue'
import VueBus from 'vue-bus';
import BootstrapVue from 'bootstrap-vue'
import Vuelidate from 'vuelidate'
import VueMoment from 'vue-moment'
//import moment from 'moment-timezone'
import App from './App'
import store from './store'
import router from './router'
import { i18n } from './plugins/i18n'

/*
 * Imported here only for setup 'axios' and interceptors, axios component remains undefined.
 * We use 'axios' independently to Vue and for true component import we must use directive:
 * import axios from 'axios'
 */
import axios from './services/axios'

// todo
// cssVars()

// make some plugins accesible anywhere, even in dev-tools console
window.store = store;
window.i18n = i18n;

Vue.use(VueBus);
Vue.use(BootstrapVue, {
  breakpoints: [`xs`, 'sm', 'md', 'lg', 'xl', 'xxl']
});
Vue.use(Vuelidate);
Vue.use(VueMoment);

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  i18n,
  template: '<App/>',
  components: {
    App
  },
  created() {
    /* Set language by browser setup */
    let language = navigator.language || navigator.userLanguage;
    this.$store.commit('translations/SET_CURRENT', language);

    /* Load translations */
    this.$store.dispatch('translations/reload');

    /* Load authenticated user info */
    if (this.$store.get('auth/isAuthenticated')) {
      this.$store.dispatch('auth/loadMe');
      this.$store.dispatch('auth/loadRoles');
      this.$store.dispatch('auth/loadPermissionsList');
    }
  }
});



