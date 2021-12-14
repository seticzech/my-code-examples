/* Manage uploads */

import axios from 'axios'

export default {
  getServerStatus (config) {
    let url = 'upload/server-status';

    config = config || {};

    return axios.get(url, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })

  },
  uploadStart (data, config) {
    let url = 'upload/start';

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
  uploadPart (id, data, config) {
    let url = `upload/${id}/part`;

    config = config || {};
    config = Object.assign(config, {
      headers: {"Content-Type": "multipart/form-data"}
    });

    return axios.post(url, data, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
  uploadFinish (id, data, config) {
    let url = `upload/${id}/finish`;

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
  }
};
