/*
|--------------------------------------------------------------------------
| VueX Store Main file
|--------------------------------------------------------------------------
|
| Here is where you can control and manipulate the VueX Store. Please create
| separate files for additional non-global content (e.g. specific requests).
| You can store these additional files in the StoreModules directory. Don't
| forget to register these files in the modules section of this main file.
|
*/


// import Vue components
import Vue from 'vue';
import Vuex from 'vuex';

import lists        from './StoreModules/entitylists';
import screenkeys   from './StoreModules/screenkeys';
import storage      from './StoreModules/storage';

Vue.use(Vuex);


export default new Vuex.Store ({

    modules: {
        lists,
        screenkeys,
        storage
    },

    state: {
        appName:    'CN Editor',
        baseURL:    null,
        sparql:     null,
        language:   'en',

        user:       {},
        log:        {},
        settings:   {},
        system:     {},

        snack: {
            message: null,
            color: null
        },

        showAbout: false,

        cache: {
            coins: [],
            types: [],
            details: []
        },

        searchLayout:   'tiles',
        itemsPerPage:   [6, 12, 18, 24, 36, 48],

        detailsDialog: {
            show: false,
            entity: null,
            id: null
        }
    },

    actions: {
        showSnack ({ commit }, payload) {
            if (payload?.message) {
                commit('setSnack', { message: payload.message, color: payload?.color ?? null })
                setTimeout (() => { commit('setSnack', {}) }, 5000)
            }
        }
    },

    mutations: {
        setAppName (state, input) { state.appName = input },
        setBaseURL (state, input) { state.baseURL = input },
        setSparql (state, input) { state.sparql = input },
        setLanguage (state, input) { state.language = input },

        setUser (state, input) { state.User = input },
        setLog (state, input) { state.Log = input },
        setSettings (state, input) { state.settings = input },
        setSystem (state, input) { state.System = input },

        setSnack (state, input) {
            state.snack = {
                message: input?.message ? (typeof input.message === 'string' ? input.message : (input.message[state.language] ?? input.message.en)) : null,
                color: input?.color ?? null
            }
        },

        setAbout (state, input) { state.showAbout = input ?? false },

        setCache (state, input) {
            if (input.key && input.value) state.cache[input.key] = input.value //; console.log(state.cache[input.key])
            else console.log('ERROR: Store-setCache requires { key, value }')
        },

        pushToCache (state, input) {
            if (input.key && input.value) {
                const history = [ ...state.cache[input.key] ]
                history.unshift({ ...input.value })
                state.cache[input.key] = history
            }
            else console.log('ERROR: Store-setCache requires { key, value }')
        },

        set_searchLayout (state, input) {
            state.searchLayout = input
        },

        setDetailsDialog (state, input) {
            if (!input) state.detailsDialog = {
                show: false,
                entity: null,
                id: null
            }
            else if (!['types', 'coins'].includes(input.entity)) console.error('Store: Details Dialog: invalid entity ' + input.entity)
            else {
                state.detailsDialog = {
                    show: true,
                    entity: input.entity,
                    id: input.id
                }
                //state.cache.details.unshift({ entity: input.entity, id: input.id })
            }
        },
    },

    getters: {
    }

});
