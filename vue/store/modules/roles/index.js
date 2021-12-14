/* Roles module */

import { make } from 'vuex-pathify'

import RolesApi from '@/services/api/Roles.js';

const state = () => {
  return {
    items: [],
    loaded: false,
    loading: false
  }
};

const actions = {
  itemAppend ({ commit }, item) {
    return RolesApi.post(item)
      .then( response  => {
        if (response.data) {
          commit('APPEND_ITEM', response.data);
        }
      })
      .catch(() => {});
  },
  itemUpdate ({ commit }, item) {
    return RolesApi.put(item.id, item)
      .then( response  => {
        if (response.data) {
          commit('UPDATE_ITEM', response.data);
        }
      })
      .catch(() => {});
  },
  itemsLoad ({ commit, state }) {
    if (!state.loaded) {
      commit('SET_LOADING', true);

      return RolesApi.getAll()
        .then(response => {
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
    commit('SET_LOADED', false);
    //commit('CLEAR_ITEMS');

    return dispatch('itemsLoad');
  }
};

const getters = {
  ...make.getters(state),
  list (state) {
    return state.items.translatedList('name');
  },
  formattedOptions (state) {
    return state.items.translatedArrayOfObjectsList('name', 'value', 'text');
  }
};

const mutations = {
  ...make.mutations(state),
  APPEND_ITEM ( state, payload ) {
    state.items.push(payload);
  },
  CLEAR_ITEMS ( state ) {
    state.items = [];
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
