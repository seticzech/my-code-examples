/* Manage translations */

import axios from 'axios'

export default {
  getByLanguageCode (language, config) {
    let url = `/translations/language/code/${language}`;

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
