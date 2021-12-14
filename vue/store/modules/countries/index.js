/* Countries module */

import { make } from 'vuex-pathify'

import CountriesApi from '@/services/api/Countries.js'

const state = () => {
  return {
    deleted: false,
    items: [],
    loaded: false,
    loading: false,
  }
};

const actions = {
  itemAppend ({ commit }, item) {
    commit('APPEND_ITEM', item);
  },
  itemRefresh ({ commit }, id) {
    return CountriesApi.get(id)
      .then( response => {
        if (response.data) {
          commit('UPDATE_ITEM', response.data);
        }
      })
      .catch(() => {});
  },
  itemUpdate ({ commit }, item) {
    commit('UPDATE_ITEM', item);
  },
  itemsLoad ({ commit, state }) {
    if (!state.loaded) {
      commit('SET_LOADING', true);

      return CountriesApi.getAll()
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
  }
};

const getters = {
  ...make.getters(state),
  countriesOptions (state) {
    return state.items.map( i => { return { value: i.id, text: i.name} });
  }
};

const mutations = {
  ...make.mutations(state),
  APPEND_ITEM ( state, payload ) {
    state.items.push(payload);
  },
  CLEAR_LOADED (state) {
    state.items = [];
    state.loaded = false;
  },
  UPDATE_ITEM ( state, payload ) {
    state.items = state.items.map( i => {
      return (i.id === payload.id) ? payload : i;
    });
  }
};

export default {
  namespaced: true,
  state,
  actions,
  getters,
  mutations
}
