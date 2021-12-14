/* Manage files */

import axios from 'axios'

export default {
  delete (id, config) {
    let url = `file/${id}`;

    config = config || {};

    return axios.delete(url, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
  deleteFiles (data, config) {
    let url = 'files';

    config = config || {};
    config = Object.assign(config, {
      data,
      headers: {"Content-Type": "application/json"}
    });

    return axios.delete(url, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
};
