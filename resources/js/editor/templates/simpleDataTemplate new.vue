<template>
<div>
    <!-- Toolbar -->
    <div class="component-toolbar header_bg">

        <!-- Search -->
        <pagination-bar
            v-if="!section || section === 'search'"
            :offset="pagination.offset"
            :limit="pagination.limit"
            :count="pagination.count"
            :sortby="pagination.sort_by"
            :sorters="sorters"
            :layout="layout"
            :layouts="layouts"
            :loading="loading"
            v-on:reload="setItems(true)"
            v-on:offset="setOffset"
            v-on:limit="setLimit"
            v-on:sortby="setSortBy"
            v-on:layout="(emit) => { this.layout = emit }"
            v-on:add="newItem()"
        >
        </pagination-bar>

        <!-- Editor -->
        <div v-else-if="section === 'editor'" class="d-flex align-center" style="width: 100%; height: 50px">

            <slot name="editor-toolbar-left" v-bind:item="editorItem">
                <div class="ml-3 headline" v-html="editorItem.id ? (editorItemLabel + ' ' + editorItem.id) : 'Add new Item'" />
            </slot>

            <v-spacer />

            <v-hover v-slot="{ hover }">
                <div
                    class="d-flex align-center justify-center headline font-weight-bold text-uppercase"
                    :class="(saveDisabled ? 'grey--text' : 'light-blue--text text--darken-2') + (hover && !saveDisabled ? ' header_hover' : ' header_bg')"
                    style="width: 200px; height: 50px;"
                    :style="saveDisabled ? 'cursor: default:' : 'cursor: pointer'"
                    @click="save()"
                >
                    <v-icon v-text="'save'" class="mr-2" :class="saveDisabled ? 'grey--text' : 'light-blue--text text--darken-2'" />
                    <div v-text="'save'" />
                </div>
            </v-hover>
        </div>

    </div>

    <!-- Filter Drawer -->
    <v-slide-x-transition>
        <drawer
            v-if="!section || section === 'search'"
            header
            :collapse="queryCounter"
            v-on:expand="onDrawerExpand"
            v-on:search="runQuery()"
            v-on:clear="resetFilters(true)"
        >
            <!-- Filters -->
            <template v-slot:content>
                <div class="d-flex align-center" style="height: 40px; width: 200px;" :style="showFilters ? 'cursor: default' : 'cursor: pointer'">
                    <div class="d-flex align-center justify-center" style="height: 40px; width: 40px;">
                        <v-icon v-text="'filter_alt'" />
                    </div>
                    <div class="font-weight-bold body-1 ml-3" v-text="'Available Filters'" />
                </div>
                <v-expand-transition>
                    <div v-if="showFilters" class="pa-5">
                        <slot name="filters" />
                    </div>
                </v-expand-transition>
            </template>
        </drawer>
    </v-slide-x-transition>

    <!-- Content -->
    <div class="component-content">

        <div :style="section === 'search' ? 'padding-left: 40px;' : ''">

            <v-fade-transition>
                <!-- Search - Table --------------------------------------------------------------------------------- -->
                <div v-if="(!section || section === 'search') && layout === 'table'" class="pa-5">
                    <v-card
                        tile
                        class="grey_trip pt-2 pb-1 pl-5 pr-5"
                    >
                        <table class="sdt" style="width: 100%;">
                            <!-- Table Header -->
                            <tr class="body-1 font-weight-black">
                                <td v-if="select" />
                                <td
                                    v-for="(header, i) in headers"
                                    :key="i + 'h'"
                                    class="pb-2 pr-2"
                                >
                                    <div class="d-flex">
                                        <div
                                            :style="attributes[header.value].sortable ? 'cursor: pointer;' : ''"
                                            v-html="header.text + (sorted.value === header.value ? ('&nbsp;' + (sorted.desc ? '&uarr;' : '&darr;')) : '')"
                                            @click="attributes[header.value].sortable ? setSortBy(header.value + '.' + (sorted.value === header.value ? (sorted.desc ? 'ASC' : 'DESC') : 'ASC')) : ''"
                                        />
                                        <v-spacer />
                                    </div>
                                </td>
                                <td />
                            </tr>
                            <!-- Table Body -->
                            <template v-for="(item, i) in items">
                                <tr
                                    :key="i"
                                    class="caption"
                                    :class="selected == item.id ? 'marked' : ''"
                                >
                                    <td v-if="select">
                                        <v-btn
                                            fab
                                            x-small
                                            color="blue_prim"
                                            class="mr-2 ml-2"
                                            @click="selectItem(item)"
                                        >
                                            <v-icon v-text="'touch_app'" />
                                        </v-btn>
                                    </td>

                                    <td
                                        v-for="(header, i) in headers"
                                        :key="i + 'r'"
                                        class="pa-3 pl-0"
                                        style="vertical-align: top"
                                        v-html="header.content(item)"
                                    />

                                    <td>
                                        <div class="d-md-flex justify-end">
                                            <adv-btn
                                                icon="preview"
                                                tooltip="View/Edit Details"
                                                medium
                                                @click="updateEditor(true, item)"
                                            />
                                            <adv-btn
                                                icon="library_add"
                                                tooltip="Copy Record"
                                                medium
                                                @click="updateClone(true, item)"
                                            />
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </table>
                    </v-card>
                </div>

                <!-- Search - Tiles --------------------------------------------------------------------------------- -->
                <div v-else-if="section === 'search' && layout === 'tiles'" class="pa-3">
                    <v-row class="ma-0 pa-0">
                        <v-col
                            v-for="(item, i) in items"
                            :key="i"
                            cols=12
                            :sm="cols.sm"
                            :md="cols.md"
                            :lg="cols.lg"
                            :xl="cols.xl"
                        >
                            <v-card tile class="grey_trip">
                                <!-- Details Header -->
                                <v-card-title
                                    class="pb-n3"
                                    :class="item.id == selected ? 'marked' : 'grey_trip'"
                                >
                                    <slot name="content-cards-header" v-bind:item="item" />
                                    <v-spacer />
                                    <v-btn
                                        v-if="select"
                                        fab
                                        x-small
                                        color="blue_prim"
                                        @click="selectItem(item)"
                                    >
                                        <v-icon v-text="'touch_app'" />
                                    </v-btn>
                                </v-card-title>

                                <!-- Details Body -->
                                <v-card-text>
                                    <slot name="content-cards-body" v-bind:item="item" />
                                </v-card-text>

                                <v-divider />

                                <div class="d-flex">
                                    <v-spacer />
                                    <v-divider vertical />
                                    <adv-btn
                                        icon="preview"
                                        tooltip="View/Edit Details"
                                        @click="updateEditor(true, item)"
                                    />
                                    <adv-btn
                                        icon="library_add"
                                        tooltip="Copy Record"
                                        @click="updateClone(true, item)"
                                    />
                                </div>
                            </v-card>
                        </v-col>
                    </v-row>
                </div>

                <!-- Editor --------------------------------------------------------------------------------- -->
                <div v-else-if="section === 'editor'" class="pa-5">
                    <v-card tile class="pa-5 mb-10 grey_sec">
                        <div
                            class="mt-n1 d-flex justify-space-between align-center mb-5"
                            style="cursor:pointer"
                            @click="editorExpanded = !editorExpanded"
                        >
                            <div class="body-1 font-weight-bold" v-text="editorExpanded ? 'Edit Data' : 'Details'" />
                            <v-icon class="ml-2 mr-3" v-text="editorExpanded ? 'expand_less' : 'edit'" />
                            <v-divider />
                        </div>

                        <v-expand-transition>
                            <slot
                                v-if="editorExpanded || !editorItem.id"
                                name="editor-body"
                                v-bind:item="editorItem"
                            />
                            <div
                                v-else
                                class="d-flex justify-center mt-n4"
                                v-html="printEditorSumup()"
                            />
                        </v-expand-transition>
                    </v-card>

                    <!-- Gallery -->
                    <v-expand-transition>
                        <ItemGallery
                            v-if="editorItem.id"
                            :key="editorItem.id"
                            :entity="entity"
                            :search_key="gallery"
                            :search_val="editorItem.id ? editorItem.id : 0"
                            :linking="linking"
                            card-header
                        >
                            <template v-slot:link="slot">
                                <div :key="slot.link.active">
                                    <slot name="gallery-link" v-bind:link="slot.link" />
                                </div>
                            </template>
                        </ItemGallery>
                    </v-expand-transition>
                </div>

            </v-fade-transition>
        </div>
    </div>

