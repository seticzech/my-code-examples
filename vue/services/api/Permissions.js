/* Manage permissions */

import axios from 'axios'

export default {
  getTree (config) {
    let url = 'permissions/tree';

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
}
