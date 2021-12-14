/* Users module */

import { make } from 'vuex-pathify'

import UsersApi from '@/services/api/Users.js';

const state = () => {
  return {
    items: [],
    loaded: false,
    loading: false
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

      return UsersApi.getAll()
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

    return state.items;
  },
  itemsRefresh ({ commit, dispatch }) {
    commit('SET_LOADED', false);
    commit('CLEAR_ITEMS');

    return dispatch('itemsLoad');
  }
};

const getters = {
  ...make.getters(state),
  list (state) {
    return state.items.map( v => {
      return {
        [v.id]: `${v.lastName}, ${v.firstName}`
      }
    });
  },
  formattedList (state) {
    return state.items.map( v => {
      return {
        id: v.id,
        name: `${v.lastName}, ${v.firstName}`
      }
    });
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
