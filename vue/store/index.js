/* Vuex definition */

import Vue from 'vue'
import Vuex from 'vuex'
import createPersistedState from 'vuex-persistedstate'
import pathify from './pathify'

// import modules
import modules from './modules'

Vue.use(Vuex);

const debug = process.env.NODE_ENV !== 'production';

export default new Vuex.Store({
  plugins: [
    pathify.plugin,
    createPersistedState({
      paths: ['callbacks'],
      storage: window.sessionStorage
    })
  ],
  modules,
  strict: debug,
})
