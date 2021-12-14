/* Languages module */

import { make } from 'vuex-pathify'

import LanguagesApi from '@/services/api/Languages.js'

const state = () => {
  return {
    items: [],
    loaded: false,
    loading: false
  }
};

const actions = {
  itemsLoad ({ commit, state }) {
    if (!state.loaded) {
      commit('SET_LOADING', true);

      return LanguagesApi.getAll()
        .then( response => {
          if (response.data) {
            commit('SET_LOADED', true);
            commit('SET_ITEMS', response.data);
          }
        })
        .catch(() => {})
        .finally(() => {
          commit('SET_LOADING', false);
        });
    }
  },
  itemsRefresh ({ commit, dispatch }) {
    commit('CLEAR_LOADED');

    return dispatch('itemsLoad');
  },
  itemsReorder ({ commit }, idList) {
    return LanguagesApi.postReorder(idList)
      .then( response => {
        if (response.data) {
          commit('SET_ITEMS', response.data);
        }
      })
      .catch(() => {});
  }
};

const getters = make.getters(state);

const mutations = {
  ...make.mutations(state),
  CLEAR_LOADED (state) {
    state.items = [];
    state.loaded = false;
  }
};

export default {
  namespaced: true,
  state,
  actions,
  getters,
  mutations
}
