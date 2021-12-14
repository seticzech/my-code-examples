/*
 * Axios setup
 *
 * We use interceptors for global error handling. Global error handling can
 * be disabled entirely by pass 'globalErrorHanglig' with FALSE value
 * on axios request, like:
 *
 * axios.get('some_url', { globalErrorHandling: false })
 *   .then(response => {
 *     // handle response here
 *   })
 *   .catch(error => {
 *     // handle errors locally
 *
 *     return error;
 *   })
 *
 * Or we can handle some of errors and other let to be handled by global
 * error handler.
 * To do this we can pass callback function(s) on axios request named by
 * HTTP status code(s), like:
 *
 * axios.get('some_url', (
 *     onError404: (error => { // do some stuff }),
 *     onError500: (error => { // do some other stuff })
 *   })
 *   .then(response => {
 *     // handle response here
 *   })
 *   .catch(error => {
 *     // or reject promise to catch it later
 *     Promise.reject(error);
 *   })
 */

import Vue from 'vue'
import axios from 'axios'
import axiosCancel from 'axios-cancel'
import store from '@/store'
import { i18n } from '@/plugins/i18n'

axiosCancel(axios, {
  debug: false // default
});

axios.defaults.baseURL = process.env.VUE_APP_API_URL;

  /* Global axios request interceptors setup */
axios.interceptors.request.use(
  config => {

    // add authorization token to the headers
    let token = store.get('auth/token');
    if (token) {
      config.headers = Object.assign(config.headers, {
        Authorization: 'Bearer ' + token
      });
    }

    // add user ID to data on specific requests
    let requestTypes = ['patch', 'post', 'put'];
    if ((requestTypes.indexOf(config.method) > -1) && config.data) {
      let user = store.get('auth/user');
      if (user) {
        if (( typeof config.data === 'object') && (config.data.constructor.name === 'FormData')) {
          config.data.set('userId', user.id);
        } else {
          config.data = Object.assign(config.data, {userId: user.id});
        }
      }
    }

    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

/* Global axios response interceptors setup */
axios.interceptors.response.use(
  response => {
    return Promise.resolve(response)
  },
  error => {
    let config = error.config || {};

    // we can set axios config.globalErrorHandling to false if we want to handle errors locally
    // on .catch part of axios request
    if ((typeof config.globalErrorHandling !== 'undefined') && (config.globalErrorHandling === false)) {
      return Promise.reject(error);
    }

    if (error.response) {

      if (error.response.status === 401) {
        store.dispatch('app/redirectTo', { name: 'Login' });

        return Promise.reject(error);
      }

      let callback = 'onError' + error.response.status;
      let data = (typeof error.response.data !== 'undefined') ? error.response.data : null;

      if (typeof config[callback] === 'function') {
        config[callback].call(config[callback], error);
      } else {
        let title = 'dialog.title_error_' + error.response.status;
        let message = 'error.error_' + error.response.status;

        store.commit('modals/error/SET_DATA', data);
        store.commit('modals/error/SET_MESSAGE', i18n.t(message));
        store.commit('modals/error/SET_TITLE', i18n.t(title));
        store.commit('modals/error/SET_VISIBLE', true);
      }

      return Promise.reject(error);

    } else if (error.message && (error.message === 'Network Error')) {

      if (typeof config.onNetworkError === 'function') {
        config.onNetworkError.call(config.onNetworkError, error);
      } else {
        store.commit('modals/error/SET_DATA', null);
        store.commit('modals/error/SET_MESSAGE', i18n.t('error.connecting_to_server_failed'));
        store.commit('modals/error/SET_TITLE', i18n.t('dialog.title_error_network'));
        store.commit('modals/error/SET_VISIBLE', true);
      }

      return Promise.reject(error);

    }

    return Promise.reject(error);
  }
);

export default axios