</div>
</template>


<script>

export default {
    data () {
        return {
            loading:        false,

            section:        'search',
            showFilters:    false,
            layout:         this.defaultDisplay,

            fullFetch:      !this.sync,
            queryCounter:   0,
            itemsRaw:       [],
            items:          [],
            pagination: {
                offset:     0,
                limit:      12,
                count:      0,
                sort_by:    this.defaultSortBy
            },

            editorItem:     {},
            editorItemLabel: this.itemLabel ?? ('cn ' + (this.entity.slice(-1) === 's' ? this.entity.slice(0, -1) : this.entity)),
            saveDisabled:   false,
            editorExpanded: false,
        }
    },

    props: {
        dialog:         { type: Boolean, default: false },
        entity:         { type: String, required: true },
        name:           { type: String, required: true },
        attributes:     { type: Object, required: true },
        defaultSortBy:  { type: String, default: 'id.DESC' },
        headline:       { type: String },
        defaultDisplay: { type: String, default: 'table' },
        table:          { type: Boolean, default: true },
        tiles:          { type: Boolean, default: true },
        smallTiles:     { type: Boolean, default: false },
        gallery:        { type: String, default: null },
        linking:        { type: Boolean, default: false },
        sync:           { type: Boolean, default: false },

        itemLabel:      { type: String, default: null },

        dialog:         { type: Boolean, default: false },
        select:         { type: Boolean, default: false },
        selected:       { type: [Number, String], default: null }
    },

    computed: {
        language () {
            return this.$root.language
        },

        layouts () {
            const displays = {}
            if (this.table) displays.table = { text: this.$root.label('table'), icon: 'view_headline' }
            if (this.tiles) displays.tiles = { text: this.$root.label('tiles'), icon: 'view_comfy' }
            return displays
        },

        cols () {
            if (!this.smallTiles)   return { sm: 6, md: 4, lg: 3, xl: 2 }
            else                    return { sm: 4, md: 3, lg: 2, xl: 2 }
        },

        /*defaultItem () {
            const defaultItem = {}
            Object.keys(this.attributes).forEach((key) => {
                defaultItem[key] = this.attributes[key].default
            })
            return defaultItem
        },*/

        sorted () {
            const split = this.pagination.sort_by.trim().split(/[s.]+/)
            return {
                value: split[0],
                desc: split[1] && split[1]?.toLowerCase() === 'desc'
            }
        },

        headers () {
            const headers = []
            Object.keys(this.attributes).forEach((key) => {
                if (this.attributes[key].header) headers.push({
                    value: key,
                    text: this.attributes[key].text,
                    content: this.attributes[key].content
                })
            })
            return headers
        },

        sorters () {
            const sorters = []
            Object.keys(this.attributes).forEach((key) => {
                if (this.attributes[key].sortable) sorters.push({
                    value: key,
                    text: this.attributes[key].text
                })
            })
            return sorters
        },

        filters () {
            const filters = {}
            Object.keys(this.attributes).forEach((key) => {
                if (this.attributes[key].filter !== undefined && this.attributes[key].filter !== null) filters[key] = this.attributes[key].filter
            })
            return filters
        },

        query: {
            get: function () {
                const query = {}

                // Filter
                Object.keys(this.attributes).forEach((key) => {
                    const value = this.attributes[key].filter
                    if (value !== undefined && value !== null) query[key] = value
                })

                // Pagination
                query.offset = this.pagination.offset
                query.limit = this.pagination.limit
                query.sort_by = this.pagination.sort_by

                return query
            },

            set: function (query) {
                // Filter
                Object.keys(this.attributes).forEach((key) => {
                    const filter = this.attributes[key].filter
                    const value = query[key] ?? null
                    if (filter !== undefined) this.$emit('setFilter', { key, value })
                })

                // Pagination
                this.pagination.offset = query.offset ? parseInt(query.offset) : 0
                this.pagination.limit = query.limit ? parseInt(query.limit) : 12

                // Sorting
                if (query.sort_by) {
                    const split = query.sort_by.split(/[.\s]+/)
                    this.pagination.sort_by = split[0] + '.' + (split[1]?.toLowerCase() === 'desc' ? 'DESC' : 'ASC')
                }
                else this.pagination.sort_by = this.defaultSortBy
            }
        },

        id: {
            get: function () {
                if (this.dialog) return this.selected
                else return this.$route.params.id ?? null
            },
            set: function (value) {
                if (this.dialog) this.$emit('select', value)
                else if (this.$route.path !== '/' + this.entity + '/new') this.$router.push('/' + this.entity + '/new')
            }
        }
    },

    watch: {
        $route (to, from) { this.handleQuery() },
        id () { this.handleId() }
    },

    async created () {
        if (this.id) {
            if (this.fullFetch) await this.setItems(true)
            this.handleId()
        }
        else this.handleQuery(true)
    },

    methods: {

        // Handle Search -----------------------------------------------------------------------------
        handleQuery (forcedReload) {
            if (!this.dialog) {
                if (this.$route.query) {
                    this.query = this.$route.query
                    this.$root.setTitle(this.$root.label(this.entity) + ' (' + this.$handlers.format.query(this.query) + ')')
                    this.setItems(forcedReload)
                }
            }
        },

        handleId () {
            if (this.id) {
                this.$root.setTitle(this.$root.label(this.entity) + ' (' + (this.id === 'new' ? 'new Item' : ('ID ' + this.id)) + ')')
                this.section = 'editor'
                this.setEditorItem(this.id)
            }
            else this.section = 'search'
        },

        runQuery () {
            if (this.dialog) console.log('dialog')
            else {
                this.queryIncrement()
                this.pagination.offset = 0
                const query = this.constructQuery()
                console.log(query)
                this.$router.push({ path: '/' + this.entity, query: this.constructQuery() })
            }
        },

        constructQuery () {
            const params = {}

            // FIlters
            Object.keys(this.attributes).forEach((key) => {
                const value = this.attributes[key].filter
                if (value !== undefined && value !== null && value !== []) params[key] = value
            })

            // Pagination
            params.offset = this.pagination.offset
            params.limit = this.pagination.limit
            params.sort_by = this.pagination.sort_by
            params.i = this.queryCounter

            return params
        },

        queryIncrement () {
            if (this.dialog) ++this.queryCounter
            else {
                const i = parseInt(this.$route.query?.i) ?? 0
                this.queryCounter = this.queryCounter + 1 + (this.queryCounter + 1 === i ? 1 : 0)
            }
        },

        async getItems () {
            this.loading = this.$root.loading = true
            let dbi = {}

            if (this.fullFetch) dbi = await this.$root.DBI_SELECT_GET(this.entity, null)
            else dbi = await this.$root.DBI_SELECT_POST(this.entity, this.query)

            if (dbi?.pagination?.limit !== undefined) {
                this.pagination.limit = dbi.pagination.limit
                this.pagination.offet = dbi.pagination.offset
                this.pagination.sort_by = dbi.pagination.sort_by?.replace(' ', '.')
                this.fullFetch = false
            }

            if (dbi?.pagination?.count !== undefined) this.pagination.count = dbi.pagination.count
            else this.pagination.count = dbi?.contents?.[0]?.id ? dbi.contents.length : 0

            this.itemsRaw = dbi?.contents?.[0]?.id ? dbi.contents : []

            this.loading = this.$root.loading = false
        },

        async setItems (forcedReload) {
            if (forcedReload) await this.getItems()

            if (this.fullFetch) {
                this.loading = this.$root.loading = true

                setTimeout(() => {

                    // Filter Items
                    const filtered = this.itemsRaw.filter((item) => this.filterItems(item))
                    console.log(filtered)

                    // Sort Items
                    const sorted = filtered.sort((a, b) => this.sortItems(a, b))
                    console.log(sorted)

                    // Set selection based on Offset/Limit
                    const selection = []

                    console.log(this.pagination)

                    for (let i = this.pagination.offset; i < (this.pagination.offset + this.pagination.limit); i = ++i) {
                        if (sorted[i] === undefined) break
                        selection.push(sorted[i])
                    }

                    this.items = selection
                    this.loading = this.$root.loading = false

                }, 150)
            }
            else if (!forcedReload) this.items = this.getItems()
        },

        // Filtering
        filterItems (item) {
            let match = true

            Object.keys(this.filters).forEach((key) => {
                if (typeof this.filters[key] === 'number') {
                    if (this.checkMatching(this.filters[key], item[key])) match = false
                }
                else {
                    this.filters[key].split(' ').forEach((filter) => {
                        if (this.checkMatching(filter, item[key])) match = false
                    })
                }
            })

            if (match) return item
        },

        checkMatching (filter, value) {
            if (filter) {
                if (typeof filter === 'number' || typeof value === 'number') {
                    return filter == value ? false : true
                }
                else {
                    return value?.toLowerCase().includes(filter.toLowerCase()) ? false : true
                }
            }
            else return false
        },

        sortItems (a, b) {
            const key = this.sorted.value
            const asc = !this.sorted.desc

            a = a[key]
            b = b[key]

            if (typeof a === 'string' || typeof b === 'string') {
                a = (a ? (typeof a !== 'string' ? a.toString() : a) : '').toLowerCase().replace('ä', 'ae').replace('ö', 'oe').replace('ü', 'ue')
                b = (b ? (typeof b !== 'string' ? a.toString() : b) : '').toLowerCase().replace('ä', 'ae').replace('ö', 'oe').replace('ü', 'ue')
            }

            if (a < b) return asc ? -1 : 1
            if (a > b) return asc ? 1 : -1

            return 0
        },

        resetFilters (message) {
            Object.keys(this.attributes).forEach((key) => {
                if (this.attributes[key].filter !== undefined) this.$emit('setFilter', { key, value: null })
            })
            if (message) this.$store.dispatch('showSnack', { color: 'blue_sec', message: 'Query Parameters set to default!' })
        },

        // Pagination
        setOffset (value) {
            this.pagination.offset = value
            this.setItems()
        },

        setLimit (value) {
            this.pagination.limit = value
            this.setItems()
        },

        setSortBy (value) {
            this.pagination.sort_by = value
            this.pagination.offset = 0

            this.setItems()
        },

        // Functional ---------------
        onDrawerExpand (expand) {
            if (expand) setTimeout(() => { this.showFilters = true }, 350)
            else this.showFilters = false
        },

        close () {
            if (this.clone.active) this.updateClone(false, null)
            else this.updateEditor(false, null)

            if (!this.select && this.$route.params.id) this.$router.push('/' + this.entity)
        },

        // Input -----------------------------------------------------------------------------
        newItem () {
            this.section = 'editor'
            this.setItemToDefault
            this.id = 'new'
        },

        setItemToDefault () {
            const defaultItem = {}
            Object.keys(this.attributes).forEach((key) => {
                defaultItem[key] = typeof this.attributes[key].default !== 'object' ? { ... this.attributes[key].default } : this.attributes[key].default
            })
            this.editorItem = defaultItem
        },

        async setEditorItem (id) {
            this.setItemToDefault

            if (id && id !== 'new') {
                this.editorItem = await this.getSingleItem(this.id)
                console.log(this.editorItem)
            }
        },

        async getSingleItem (id) {
            this.loading = this.$root.loading = true

            const cached = this.itemsRaw.find((item) => item.id == id)
            let item = {}

            if (cached) {
                console.log('Requested ID ' + id + ' in Cache!')
                item = cached
            }
            else {
                let data = {}

                if (this.fullFetch) {
                    await this.getItems()
                    data = this.itemsRaw.find((item) => item.id == id)
                }
                else {
                    const dbi = await this.$root.DBI_SELECT_GET(this.entity, id)
                    if (dbi?.contents?.[0]?.id) data = dbi.contents[0]
                }

                if (data) item = data
                else {
                    alert('Unknown ID ' + id)
                    if (this.dialog) this.section = 'search'
                    else this.$router.push('/' + this.entity)
                }
            }

            this.loading = this.$root.loading = false
            return item
        },

        async sendItem () {
            this.$root.loading = this.$root.loading = true

                const response = await this.$root.DBI_INPUT_POST(this.entity, 'input', this.editor.item);

                if (response.success) {
                    this.$root.snackbar(response.success, 'success');
                    if (!this.editor.item.id) {
                        this.editor.item.id = response.id
                        this.pagination.sort_by = 'id.DESC'
                    }
                    this.getItems()
                }
                else {
                    console.log('Data Input Error: response was not ok');
                }

            this.$root.loading = this.$root.loading = false
        },

        async deleteItem () {
            const confirmed = confirm('Soll Eintrag ID ' + this.editor.item.id + ' wirklich gelöscht werden?')

            if (confirmed) {
                this.$root.loading = this.$root.loading = true

                    const response = await this.$root.DBI_INPUT_POST(this.entity, 'delete', this.editor.item);

                    if (response.success) {
                        this.$root.snackbar (response.success, 'success');
                        this.updateEditor(false, null)
                        this.getItems()
                    }
                    else {
                        console.log('Data Input Error: response was not ok');
                    }

                this.$root.loading = this.$root.loading = false
            }
        },

        printEditorSumup () {
            const print = []
            this.headers.forEach((header) => {
                if (this.editorItem[header.value] !== undefined) print.push(
                    '<td class="pr-5" style="vertical-align: top">' +
                        '<div class="caption font-weight-thin text-uppercase">' + header.text + '</div>' +
                        '<div>' + this.attributes[header.value].content(this.editorItem) + '</div>' +
                    '</td>'
                )
            })
            return '<table><tr>' + print.join('\n') + '</tr></table>'
        },

        updateClone (active, input) {
            const self = this
            const item = {}

            if (active) {
                Object.keys(this.defaultItem).forEach((key) => {
                    item[key] = {
                        text: self.attributes[key].text,
                        content: self.attributes[key].content(input),
                        value: self.attributes[key].clone === undefined || !input[key] ? null : input[key]
                    }
                    if (self.attributes[key].clone !== undefined) {
                        if (['', null].includes(input[key])) {
                            item[key].clone = null
                        }
                        else {
                            item[key].clone = self.attributes[key].clone
                        }
                    }
                })
            }
            else {
                Object.keys(this.defaultItem).forEach((key) => {
                    item[key] = { value: self.defaultItem[key] }
                })
            }

            this.clone = {
                active: active ? true : false,
                item: item
            }
        },

        pasteClone () {
            const self = this
            const item = {}

            Object.keys(this.defaultItem).forEach((key) => {
                item[key] = self.clone.item[key].clone === true ? self.clone.item[key].value : self.defaultItem[key]
            })

            this.updateClone(false, null)
            this.updateEditor(true, item)
        },

        selectItem (item) {
            if (item) {
                if (item.id === this.selected) alert(this.name + ' ID ' + item.id + ' is already selected!')

                else {
                    const selected = {}
                    Object.keys(item).forEach((key) => {
                        selected[key] = item[key]
                    })
                    if (selected.entity === undefined) {
                        selected.entity = this.entity
                    }
                    this.$emit('select', selected)
                    this.$root.snackbar(this.name + ' ID ' + item.id + ' selected!', 'success')
                }
            }
            else alert('ERROR: item is empty!')
        },
    }
}

</script>

<style scoped>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    table tr {
        border-bottom: 1pt solid grey;
    }

    table tr:last-child {
        border: 0;
    }
</style>
