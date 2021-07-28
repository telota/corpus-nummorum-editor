// vendor
window.Vue = require('vue');
window.axios = require('axios');

// 3rd party
//import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/dist/vuetify.min.css';
import Vue from 'vue';
import Vuetify from 'vuetify';
import router from './plugins/router';
import store from './plugins/Store';
//import VueProgressBar from 'vue-progressbar'
import AxiosAjaxDetct from './plugins/AxiosAjaxDetect';
import { mapState } from 'vuex';

// this is the vuetify theming options
Vue.use(Vuetify);

// this is the progress bar settings
/*Vue.use(VueProgressBar,{
    color: '#3f51b5',
    failedColor: '#b71c1c',
    thickness: '5px',
    transition: {
        speed: '0.2s',
        opacity: '0.6s',
        termination: 300
    },
    autoRevert: true,
    inverse: false
});*/


// global component registrations here
//Vue.component("sessioninfo",        () => import("./modules/sessioninfo.vue")); // Async Load (creates seperate File!!!)

//Vue.component('sessioninfo',        require('./modules/sessioninfo.vue').default);
//Vue.component('NavigationDrawer',   require('./modules/NavigationDrawer.vue').default);

// App Components
Vue.component('app-settings',       require('./app/appSettings.vue').default);
Vue.component('app-snack',          require('./app/appSnack.vue').default);
Vue.component('app-header',         require('./app/appHeader.vue').default);
//Vue.component('app-footer',         require('./app/appFooter.vue').default);
Vue.component('app-loader',         require('./app/appLoader.vue').default);
Vue.component('app-dialog',         require('./app/appDialog.vue').default);
//Vue.component('app-navigation',     require('./app/appNavigation.vue').default);
//Vue.component('app-context-menu',   require('./app/appContextMenu.vue').default);
Vue.component('app-about',          require('./app/appAbout.vue').default);
Vue.component('app-eye-protection', require('./app/appEyeProtection.vue').default);

Vue.component('simpleDataTemplate', require('./templates/simpleDataTemplate.vue').default);
Vue.component('simpleSelectDialog', require('./templates/simpleDataSelectDialog.vue').default);
Vue.component('component-toolbar',  require('./templates/componentToolbar.vue').default);
Vue.component('component-content',  require('./templates/componentContent.vue').default);

Vue.component('ItemsTemplate',      require('./modules/ItemsTemplate.vue').default);
Vue.component('InputTemplate',      require('./modules/InputTemplate.vue').default);

Vue.component('generic-entity-template',      require('./templates/genericEntityTemplate.vue').default);
Vue.component('sheet-template',      require('./templates/sheetTemplate.vue').default);
Vue.component('small-dialog',       require('./templates/dialogSmall.vue').default);

Vue.component('Imager',             require('./modules/Imager.vue').default);
//Vue.component('coin-images',        require('./modules/coinImages.vue').default);
Vue.component('adv-img',            require('./modules/advImg.vue').default);
Vue.component('InputForeignKey',    require('./modules/InputForeignKey.vue').default);
Vue.component('SearchForeignKey',   require('./modules/SearchForeignKey.vue').default);
Vue.component('ErrorDialog',        require('./modules/ErrorDialog.vue').default);
Vue.component('ChildDialog',        require('./modules/ChildDialog.vue').default);
Vue.component('ScreenKeys',         require('./modules/ScreenKeys.vue').default);
Vue.component('files',              require('./modules/files.vue').default);
Vue.component('upload',             require('./modules/upload.vue').default);
Vue.component('subheader',          require('./modules/subheader.vue').default);
Vue.component('wysiwyg',            require('./modules/wysiwyg.vue').default);
Vue.component('keyboard',           require('./modules/keyboard.vue').default);

// Elements
Vue.component('advbtn',             require('./modules/advbtn.vue').default);
Vue.component('adv-btn',            require('./modules/btnAdv.vue').default);
Vue.component('pagination',         require('./modules/pagination.vue').default);
Vue.component('pagination-bar',     require('./modules/paginationBar.vue').default);
Vue.component('dialogbartop',       require('./modules/dialogBarTop.vue').default);
Vue.component('vertdivider',        require('./modules/vertDivider.vue').default);
Vue.component('drawer',             require('./modules/drawer.vue').default);


