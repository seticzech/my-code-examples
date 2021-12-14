/* Manage authentication */

import axios from 'axios'

export default {
  login (data, config) {
    let url = 'oauth/login';

    config = config || {};

    data = Object.assign({
      grant_type: "password",
      client_id: process.env.VUE_APP_CLIENT_ID,
      client_secret: process.env.VUE_APP_CLIENT_SECRET,
      scope: ""
    }, data);

    return axios.post(url, data, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
  logout (config) {
    let url = 'oauth/logout';

    config = config || {};

    return axios.get(url, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
  getMe (config) {
    let url = 'oauth/me';

    config = config || {};

    return axios.get(url, config)
      .then(response => {
        if (response) {
          return response.data;
        }
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
  getRoles (config) {
    let url = 'oauth/roles';

    config = config || {};
    config = Object.assign(config, {
      params: {f: "vue"}
    });

    return axios.get(url, config)
      .then(response => {
        if (response) {
          return response.data;
        }
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
  getPermissionsList (config) {
    let url = 'oauth/permissions/list';

    config = config || {};

    return axios.get(url, config)
      .then(response => {
        if (response) {
          return response.data;
        }
      })
      .catch(error => {
        return Promise.reject(error);
      })
  }
}
