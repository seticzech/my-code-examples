/* Manage languages */

import axios from 'axios'

export default {
  getAll (config) {
    let url = 'languages';

    config = config || {};

    return axios.get(url, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
  get (id, config) {
    let url = `language/${id}`;

    config = config || {};

    return axios.get(url, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
  postReorder (idList, config) {
    let url = 'languages/reorder';

    config = config || {};

    let data = {
      idList
    };

    return axios.post(url, data, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  }
}
