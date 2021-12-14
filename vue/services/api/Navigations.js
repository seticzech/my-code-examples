/* Manage navigations */

import axios from 'axios'

export default {
  getAll (config) {
    let url = 'navigations';

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
  }
}
