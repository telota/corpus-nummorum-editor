<template>
<div>
    <dialog-template
        :dialog="dialog"
        :text="$root.label(entity)"
        :closing="closing"
        :select="select"
        :selected="selected"
        v-on:close="$emit('close')"
    >
        <!-- Toolbar -->
        <div class="header_bg" :class="'component-toolbar' + (dialog ? ' component-toolbar-dialog' : '')">

            <!-- Search -->
            <pagination-bar
                :offset="pagination.offset"
                :limit="pagination.limit"
                :count="pagination.count"
                :sortby="pagination.sort_by"
                :sorters="sorters"
                :layout="layout"
                :layouts="layouts"
                :loading="loading"
                :dialog="dialog"
                v-on:reload="setItems(true)"
                v-on:offset="setOffset"
                v-on:limit="setLimit"
                v-on:sortby="setSortBy"
                v-on:layout="(emit) => { this.layout = emit }"
                v-on:add="$emit('add', true)"
            >
                <template v-slot:right>
                    <template v-if="!dialog && $root.user.level > 17">
                        <!-- Publisher Functions -->
                        <v-slide-x-reverse-transition>
                            <adv-btn
                                v-if="publisher"
                                :icon="checked_state ? 'radio_button_unchecked' : 'radio_button_checked'"
                                :tooltip="(checked_state ? 'Deselect' : 'Select') + ' all ' + labels[entity]"
                                :disabled="items[0] ? false : true"
                                color-main="blue_prim"
                                color-hover="blue_sec"
                                color-text="white"
                                @click="checked_state = !checked_state; SetChecked(checked_state);"
                            />
                        </v-slide-x-reverse-transition>

                        <v-slide-x-reverse-transition>
                            <adv-btn
                                v-if="publisher"
                                icon="publish"
                                :tooltip="'Publish selected ' + labels[entity]"
                                :disabled="!checked.filter((check) => { return check.state === true })[0]"
                                color-main="blue_prim"
                                color-hover="blue_sec"
                                color-text="white"
                                @click="Publish(checked, 1)"
                            />
                        </v-slide-x-reverse-transition>

                        <!-- Publisher Toggle -->
                        <adv-btn
                            :icon="publisher ? 'public_off' : 'public'"
                            :tooltip="publisher ? 'Hide Publisher' : 'Show Publisher'"
                            color-hover="header_hover"
                            @click="togglePublisher()"
                        />
                    </template>

                    <!-- Switch Coin/Types -->
                    <a :href="'/editor#/' + (entity === 'coins' ? 'types' : 'coins') + '/' + (publisher ? 'publish' : 'search')">
                        <adv-btn
                            :icon="entity === 'coins' ? 'blur_circular' : 'copyright'"
                            :tooltip="entity === 'coins' ? 'Go to Types' : 'Go to Coins'"
                            color-hover="header_hover"
                        />
                    </a>
                </template>
            </pagination-bar>

        </div>

        <!-- Filter Drawer -->
        <drawer
            header
            :collapse="searchCounter"
            v-on:expand="onDrawerExpand"
            v-on:search="runQuery()"
            v-on:clear="SearchDefaults(true)"
        >
            <!-- Filters -->
            <template v-slot:content>
                <slot name="filters" />
            </template>
        </drawer>

        <!-- Results Content -->
        <div :class="'component-content' + (dialog ? ' component-content-dialog' : '')" style="padding-left: 40px;">
            <v-fade-transition>
                <!-- Error -->
                <div
                    v-if="error"
                    class="mt-10 headline d-flex justify-center"
                    v-html="'Sorry, an error occured.<br/>Please reload and retry!'"
                />

                <!-- Results -->
                <div v-else-if="items[0]">

                    <!-- Trading Cards || Index Cards -->
                    <div v-if="['tiles', 'cards', 'table'].includes(layout)" style="padding-left: 43px; padding-top: 3px">
                        <v-row class="ma-0 pa-0">
                            <v-col
                                v-for="(item, i) in items"
                                :key="i + ' ' + layout"
                                cols="12"
                                :sm="layout === 'tiles' ? 6 : 12"
                                :md="layout === 'tiles' ? 3 : 12"
                                :lg="layout === 'tiles' ? 2 : 12"
                            >
                                <component
                                    :is="layouts[layout].component"
                                    :key="layout + item.id + entity + (publisher ? 1 : 0) + item.public + (checked[i].state ? 1 : 0)"
                                    :entity="entity"
                                    :item="item"
                                    :publisher="publisher"
                                    :checked="checked[i].state"
                                    v-on:publish="(emit) => { Publish ([{ id: item.id, state: true }], (item.public === 0 ? 1 : 0)) }"
                                    v-on:checked="checked[i].state = !checked[i].state"
                                ></component><!-- v-on:inheritor="(emit) => { details_dialog = { entity: 'types', id: emit } }"-->
                            </v-col>
                        </v-row>
                    </div>

                </div>

                <!-- No results -->
                <div
                    v-else-if="queried"
                    class="mt-10 headline d-flex justify-center"
                    v-html="loading ? 'Loading ...' : 'Sorry, no matching Records!'"
                />

                <!-- Start Screen -->
                <div v-else class="start-screen">
                    <v-card
                        tile
                        raised
                        class="header_bg"
                        style="height: 100%; position: relative"
                    >
                        <div class="pa-3">
                            <div class="text-center title text-uppercase" v-text="'Search ' + entity" />
                            <a :href="'/editor#/' + (entity === 'coins' ? 'types' : 'coins') + '/search'">
                                <div class="text-center caption mb-4" v-text="'Looking for ' + (entity === 'coins' ? 'types' : 'coins') + '?'" />
                            </a>

                            <v-text-field clearable dense
                                v-model="search.id"
                                label="ID"
                                class="mb-5"
                                v-on:keyup.enter="runQuery()"
                                style="width: 50%"
                            />

                            <v-text-field clearable dense
                                v-model="search.q"
                                ref="stringSearchInput"
                                :label="$root.label('keywords')"
                                append-icon="keyboard"
                                class="mb-n2"
                                v-on:keyup.enter="runQuery()"
                                v-on:click:append="searchStringKeyboard = !searchStringKeyboard"
                            />
                            <v-expand-transition>
                                <div v-if="searchStringKeyboard" class="d-flex justify-center">
                                    <keyboard
                                        :string="search.q"
                                        layout="el_uc"
                                        small
                                        hide_options
                                        v-on:input="(emit) => { search.q = emit }"
                                    ></keyboard>
                                </div>
                            </v-expand-transition>
                        </div>

                        <!-- Search Button -->
                        <div
                            class="text-center mt-1 pa-3"
                            style="position:absolute; bottom: 0; width: 100%"
                        >
                            <div class="text-center body-2 mb-3" v-text="'Please check the left sidebar for advanced filters.'" />
                            <v-btn
                                tile
                                block
                                dark
                                color="blue_prim"
                                class="title"
                                v-text="'search'"
                                :width="350"
                                @click="runQuery()"
                            />
                        </div>
                    </v-card>
                </div>
            </v-fade-transition>
        </div>

    </dialog-template>
