/* Manage locations */

import axios from 'axios'

export default {
  get (id, config) {
    let url = `location/${id}`;

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
    let url = 'locations';

    config = config || {};

    return axios.get(url, config)
      .then(response => {
        return response.data;
      })
      .catch(error => {
        return Promise.reject(error);
      })
  },
  getAvailableCities(regionId, config) {
    let url = `location/region/${regionId}/available-cities`;

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
    let url = 'location';

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
    let url = `location/${id}`;

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
