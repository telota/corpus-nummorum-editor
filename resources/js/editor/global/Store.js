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

//  import StorModules here (you may choose an alias if you want):
//import attributes   from './StoreModules/entityattributes';
import lists        from './StoreModules/entitylists';
import screenkeys   from './StoreModules/screenkeys';
//import objects      from './StoreModules/objects';

Vue.use(Vuex);


export default new Vuex.Store ({

    modules: 
    {
        //attributes,
        lists,
        screenkeys,
        //objects
    },

    state: 
    {        
        // basic form
        ItemsPerPage:           [6, 12, 18, 24, 36, 48],    // JK: Limit of Items in "DataItems"
        ItemsScales:            [                           // JK: Grid of Cards in "DataItems"
            {sm: 3, md: 2, lg: 1, text: 'small'},
            {sm: 4, md: 3, lg: 2, text: 'medium'},
            {sm: 6, md: 6, lg: 4, text: 'large'}
        ],

        // SearchItem
        displayMode:            0,
        previousSearches:       {
            coins: [],
            types: []
        },

        // SimpleDataTemplate
        std_display:            null,
        std_filters:            true,

        // Old Stuff - needs to be investigated!
        dt_ItemsPerPage:        [10, 20, 50],               // JK: number of items per page for data table - OLD OLD OLD !

        // InputCheck
        InputCheckDialog:       false,
        InputCheckMessage:      null,

        // Starterkit Defaults --------------------------------------*/
        breadcrumbs:            [],
        showLoader:             false,
    },

    actions: {},

    mutations: 
    {
        set_display_mode (state, input) {
            state.displayMode = input
        },

        set_previous_search (state, input) {
            state.previousSearches[input.entity] = input.data
        },

        set_std_display (state, input) {
            state.std_display = input
        },

        set_std_filters (state) {
            state.std_filters = !state.std_filters
        },

        // Starterkit Defaults --------------------------------------
        setBreadcrumbs(state, items) { // breadcrumbs
            items.unshift({label:'Editor',to:{name:'dashboard'}});
            state.breadcrumbs = items;
        },
        // loader
        showLoader(state) { // loader
            state.showLoader = true
        },
        hideLoader(state) {
            state.showLoader = false
        },
    },
    
    getters: 
    {
        // Starterkit Defaults --------------------------------------
        getBreadcrumbs: state => { // get breadcrumbs
            return state.breadcrumbs
        },
        // loader
        showLoader: state => {
            return state.showLoader
        },
    }

});
