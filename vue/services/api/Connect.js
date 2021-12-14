/* Connect to API server */

import axios from 'axios'

export default {
  connect (config) {
    // fake failed connection with timeout
    // return new Promise((resolve, reject) => {
    //   let wait = setTimeout(() => {
    //     clearTimeout(wait);
    //     reject('Network Error');
    //   }, 2000)
    // });

    // try to connect to server with global error handling turned off
    let url = 'version';

    config = config || {};

    return axios.get(url, config)
      .then(response => {
        return response.data;
      })
  }
}
