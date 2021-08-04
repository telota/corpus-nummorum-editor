<template>
<div>
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
        </pagination-bar>

    </div>

    <!-- Filter Drawer -->
    <drawer
        header
        :dialog="dialog"
        :collapse="queryCounter"
        v-on:expand="onDrawerExpand"
        v-on:search="runQuery()"
        v-on:clear="resetFilters(true)"
    >
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

    <!-- Content -->
    <div :class="'component-content' + (dialog ? ' component-content-dialog' : '')" style="padding-left: 40px;">
        <v-fade-transition>

            <!-- Search-Table -->
            <div v-if="layout === 'table'" class="pa-5">
                <v-card
                    tile
                    class="tile_bg pt-2 pb-1"
                    style="overflow-x: auto"
                >
                    <table class="sdt" style="width: 100%;">
                        <!-- Table Header -->
                        <tr class="body-1 font-weight-black">
                            <td
                                v-for="(header, i) in headers"
                                :key="i + 'h'"
                                class="pb-2 pl-3 pr-1"
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
                                :class="item.id == selected ? 'tile_selected' : ''"
                            >
                                <td
                                    v-for="(header, i) in headers"
                                    :key="i + 'r'"
                                    class="pa-3 pr-1"
                                    style="vertical-align: top"
                                    v-html="header.content(item)"
                                />

                                <td style="width: 140px">
                                    <div class="d-flex justify-end">
                                        <adv-btn
                                            v-if="select"
                                            icon="touch_app"
                                            color-main="blue_prim"
                                            color-hover="blue_sec"
                                            color-text="white"
                                            medium
                                            :disabled="item.id == selected"
                                            v-on:click="selectItem(item)"
                                        />
                                        <adv-btn
                                            icon="preview"
                                            dis-tooltip="View/Edit Details"
                                            medium
                                            @click="editItem(item)"
                                        />
                                        <adv-btn
                                            icon="library_add"
                                            dis-tooltip="Copy Record"
                                            medium
                                            @click="cloneItem(item)"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </table>
                </v-card>
            </div>

            <!-- Search-Tiles -->
            <div v-else-if="layout === 'tiles'" class="pa-3">
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
                        <v-card tile :class="item.id == selected ? 'tile_selected' : 'tile_bg'">
                            <!-- Details Header -->
                            <v-card-title class="pb-n3 text-truncate">
                                <slot name="search-tile-header" v-bind:item="item">
                                    cn {{ entity.slice(-1) === 's' ? entity.slice(0, -1) : entity }} {{ item.id }}
                                </slot>
                            </v-card-title>

                            <!-- Details Body -->
                            <v-card-text>
                                <slot name="search-tile-body" v-bind:item="item" />
                            </v-card-text>

                            <v-divider />

                            <div class="d-flex">
                                <adv-btn
                                    v-if="select"
                                    icon="touch_app"
                                    color-main="blue_prim"
                                    color-hover="blue_sec"
                                    color-text="white"
                                    medium
                                    :disabled="item.id == selected"
                                    v-on:click="selectItem(item)"
                                />
                                <v-spacer />
                                <v-divider vertical />
                                <adv-btn
                                    icon="preview"
                                    dis-tooltip="View/Edit Details"
                                    medium
                                    @click="editItem(item)"
                                />
                                <adv-btn
                                    icon="library_add"
                                    dis-tooltip="Copy Record"
                                    medium
                                    @click="cloneItem(item)"
                                />
                            </div>
                        </v-card>
                    </v-col>
                </v-row>
            </div>

        </v-fade-transition>
    </div>

</div>
</template>


<script>

export default {
    data () {
        return {
            loading:        false,

            showFilters:    false,
            layout:         this.defaultLayout,

            fullFetch:      !this.sync,
            queryCounter:   0,
            itemsRaw:       [],
            items:          [],
            pagination: {
                offset:     0,
                limit:      12,
                count:      0,
                sort_by:    this.defaultSortBy
            }
        }
    },

    props: {
        dialog:         { type: Boolean, default: false },

        entity:         { type: String, required: true },

        attributes:     { type: Object, required: true },
        defaultSortBy:  { type: String, default: 'id.DESC' },
        defaultLayout:  { type: String, default: 'table' },
        table:          { type: Boolean, default: true },
        tiles:          { type: Boolean, default: true },
        smallTiles:     { type: Boolean, default: false },
        sync:           { type: Boolean, default: false },

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
        }
    },

    watch: {
        $route (to, from) { this.handleQuery() }
    },

    async created () {
        this.handleQuery(true)
    },

    methods: {

        // Handle Search -----------------------------------------------------------------------------
        handleQuery (forcedReload) {
            if (!this.dialog) {
                if (this.$route.query) this.query = this.$route.query
                this.$root.setTitle(this.$root.label(this.entity) + ' (' + this.$handlers.format.query(this.query) + ')')
            }
            this.setItems(forcedReload)
        },

        runQuery (offset = 0) {
            this.pagination.offset = offset
            this.queryIncrement()
            const query = this.constructQuery()
            //console.log(query)

            if (this.dialog) {
                this.query = query
                this.setItems()
            }
            else this.$router.push({ path: '/' + this.entity, query })
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

            const dbi = await this.$root.DBI_SELECT_POST(this.entity, this.query)

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
            if (!this.fullFetch || forcedReload) await this.getItems()

            if (this.fullFetch) {
                this.loading = this.$root.loading = true

                setTimeout(() => {

                    // Filter Items
                    const filtered = this.itemsRaw.filter((item) => this.filterItems(item))

                    this.pagination.count = filtered.length

                    // Sort Items
                    const sorted = filtered.sort((a, b) => this.sortItems(a, b))

                    // Set selection based on Offset/Limit
                    const selection = []

                    for (let i = this.pagination.offset; i < (this.pagination.offset + this.pagination.limit); i = ++i) {
                        if (sorted[i] === undefined) break
                        selection.push(sorted[i])
                    }

                    this.items = selection
                    this.loading = this.$root.loading = false

                }, 150)
            }
            else this.items = this.itemsRaw
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

        // Functional ---------------
        onDrawerExpand (expand) {
            if (expand) setTimeout(() => { this.showFilters = true }, 350)
            else this.showFilters = false
        },

        selectItem (item) {
            this.$emit('select', item)
        },

        editItem (item) {
            this.$emit('edit', item)
        },

        cloneItem (item) {
            this.$emit('clone', item)
        }
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
