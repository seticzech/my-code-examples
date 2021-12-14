/* Locations module */

import { make } from 'vuex-pathify'

import LocationsApi from '@/services/api/Locations.js'

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
  itemUpdate ({ commit }, item) {
    commit('UPDATE_ITEM', item);
  },
  itemsLoad ({ commit, state }) {
    if (!state.loaded) {
      commit('SET_LOADING', true);

      return LocationsApi.getAll()
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

    return state.items;
  },
  itemsRefresh ({ commit, dispatch }) {
    commit('CLEAR_LOADED');

    return dispatch('itemsLoad');
  }
};

const getters = {
  ...make.getters(state),
  list (state) {
    return state.items.map( v => {
      return { [v.id]: `${v.city.name} (${v.region.name}, ${v.country.isoCode2})` }
    });
  },
  formattedList (state) {
    return state.items.map( v => {
      return { id: v.id, name: `${v.city.name} (${v.region.name}, ${v.country.isoCode2})` }
    });
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
