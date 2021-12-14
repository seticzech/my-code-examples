/* Permissions module */

import { make } from 'vuex-pathify'

import PermissionsApi from '@/services/api/Permissions.js';

const state = () => {
  return {
    loading: false,
    tree: [],
    treeLoaded: false,
  }
};

const actions = {
  loadTree ({ commit, state }) {
    if (!state.treeLoaded) {
      commit('SET_LOADING', true);

      return PermissionsApi.getTree()
        .then( response => {
          if (response.data) {
            commit('SET_TREE_LOADED', true);
            commit('SET_TREE', response.data);
          }
        })
        .catch(() => {})
        .finally(() => {
          commit('SET_LOADING', false);
        });
    }

    return state.tree;
  }
};

const getters = {
  ...make.getters(state),
};

const mutations = {
  ...make.mutations(state),
};

export default {
  namespaced: true,
  state,
  actions,
  getters,
  mutations
}
