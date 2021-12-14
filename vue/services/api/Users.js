/* Manage users */

import axios from 'axios'

export default {
  get (id, config) {
    let url = `user/${id}`;

    config = config || {};
    config = Object.assign(config, {
      params: {f: "vue"}
    });

    return axios.get(url, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
  getAll (config) {
    let url = 'users';

    config = config || {};
    config = Object.assign(config, {
      //params: {f: "vue"}
    });

    return axios.get(url, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
  getRoles (id, config) {
    let url = `user/${id}/roles`;

    config = config || {};
    config = Object.assign(config, {
      params: {f: "vue"}
    });

    return axios.get(url, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
  post (data, config) {
    let url = 'user';

    config = config || {};
    config = Object.assign(config, {
      headers: {"Content-Type": "application/json"},
      params: {f: "vue"}
    });

    return axios.post(url, data, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
  put (id, data, config) {
    let url = `user/${id}`;

    config = config || {};
    config = Object.assign(config, {
      headers: {"Content-Type": "application/json"},
      params: {f: "vue"}
    });

    return axios.put(url, data, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  }
}
