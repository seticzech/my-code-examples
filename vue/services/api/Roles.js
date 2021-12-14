/* Manage users */

import axios from 'axios'

export default {
  getAll (config) {
    let url = 'roles';

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
  getPermissions (id, config) {
    let url = `role/${id}/permissions`;

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
    let url = 'role';

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
    let url = `role/${id}`;

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
