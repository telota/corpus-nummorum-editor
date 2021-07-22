<template>
<div>
    <!-- Toolbar -->
    <div class="component-toolbar">
        <pagination-bar
            :offset="pagination.offset"
            :limit="pagination.limit"
            :count="pagination.count"
            :sortby="pagination.sort_by.replace('.', ' ')"
            :sorters="sorters"
            :layout="layout"
            :layouts="layouts"
            v-on:reload="getItems()"
            v-on:offset="setOffset"
            v-on:limit="setLimit"
            v-on:sortby="setSortBy"
            v-on:layout="(emit) => { this.layout = emit }"
        >
        </pagination-bar>
    </div>

    <!-- Content -->
    <div class="component-content">
        test
    </div>

</div>
</template>


<script>

export default {
    data () {
        return {
            loading:        false,
            items_raw:      [],
            items_refresh:  0,
            layout:         this.defaultDisplay === 'table' ? 0 : 1,
            fullFetch:      !this.sync,

            editor: {
                active: false,
                item: {},
                expand: true
            },

            clone: {
                active: false,
                item: {}
            },

            link: {
                active: false,
                entity: null,
                item: {}
            },

            pagination: {
                offset: 0,
                limit: 12,
                count: 0,
                sort_by: this.defaultSortBy
            },

            /*sort: {
                by: this.defaultSortBy?.split('.')?.[0],
                asc: this.defaultSortBy?.split('.')?.[1]?.toLowerCase() === 'desc' ? false : true
            }*/
        }
    },

    props: {
        dialog:         { type: Boolean, default: false },
        entity:         { type: String, required: true },
        name:           { type: String, required: true },
        attributes:     { type: Object, required: true },
        defaultSortBy:  { type: String, default: 'id' },
        headline:       { type: String },
        defaultDisplay: { type: String, default: 'table' },
        table:          { type: Boolean, default: true },
        cards:          { type: Boolean, default: false },
        smallCards:     { type: Boolean, default: false },
        gallery:        { type: String, default: null },
        linking:        { type: Boolean, default: false },
        select:         { type: Boolean, default: false },
        selected:       { type: [Number, String], default: 0 },
        sync:           { type: Boolean, default: false }
    },

    computed: {
        l () {
            return this.$root.language
        },
        labels () {
            return this.$root.labels
        },

        layouts () {
            const displays = []
            if (this.table) displays.push({ value: 'table', text: this.$root.label('table'), icon: 'view_headline' })
            if (this.cards) displays.push({ value: 'cards', text: this.$root.label('tiles'), icon: 'view_comfy' })
            return displays
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

        items () {
            if (this.fullFetch) {

                // Filter items
                const filtered = this.items_raw.filter((item) => this.filterItems(item))

                // Sort Items
                const sorted = filtered.sort((a, b) => this.sortItems(a, b))

                // Set Offset/Limit
                const selection = []
                for (let i = this.pagination.offset; i < (this.pagination.offset + this.pagination.limit); i = ++i) {
                    if (sorted[i] === undefined) break
                    selection.push(sorted[i])
                }

                return selection
            }
            else {
                return this.items_raw
            }
        },

        /*id () {
            if (this.$route.params.id) {
                return this.$route.params.id
            }
            else if (this.selected) {
                return this.selected == 0 ? null : this.selected
            }
            else {
                return null
            }
        },*/

        cols () {
            if (!this.smallCards)   return { sm: 6, md: 4, lg: 3, xl: 2 }
            else                    return { sm: 4, md: 3, lg: 2, xl: 2 }
        },

        defaultItem () {
            const defaultItem = {}
            Object.keys(this.attributes).forEach((key) => {
                defaultItem[key] = this.attributes[key].default
            })
            return defaultItem
        },

        filterStringified () {
            if (this.fullFetch) return null

            else {
                const strings = []
                Object.keys(this.attributes).forEach((key) => {
                    if (this.attributes[key].filter !== undefined) {
                        strings.push(this.attributes[key].filter === null ? '_' : this.attributes[key].filter)
                    }
                })
                return strings.join('|')
            }
        }
    },

    watch: {
        $route (to, from) {
            if (this.$route.params.id) { this.getSingleItem(this.$route.params.id) }
        },

        filterStringified () {
            this.pagination.offset = 0
            this.getItems()
        }
    },

    async created () {
        this.$root.setTitle(this.entity)
        await this.getItems()

        if (this.select) {
            if (this.selected) {
                this.getSingleItem(this.selected)
                this.editor.expand = false
            }
        }
        else if (this.$route.params.id) {
            this.getSingleItem(this.$route.params.id)
            this.editor.expand = false
        }
    },

    methods: {
        updateEditor (active, input) {
            const item = {}

            Object.keys(this.defaultItem).forEach((key) => {
                item[key] = input?.[key] ? input[key] : this.defaultItem[key]
            })

            this.editor = {
                active: active ? true : false,
                item: item,
                expand: true
            }
        },

        printEditorSumup () {
            const print = []
            this.headers.forEach((header) => {
                if (this.editor.item[header.value] !== undefined) print.push(
                    '<td class="pr-5" style="vertical-align: top">' +
                    '<div class="caption font-weight-thin text-uppercase">' + header.text + '</div>' +
                    '<div>' + this.attributes[header.value].content(this.editor.item) + '</div>' +
                    '</td>'
                )
            })
            return '<table><tr>' + print.join('') + '</tr></table>'
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

        setOffset (offset) {
            this.pagination.offset = offset
            if (!this.fullFetch) this.getItems()
        },

        setLimit (limit) {
            this.pagination.limit = limit
            if (!this.fullFetch) this.getItems()
        },

        setSortBy (input) {
            if (input === this.pagination.sort_by) {
                this.pagination.sort_by_op = this.pagination.sort_by_op != 'ASC' ? 'ASC' : 'DESC'
            }
            else {
                this.pagination.sort_by = input
                this.pagination.sort_by_op = 'ASC'
            }
            this.pagination.offset = 0


            this.pagination.offset = 0
            console.log(sorter)

            if (sorter.value === this.sort.by) this.sort.asc = !this.sort.asc
            else this.sort = { by: sorter.value, asc: true }

            if (!this.fullFetch) this.getItems()
        },

        close () {
            if (this.clone.active) this.updateClone(false, null)
            else this.updateEditor(false, null)

            if (!this.select && this.$route.params.id) this.$router.push('/' + this.entity)
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

        // Handle DBI Output -----------------------------------------------------------------------------
        async getItems () {
            this.loading = this.$root.loading = true
            let dbi = {}

            if (this.fullFetch) {
                dbi = await this.$root.DBI_SELECT_GET(this.entity, null)
            }
            else {
                const params = {}
                Object.keys(this.pagination).forEach((key) => {
                    params[key] = key != 'sort_by' ? this.pagination[key] : (this.sort.by + '.' + (this.sort.asc ? 'ASC' : 'DESC'))
                })
                const search = {}
                Object.keys(this.attributes).forEach((key) => {
                    if (![null, '', undefined].includes(this.attributes[key].filter)) {
                        if (typeof this.attributes[key].filter === 'number') {
                            search[key] = this.attributes[key].filter
                        }
                        else {
                            search[key] = []
                            this.attributes[key].filter.split(' ').forEach((string) => {
                                search[key].push(string)
                            })
                        }
                    }
                })
                dbi = await this.$root.DBI_SELECT_POST(this.entity, params, search)
            }

            if (dbi?.pagination?.limit !== undefined) {
                this.pagination = dbi.pagination
                this.fullFetch = false
            }
            else if (dbi?.pagination?.count !== undefined) {
                this.pagination.count = dbi.pagination.count
            }
            else {
                this.pagination.count = dbi?.contents?.[0]?.id ? dbi.contents.length : 0
            }

            this.items_raw = dbi?.contents?.[0]?.id ? dbi.contents : []

            this.loading = this.$root.loading = false
        },

        async getSingleItem (id) {
            this.loading = this.$root.loading = true

            const cached = this.items_raw.find((item) => item.id == id)

            if (cached) {
                console.log('Requested ID ' + id + ' in Cache!')
                this.updateEditor(true, cached)
            }
            else {
                if (this.fullFetch) {
                    this.getItems()
                    const data = this.items_raw.find((item) => item.id == id)
                    if (data) {
                        this.updateEditor(true, cached)
                    }
                    else {
                        alert('Unknown ' + this.name + ' ' + id)
                        this.$router.push('/' + this.entity)
                    }
                }
                else {
                    const dbi = await this.$root.DBI_SELECT_GET(this.entity, id)
                    if (dbi?.contents?.[0]?.id) {
                        this.updateEditor(true, dbi.contents[0])
                    }
                    else {
                        alert('Unknown ' + this.name + ' ' + id)
                        this.$router.push('/' + this.entity)
                    }
                }
            }

            this.loading = this.$root.loading = false
        },

        filterItems (item) {
            const self = this
            let match = true

            Object.keys(self.filters).forEach((key) => {
                if (typeof self.filters[key] === 'number') {
                    if (self.checkMatching(self.filters[key], item[key])) match = false
                }
                else {
                    self.filters[key].split(' ').forEach((filter) => {
                        if (self.checkMatching(filter, item[key])) match = false
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
            const key = this.sort.by
            const asc = this.sort.asc
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

        // Provide DBI input -----------------------------------------------------------------------------
        async sendItem () {
            this.$root.loading = this.$root.loading = true

                const response = await this.$root.DBI_INPUT_POST(this.entity, 'input', this.editor.item);

                if (response.success) {
                    this.$root.snackbar(response.success, 'success');
                    if (!this.editor.item.id) {
                        this.editor.item.id = response.id
                        this.sort = { by: 'id', asc: false }
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
