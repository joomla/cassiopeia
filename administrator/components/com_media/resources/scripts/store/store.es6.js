import Vue from 'vue';
import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate';
import state from './state.es6';
import * as getters from './getters.es6';
import * as actions from './actions.es6';
import mutations from './mutations.es6';
import { persistedStateOptions } from './plugins/persisted-state.es6';

Vue.use(Vuex);

// A Vuex instance is created by combining the state, mutations, actions, and getters.
export default new Vuex.Store({
  state,
  getters,
  actions,
  mutations,
  plugins: [createPersistedState(persistedStateOptions)],
  strict: (process.env.NODE_ENV !== 'production'),
});