// Primary Entities
//Vue.component('ItemEdit',           require('./pages/entities_primary/ItemEdit.vue').default);
//Vue.component('ItemEditInput',      require('./pages/entities_primary/ItemEditInput.vue').default);
Vue.component('ItemInputTemplate',  require('./templates/itemInputTemplate.vue').default);
//Vue.component('ItemSearch',         require('./pages/entities_primary/ItemSearch.vue').default);
Vue.component('ItemCopy',           require('./pages/entities_primary/ItemCopy.vue').default);
Vue.component('ItemDetails',        require('./pages/entities_primary/ItemDetails.vue').default);
Vue.component('detailsdialog',      require('./pages/entities_primary/modules/detailsDialog.vue').default);
Vue.component('ItemGallery',        require('./pages/entities_primary/modules/coinsTypesGallery.vue').default);
Vue.component('commandbar',         require('./pages/entities_primary/modules/commandBar.vue').default);
Vue.component('coin-images',        require('./pages/entities_primary/modules/coinImages.vue').default);

// Entities
Vue.component('coins',              require('./pages/entities_primary/search/searchCoins.vue').default);
Vue.component('types',              require('./pages/entities_primary/search/searchTypes.vue').default);
Vue.component('coin-type-details',  require('./pages/entities_primary/show/dialog.vue').default);

Vue.component('denominations',      require('./pages/entities_secondary/denominations.vue').default);
Vue.component('designs',            require('./pages/entities_secondary/designs.vue').default);
Vue.component('dies',               require('./pages/entities_secondary/dies.vue').default);
Vue.component('periods',            require('./pages/entities_secondary/periods.vue').default);
Vue.component('findspots',          require('./pages/entities_secondary/findspots.vue').default);
Vue.component('hoards',             require('./pages/entities_secondary/hoards.vue').default);
Vue.component('legends',            require('./pages/entities_secondary/legends.vue').default);
Vue.component('materials',          require('./pages/entities_secondary/materials.vue').default);
Vue.component('mints',              require('./pages/entities_secondary/mints.vue').default);
Vue.component('monograms',          require('./pages/entities_secondary/monograms.vue').default);
Vue.component('owners',             require('./pages/entities_secondary/owners.vue').default);
Vue.component('persons',            require('./pages/entities_secondary/persons.vue').default);
Vue.component('regions',            require('./pages/entities_secondary/regions.vue').default);
Vue.component('standards',          require('./pages/entities_secondary/standards.vue').default);
Vue.component('symbols',            require('./pages/entities_secondary/symbols.vue').default);
Vue.component('tribes',             require('./pages/entities_secondary/tribes.vue').default);

Vue.component('references',         require('./pages/tools/bibliography.vue').default);
Vue.component('objectgroups',       require('./pages/tools/objectgroups.vue').default);

// Templates
Vue.component('dialog-template',    require('./templates/dialogTemplate.vue').default);


// Own global JS variables/functions
import editor_format from './plugins/format';
import handlers from './plugins/handlers';
import localization from './plugins/localization';

Vue.use(editor_format);
Vue.use(handlers);
Vue.use(localization);