</div>
</template>


<script>

import h            from './search.js'
import dialogTemplate  from '../../../templates/dialogTemplate.vue'
import tradingcard  from './searchLayoutTradingcard.vue'
import indexcard    from './searchLayoutIndexcard.vue'
import tablerow     from './searchLayoutTablerow.vue'

export default {
    components: {
        tradingcard,
        indexcard,
        tablerow,
        dialogTemplate
    },

    data () {
        return {
            closing:            0,
            queried:            false,
            loading:            false,
            error:              false,

            items:              [],

            search:             h.constructParams(),
            searchCounter:      0,
            pagination:         {},

            checked_state:      false,
            checked:            [],

            layout:             this.$store.state.searchLayout,
            layouts:            {
                tiles: { component: 'tradingcard',    text: 'Tiles',   icon: 'view_comfy' },
                cards: { component: 'indexcard',      text: 'Cards',   icon: 'view_list' },
                table: { component: 'tablerow',       text: 'Text',    icon: 'view_column' }
            },

            // selectlists
            selects:            {
                state_public:       [
                    { value: 'all', text: 'All' },
                    { value: 0, text: 'Not published' },
                    { value: 2, text: 'Publishing requested' },
                    { value: 1, text: 'Published' },
                    { value: 3, text: 'Deleted' }
                ],
                state_yes_no:       [
                    { value: null, text: 'All' },
                    { value: 0, text: 'No' },
                    { value: 1, text: 'Yes' }
                ]
            },

            searchStringKeyboard: false,
            searchStringKeyboardF: false,

            queryDialog: {
                show: false,
                text: null,
                page: false
            }
        }
    },

    props: {
        entity:         { type: String, default: 'coins' },
        publisher:      { type: Boolean, default: false },
        dialog:         { type: Boolean, default: false },
        select:         { type: Boolean, default: false },
        selected:       { type: [Number, String], default: null }
    },

    computed: {
        language () { return this.$root.language },
        labels () { return this.$root.labels },

        sorters () {
            const sorters = [
                { value: 'id', text: 'ID' },
                { value: 'date', text: this.labels['date'] }
            ]

            if (this.entity === 'coins') {
                sorters.push(
                    { value: 'weight', text: this.labels['weight'] },
                    { value: 'diameter', text: this.labels['diameter'] }
                )
            }

            sorters.push(
                { value: 'mint', text: this.labels['mint'] },
                { value: 'ruler', text: this.labels['authority_person'] },
                { value: 'created', text: this.labels['created_at'] },
                { value: 'updated', text: this.labels['updated_at'] }
            )

            return sorters
        },

        query () {
            /*if (this.routed) return {}
            else return h.constructRequest(h.constructParams(this.routedQuery))*/
            return {}
        },

        queryGiven () {
            return false //Object.keys(this.routedQuery)[0] ? true : false
        },

        cachedQueries () {
            return this.$store.state.cache[this.entity].map((query) => {
                let q = {}
                let pPublic = ''
                let sortBy = ''

                query.split('&').forEach((param) => {
                    const split = param.split('=')
                    const key = split.shift()
                    const val = split.join('=')

                    if (key === 'public') pPublic = val.replaceAll('-', ', ')
                    else if (key === 'sort_by') sortBy = val.replace('.', ', ')
                    else if (key !== 'limit') q[key] = (q[key] ? (q[key] + ', ') : '') + val
                })

                let text = Object.keys(q).map((key) => { if (key) return key + ': ' + q[key] }).join('; ') ?? ''
                if (text) text += '; '
                text += 'published: ' + pPublic + '; sort: ' + sortBy

                return {
                    value: query,
                    text
                }
            })
        },

        storedQueries () {
            let queries = this.$root.settings?.queries
            if (!queries) return {}
            queries = JSON.parse(queries)
            return queries?.[this.entity] ?? {}
        }
    },

    watch: {
        entity: function () { this.Init() },
        query: function () {
            if (!this.queryGiven) this.Init()
            this.handleQuery()
        }
    },

    created () {
        this.Init()
    },

    mounted () {
        this.$refs.stringSearchInput.$refs.input.focus()
        //if (this.routed) this.handleQuery()
    },

    methods: {
        Init () {
            this.queried = false
            this.SearchDefaults()
            this.items = []
            this.pagination= {
                sort_by: 'id.DESC',
                count:  0,
                offset: 0,
                limit:  12
            }
        },

        SearchDefaults (message) {
            this.search = h.constructParams()
            if (message) this.$store.dispatch('showSnack', { color: 'blue_sec', message: 'Query Parameters set to default!' })
        },

        async handleQuery () {
            if (this.publisher && !this.queryGiven) this.showPublisher(true)
            else if (this.queryGiven) {
                await this.getItems(this.query)
                this.search = h.constructParams(this.query)
            }
        },

        queryIncrement () {
            /*if (this.routed) {
                const i = parseInt(this.$route.query?.i) ?? 0
                this.searchCounter = this.searchCounter + 1 + (this.searchCounter + 1 === i ? 1 : 0)
            }
            else ++this.searchCounter*/
        },

        runQuery () {
            this.queryIncrement()
            this.cacheCurrentQuery()
            this.processQuery()
        },

        processQuery () {
            const query = { ...this.search }
            query.offset = this.pagination.offset
            query.limit = this.pagination.limit
            query.sort_by = this.pagination.sort_by

            const request = h.constructRequest(query)
            //console.log('request', request)

            if (this.router)    this.$router.push({ query: Object.assign({}, request, { i: this.searchCounter }) })
            else                this.getItems(request)
        },

        async getItems (query) {
            this.queried = true
            this.error  = false
            this.loading = this.$root.loading = true

            const dbi = await this.$root.DBI_SELECT_POST(this.entity, query)

            if (dbi?.contents) {
                this.pagination= {
                    sort_by: dbi.pagination?.sort_by?.replace(' ', '.'),
                    count:  dbi.pagination?.count,
                    offset: dbi.pagination?.offset,
                    limit:  dbi.pagination?.limit
                }
                this.items = dbi.contents

                if (dbi.contents[0]) {
                    this.items = dbi.contents
                    this.SetChecked (false)
                }
                else {
                    this.items = []
                    this.$store.dispatch('showSnack', { message: 'No items found!' })
                }
            }
            else {
                console.error('ERROR', dbi)
                this.error = true
            }

            this.loading = this.$root.loading = false
            this.scrollToTop()
        },

        scrollToTop () {
            document.getElementById('primary-results-container')?.scrollTo(0, 0)
        },

        SetChecked (state) {
            const checkers = []
            this.checked = []

            this.items.forEach((item) => {
                checkers.push({ id: item.id, state: [0, 2].includes(item.public) ? state : false })
            })
            this.checked = checkers
        },

        togglePublisher () {
            if (!this.publisher) this.showPublisher()
            else this.$router.push('/' + this.entity + '/search')
        },

        showPublisher (replace) {
            if (this.queryGiven) {
                let q = window.location.href?.split('?') ?? [null]
                q.shift()
                q = q.join('?')
                this.$router.push('/' + this.entity + '/publish?' + q)
            }
            else {
                const path = {
                    path: '/' + this.entity + '/publish',
                    query: {
                        limit: this.pagination.limit,
                        sort_by: 'updated.DESC',
                        public: 2
                    }
                }

                this.$router[replace ? 'replace' : 'push'](path)
            }
        },

        async Publish (input, mode) {
            let items = input.filter(item => { return item.state === true })

            if (items [0]) {
                let confirmed = mode === 1 ? true : (confirm(this.labels[this.entity.slice(0, -1)] + ' ' + input[0].id + ' will be ' + (mode === 3 ? 'deleted!' : 'unpublished!') ));

                if (confirmed === true) {
                    const response = await this.$root.DBI_INPUT_POST('publish', 'input', { entity: this.entity, items: items.map((item) => { return item.id }), mode: mode });

                    if (response.success) {
                        this.$store.dispatch('showSnack', { color: 'success', message: response.success })
                        this.processQuery()
                    }
                }
            }
            else {
                this.$store.dispatch('showSnack', { color: 'error', message: 'No items selected!' })
                this.processQuery();
            }
        },

        setLayout (value) {
            this.$store.commit('set_searchLayout', value)
            this.layout = value
        },

        setOffset (value) {
            this.pagination.offset = value
            this.runQuery(value)
        },

        setLimit (value) {
            this.pagination.limit = value
            this.runQuery()
        },

        setSortBy (value) {
            this.pagination.sort_by = value
            this.pagination.offset = 0

            this.runQuery()
        },

        getQueryString (page) {
            let query = window.location.href?.split('?') ?? [null]
            query.shift()
            const pagination = '(i' + (page ? '' : '|limit|offset') + ')=[^&]+&?'
            return query.join('?').replaceAll(new RegExp(pagination, 'g'), '').replaceAll('state_public=', 'public=')
        },

        cacheCurrentQuery () {
            /*if (this.routed) {
                const query = this.getQueryString()
                console.log('cache', query, query.length)

                if (query) {
                    const value = this.$store.state.cache[this.entity].slice(0, 49)
                    if (value[0] !== query) {
                        value.unshift(query)
                        this.$store.commit('setCache', { key: this.entity, value })
                    }
                }
            }*/
        },

        showQueryDialog () {
            this.queryDialog = {
                show: true,
                page: false,
                text: '' + Date.now()
            }
            setTimeout(() => {
                this.$refs.storeQueryName.$refs.input.focus()
                this.$refs.storeQueryName.$refs.input.select()
            }, 0)
        },

        saveQuery () {
            console.log(this.queryDialog)
            const queries = { ...this.storedQueries }
            queries[this.queryDialog.text.trim()] = this.getQueryString(this.queryDialog.page)
            console.log(queries)
            this.$root.changeSettings('queries', JSON.stringify({ [this.entity]: queries }))
            this.queryDialog.show = false
        },

        deleteStoredQuery (key) {
            const queries = { ...this.storedQueries }
            delete(queries[key])
            this.$root.changeSettings('queries', JSON.stringify({ [this.entity]: queries }))
        },

        restoreCachedQuery (query) {
            this.SearchDefaults()
            //console.log(query)
            window.location.href = '/editor#/' + this.entity + '/' + (this.publisher ? 'publish' : 'search') + '?' + query
            this.queryIncrement()
        },

        onDrawerExpand (expand) {
            if (expand) {
                if ([null, undefined].includes(this.activeTab)) setTimeout(() => { this.activeTab = this.cachedTab }, 350)
            }
            else {
                this.cachedTab = this.activeTab
                this.activeTab = null
            }
        }
    }
}

</script>

<style scoped>

    .start-screen {
        position: fixed;
        width: 500px;
        left: 50%;
        margin-left: -250px;
        height: 340px;
        top: 50%;
        margin-top: -180px;
    }

    .search-divider {
        opacity: 0.15;
        width: 100%;
        height: 0.5px;
    }

    .search-field-50 {
        width: 100%
    }

    .side-l {
        padding-right: 10px;
    }

    .side-r {
        padding-left: 10px;
    }

</style>
