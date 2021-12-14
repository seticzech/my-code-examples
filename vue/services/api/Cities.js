/* Manage cities */

import axios from 'axios'

export default {
  get (id, config) {
    let url = `city/${id}`;

    config = config || {};

    return axios.get(url, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
  getAll (config) {
    let url = 'cities';

    config = config || {};

    return axios.get(url, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
  post (data, config) {
    let url = 'city';

    config = config || {};
    config = Object.assign(config, {
      headers: {"Content-Type": "application/json"}
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
    let url = `city/${id}`;

    config = config || {};
    config = Object.assign(config, {
      headers: {"Content-Type": "application/json"}
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
