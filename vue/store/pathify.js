/* Vuex-pathify configuration */

import pathify from 'vuex-pathify'

// for options see: https://davestewart.github.io/vuex-pathify/#/setup/config
// mapping: /thing -> getter: thing, mutation: SET_THING, action: setThing
pathify.options.mapping = 'standard'; // Default

// re-export pathify.
export default pathify