const editor = new Vue({

    vuetify: new Vuetify({

        theme: {
            dark: true,
            themes: {
                light: {
                    primary:    '#757575',
                    //secondary:  '#666666',
                    accent:     '#666666',

                    sysbar:     '#d8d8d8',
                    appbar:     '#f5f5f5',
                    invert:     '#111111',
                    marked:     '#aaa',
                    imgbg:      '#606060',

                    blue_prim:  '#006699',
                    blue_sec:   '#0099cc',

                    grey_prim:  '#d8d8d8',
                    grey_sec:   '#f5f5f5',
                    grey_trip:  '#e1e1e1',

                    input_hover: '#111',
                    input_main: '#838383',
                    input_bg: '#d4d4d4',

                    app_bg:     '#aaaaaa',
                    header_bg:  '#e6e6e6',
                    header_hover:'#d2d2d2',
                    header_marked:'#0099cc',
                    drawer_bg:  '#d0d0d0',
                    sheet_bg:   '#fff',
                    tile_bg:    '#e6e6e6'
                },
                dark: {
                    app_bg:     '#181818',
                    primary:    '#8a8a8a',
                    //secondary:  '#b0bec5',
                    accent:     '#8c9eff',

                    sysbar:     '#363636',
                    appbar:     '#272727',
                    invert:     '#eeeeee',
                    marked:     '#666666',
                    imgbg:      '#606060',

                    blue_prim:  '#006699',
                    blue_sec:   '#0099cc',

                    grey_prim:  '#363636',
                    grey_sec:   '#272727',
                    grey_trip:  '#1e1e1e',

                    input_hover: '#eee',
                    input_main: '#bcbcbc',
                    input_bg: '#303030',

                    app_bg:     '#101010',
                    header_bg:  '#363636',
                    header_hover:'#666666',
                    header_marked:'#0099cc',
                    drawer_bg:  '#262626',
                    sheet_bg:    '#1c1c1c',
                    tile_bg:    '#363636'
                },
            },
        },

        icons: {
            iconfont: 'md'
        }
    }),

    el: '#editor',
    router,
    store,


    data () {
        return {
            loading: false,
            router_view_refresh: 0,
            drawer: {
                active: true,
                mini: true
            },
            error: {
                active: false
            },
            snack: {
                color: null,
                message: null
            },

            dialog: {
                imprint: false,
                copyright: false
            },

            child_dialog: {
                width: '75%',
                fullscreen: false
            },

            preferences: {
                show_filters: false,
            },

            position: {
                totalWidth: 0,
                totalHeight: 0,
                top: 0,
                left: 0,
                width: 0,
                height: 0
            },

            // Settings
            appName: 'CN Editor',
            baseURL: null,
            sparql: null,
            system: {},
            language: 'en',
            user: {},
            log: {},
            settings: {},
            digilib: (path) => 'https://digilib.bbaw.de/digitallibrary/servlet/Scaler?fn=silo10/thrakien/' + path + '&dw=500&dh=500'
        }
    },

    computed: {
        labels () {
            return this.$localization[this.language]
        },

        colors () {
            return this.$vuetify.theme?.themes?.[this.$vuetify.theme.dark ? 'dark' : 'light'] ?? {}
        }
    },

    created () {
        this.drawer.active = this.$vuetify.breakpoint.mdAndUp
    },

    mounted () {

        // Remove Scrollbar (Vuetify Default)
        let el = document.getElementsByTagName('html')[0]
        el.style.overflowY = 'hidden'

        // Add Resize-Watcher to get client size
        //this.onResize()
        //window.addEventListener('resize', this.onResize)
        //window.addEventListener('error', this.errorHandler)

        // Remove initial Loader
        el = document.getElementById('initial-loader')
        if (el) {
            setTimeout(() => {
                el.style.opacity = 0
                setTimeout(() => { el.remove() }, 501);
            }, 250);
        }
    },

    methods: {
        async changeSettings (key, value) {
            this.loading = true

            console.log('Settings: updating "' + key + '" => "' + value + '"')
            this.settings[key] = value

            if (key === 'color_theme') { this.$vuetify.theme.dark = this.settings.color_theme === 1 ? true : false }
            else if (key === 'language') { this.language = this.settings.language}

            const response = await this.DBI_INPUT_POST('dashboard', 'input', this.settings);
            if (response.success) { this.$store.dispatch('showSnack', { message: 'Presets updated', color: 'success' }) }

            this.loading = false
        },

        logout () {
            axios.post('logout').then(() => { window.location.href = 'login' });
        },

        snackbar (message, state) {
            const self = this
            message = typeof message === 'string' ? message : (message[this.language] ? message[this.language] : message.en)
            self.snack = { color: state ? state : null, message: message }
            setTimeout (() => { self.snack = { color: null, message: null }}, 4000)
        },

        label (string) {
            if (string) {
                const response = []
                string.split(',').forEach((value) => {
                    if (this.labels[value]) {
                        response.push(this.labels[value])
                    }
                    else {
                        response.push(value.slice(0, 1).toUpperCase() + value.slice(1).replaceAll('_', ' '))
                    }
                })
                return response.join(" ")
            }
            else {
                return 'NONE'
            }
        },

        setTitle (newTitle) {
            if (newTitle) document.title = 'CN Editor | ' + newTitle.trim()
        },

        onResize () {
            const win = window
            const totalWidth = win.innerWidth
            const totalHeight = win.innerHeight

            const el = document.getElementById('router-space')
            const bounds = el?.getBoundingClientRect()
            const top = bounds?.top ?? 0
            const left = bounds?.left ?? 0
            const width = bounds?.width ?? 0

            this.position = {
                totalWidth,
                totalHeight,
                top,
                left,
                width,
                height: (totalHeight - top - 30)
            }
        },

        openInNewTab (link) {
            if (link) { window.open(link) }
        },

        /*errorHandler (error) {
            console.log('HALLO!!!')
            console.log('TEST:' + JSON.stringify(error))
        },*/

        // JK: DBI-API-AXIOS Functions ----------------------------------------------------------------------------------
        async DBI_SELECT_GET (entity, id) {
            if (entity) {
                const self = this
                const source = 'dbi/' + entity + (id ? ('/' + id) : '')
                let dbi = {}

                console.log ('AXIOS: Fetching Data from "' + source + '" using GET. Awaiting Server Response ...');

                await axios.get(source)
                    .then((response) => {
                        dbi = response?.data
                        console.log('AXIOS Response:', response)
                    })
                    .catch((error) => {
                        self.AXIOS_ERROR_HANDLER(error)
                    })

                return dbi
            }
        },

        async DBI_SELECT_POST (entity, params, search) {
            if (entity) {
                const self = this
                const source = 'dbi/' + entity
                let dbi = {}

                //console.log ('AXIOS: Fetching Data from "' + source + '" using POST. Awaiting Server Response ...');

                if (search) {
                    for (const[key, value] of Object.entries(search)) {
                        params[key] = value
                    }
                }

                await axios.post(source, Object.assign ({}, params))
                    .then((response) => {
                        dbi = response?.data
                        console.log('AXIOS', source, response)
                    })
                    .catch((error) => {
                        self.AXIOS_ERROR_HANDLER (error)
                    })

                return dbi
            }
        },

        async DBI_INPUT_POST (entity, mode, item) {
            if (entity && ['input', 'delete', 'connect'].includes(mode)) {
                const self = this
                let dbi = {}
                const is_ok = entity === 'dashboard' ? true : this.InputPermissionCheck(item)

                if (is_ok) {
                    this.$root.loading = true
                    const url = 'dbi/' + entity + '/' + mode

                    console.log ('AXIOS: Sending Data to "' + url + '" using POST. Awaiting Server Response ...')

                    await axios.post(url, Object.assign ({}, item))
                        .then((response) => {
                            if (response.data.success) {
                                dbi = response?.data
                                console.log('RESPONSE CHECK: Server accepted input as valid:', response)
                            }
                            else {
                                self.snackbar('Validation Issue!', 'error')
                                self.error = { active: true, validation: response.data?.[self.language] ? response.data[self.language] : response.data }
                                console.log('RESPONSE CHECK: Validation Issue: Server declined input as invalid:', response)
                            }
                        })
                        .catch((error) => { self.AXIOS_ERROR_HANDLER(error) })

                    this.$root.loading = false
                }

                return dbi
            }
            else {
                alert('ERROR: wrong Parameters given.')
            }
        },

        AXIOS_ERROR_HANDLER (error) {
            this.snackbar('System Error!', 'error')
            console.log('RESPONSE CHECK: System Error:')
            console.log(error)

            this.error = {
                active:     true,
                validation: null,
                resource:   error.config   ? (error.config.url ? error.config.url : 'unknown') : 'unknown',
                exception:  error.response ? error.response.data.exception : (error.request ? error.request.data.exception : 'unknown'),
                message:    error.response ? error.response.data.message   : (error.request ? error.request.data.message   : (error.message ? error.message : '--') ),
                params:     error.config   ? (error.config.data ? error.config.data : 'none given') : 'none given'
            }
        },

        InputPermissionCheck (item) {
            const id_user = this.user?.id ? this.user.id : 0
            const rank_user = this.user?.level ? this.user.level : 10
            let rank_required = 12
            let message = '\nYou are not permitted to create or edit any object.\nPlease contact the team administration for more information.'

            // Calculate required minimum level
            if (item?.creator) {
                if (item.public === 1) {
                    console.log('Input Permission Check: item is already published => set required Level to 18.')
                    message = '\nYou are not permitted to edit an already published object.\nPlease contact an authorized team member if you consider an update necessary.'
                    rank_required = 18
                }
                else if (item.creator === id_user) {
                    console.log('Input Permission Check: current user is creator of selected not published item => set required level to 11.')
                    rank_required = 11
                }
                else {
                    message = '\nYou are not permitted to edit an object created by another user.\nPlease contact the creator or an authorized team member if you consider an update necessary.'
                    console.log('Input Permission Check: current user is not creator of selected not published item => set required level to 12.')
                }
            }
            else {
                console.log('Input Permission Check: item has no creator => set required Level to 11.')
                rank_required = 11
            }

            // Check reuqired level and user level
            if (rank_user >= rank_required) {
                console.log('Input Permission Check passed!')
                return true
            }
            else {
                console.log('Input Permission Check failed!')
                this.snackbar('Validation Issue!', 'error')
                this.error = { active: true, validation: message }
                return false
            }
        }
    }
});
