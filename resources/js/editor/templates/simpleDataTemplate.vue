<template>
<div>
    <v-expand-transition>
        <!-- List -->
        <div v-if="!editor.active && !clone.active">
            <!-- Header --------------------------------------------------------------------------------- -->
            <v-card tile class="grey_sec mb-2">
                <!-- Title Section -->
                <v-app-bar color="grey_prim">
                    <div class="d-flex align-end">
                        <div 
                            class="headline" 
                            v-html="headline ? headline : entity"
                        ></div>
                        <v-hover v-if="select">
                            <template v-slot:default="{ hover }" >                                
                                <div
                                    v-if="select"
                                    class="body-1 ml-3"
                                    :class="selected ? (hover ? 'blue_sec--text' : 'blue_prim--text') : ''"
                                    :style="selected ? 'cursor: pointer;' : ''"
                                    v-html="selected ? ('ID ' + selected + ' selected') : 'No ID selected, yet.'"
                                    @click="() => { if (selected) { getSingleItem(selected) }}"
                                ></div>
                            </template>
                        </v-hover>
                    </div>

                    <v-spacer></v-spacer>

                    <v-btn 
                        tile
                        depressed
                        class="blue_prim"
                        :disabled="$root.user.level < 11"
                        v-text="'Neuer Eintrag'"
                        @click="updateEditor(true, null)"
                    ></v-btn>
                </v-app-bar>

                <!-- Search Section -->
                <v-expand-transition>
                    <v-card-text v-if="$store.state.std_filters" class="mb-n5">                
                        <slot name="search"></slot>
                    </v-card-text>
                </v-expand-transition>

                <!-- Pagination Section -->
                <v-card 
                    tile
                    flat
                    :loading="loading"
                    class="grey_prim"
                >
                    <v-divider></v-divider>

                    <div class="d-md-flex">
                        <!-- Pagination -->
                        <div class="d-flex justify-center" :style="'width: 100%;' + ($vuetify.breakpoint.mdAndUp ? ' position: absolute' : '')">
                            <pagination 
                                :offset="params.offset" 
                                :limit="params.limit" 
                                :count="params.count" 
                                v-on:offset="(emit) => { setOffset(emit) }"
                            ></pagination>
                        </div>

                        <!-- Controls -->
                        <div class="d-flex component-wrap" style="width: 100%">
                            <!-- Sort by -->
                            <v-menu offset-y transition="scale-transition">
                                <template v-slot:activator="{ on }">
                                    <v-btn 
                                        v-on="on" 
                                        tile 
                                        depressed
                                        color="grey_prim"
                                    >
                                        <v-icon v-text="'sort_by_alpha'"></v-icon>
                                    </v-btn>
                                </template>
                                <v-list>
                                    <v-list-item 
                                        v-for="(sorter, i) in sorters" 
                                        :key="i"
                                        @click="setSortBy(sorter)"
                                    >
                                        <v-list-item-title class="d-flex component-wrap" :class="sorter.value === sort.by ? 'font-weight-black' : ''">
                                            <div class="mr-2" v-text="sorter.text"></div>
                                            <v-icon small v-text="sorter.value != sort.by ? 'arrow_downward' : (sort.asc ? 'arrow_upward' : 'arrow_downward')"></v-icon>
                                        </v-list-item-title>
                                    </v-list-item>
                                </v-list>
                            </v-menu>

                            <!-- Limit -->
                            <v-menu offset-y transition="scale-transition">
                                <template v-slot:activator="{ on }">
                                    <v-btn 
                                        v-on="on" 
                                        tile 
                                        depressed
                                        color="grey_prim"
                                        v-text="params.limit"
                                    ></v-btn>
                                </template>
                                <v-list>
                                    <v-list-item 
                                        v-for="(i) in $store.state.ItemsPerPage" 
                                        :key="i"
                                        @click="setLimit(i)"
                                    >
                                        <v-list-item-title
                                            :class="params.limit === i ? 'font-weight-black' : ''"
                                            v-text="i"
                                        ></v-list-item-title>
                                    </v-list-item>
                                </v-list>
                            </v-menu>

                            <!-- Diyplaymode -->
                            <v-menu v-if="displays[0]" offset-y transition="scale-transition">
                                <template v-slot:activator="{ on }">
                                    <v-btn 
                                        v-on="on" 
                                        :tile="displays.length > 1"
                                        :text="displays.length < 2"
                                        depressed
                                        color="grey_prim"
                                        :disabled="displays.length < 2"
                                    >
                                        <v-icon v-text="displays.filter((f) => f.value === display)[0].icon"></v-icon>
                                    </v-btn>
                                </template>
                                <v-list>
                                    <v-list-item 
                                        v-for="(i) in displays" 
                                        :key="i.value"
                                        @click="display = i.value"
                                    >
                                        <v-list-item-title
                                            :class="params.limit === i ? 'font-weight-black' : ''"
                                        >
                                            <v-icon class="mr-3" v-text="i.icon"></v-icon><span v-text="i.text"></span>
                                        </v-list-item-title>
                                    </v-list-item>
                                </v-list>
                            </v-menu>

                            <v-spacer></v-spacer>

                            <!-- Toogle Filters -->
                            <v-btn 
                                tile
                                depressed
                                color="grey_prim"
                                @click="$store.commit('set_std_filters')"
                            >
                                <v-icon v-text="'arrow_drop_' + ($store.state.std_filters ? 'up' : 'down')"></v-icon>
                                <v-icon v-text="'search'"></v-icon>
                            </v-btn>
                        </div>
                    </div>
                </v-card>
            </v-card>

            <!-- Content / Details --------------------------------------------------------------------------------- -->

            <!-- Table -->
            <v-card 
                v-if="display === 'table'" 
                tile 
                class="grey_trip mt-5 pt-2 pb-1 pl-5 pr-5"
            >
                <table class="sdt" style="width: 100%;">
                    <!-- Table Header -->
                    <tr class="body-1 font-weight-black">
                        <td v-if="select"></td>
                        <td
                            v-for="(header, i) in headers"
                            :key="i + 'h'"
                            class="pb-2 pr-2"
                        >
                            <div class="d-flex">
                                <div 
                                    :style="attributes[header.value].sortable ? 'cursor: pointer;' : ''"
                                    v-html="header.text + (sort.by === header.value ? ('&nbsp;' + (sort.asc ? '&darr;' : '&uarr;')) : '')"
                                    @click="attributes[header.value].sortable ? setSortBy(header) : ''"
                                ></div>
                                <v-spacer></v-spacer>
                            </div>
                        </td>
                        <td></td>
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
                                    <v-icon v-text="'touch_app'"></v-icon>
                                </v-btn>
                            </td>
                            <td
                                v-for="(header, i) in headers"
                                :key="i + 'r'"
                                class="pa-3 pl-0"
                                style="vertical-align: top"
                                v-html="header.content(item)"
                            ></td>
                            <td>
                                <div class="d-md-flex justify-end">
                                    <advbtn
                                        icon="preview"
                                        color_main="grey_trip"
                                        tooltip="View/Edit Details"
                                        v-on:click="updateEditor(true, item)"
                                    ></advbtn>
                                    <advbtn
                                        icon="library_add"
                                        color_main="grey_trip"
                                        tooltip="Copy Record"
                                        v-on:click="updateClone(true, item)"
                                    ></advbtn>
                                </div>
                            </td>
                        </tr>
                    </template>
                </table>
            </v-card>

            <!-- Cards -->
            <v-row v-if="display === 'cards'">
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
                            <slot 
                                name="content-cards-header"
                                v-bind:item="item"
                            ></slot>
                            <v-spacer></v-spacer>
                            <v-btn
                                v-if="select"
                                fab
                                x-small
                                color="blue_prim"
                                @click="selectItem(item)"
                            >
                                <v-icon v-text="'touch_app'"></v-icon>
                            </v-btn>
                        </v-card-title>

                        <!-- Details Body -->
                        <v-card-text>
                            <slot 
                                name="content-cards-body"
                                v-bind:item="item"
                            ></slot>
                        </v-card-text>
                        <v-divider></v-divider>
                        <div class="d-flex">
                            <v-spacer></v-spacer>
                            <v-divider vertical></v-divider>
                            <advbtn
                                icon="preview"
                                tooltip="View/Edit Details"
                                color_main="grey_trip"
                                v-on:click="updateEditor(true, item)"
                            ></advbtn>
                            <advbtn
                                icon="library_add"
                                tooltip="Copy Record"
                                color_main="grey_trip"
                                v-on:click="updateClone(true, item)"
                            ></advbtn>
                        </div>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Bottom Pagination -->
            <v-card tile class="grey_prim mt-5 d-flex justify-center">
                <pagination 
                    :offset="params.offset" 
                    :limit="params.limit" 
                    :count="params.count" 
                    v-on:offset="(emit) => { setOffset(emit) }"
                ></pagination>
            </v-card>

        </div>

        <!-- Editor Dialog --------------------------------------------------------------------------------- -->
        <v-card
            v-else
            tile
            min-height="500"
            class="grey_trip"
        >
            <!-- Editor Header -->
            <div class="mb-3">
                <!-- Header Mainbar -->
                <v-card 
                    tile
                    :flat="editor.active && !clone.active"
                    class="title grey_prim pa-3 pl-5 pr-5 d-flex component-wrap align-center"
                    style="z-index: 1"
                >
                    <!-- Header Slot -->
                    <div v-if="editor.item.id">
                        <slot 
                            name="editor-header"
                            v-bind:item="editor.item"
                        ></slot>
                    </div>
                    <div 
                        v-else 
                        v-text="clone.active ? 'Copy existing Record' : 'Neuer Eintrag'"
                    ></div>

                    <v-spacer></v-spacer>

                    <div class="d-flex component-wrap">
                        <v-btn
                            text
                            v-text="$root.label(clone.active ? 'cancel' : 'close')"
                            @click="close()"
                        ></v-btn>
                        <v-btn
                            tile
                            color="blue_prim"
                            class="ml-3 mr-n1"
                            v-text="$root.label(clone.active ? 'proceed' : 'save')"
                            @click="clone.active ? pasteClone() : sendItem()"
                        ></v-btn>
                    </div>
                </v-card>
                <!-- Header Toolbar -->
                <v-expand-transition>
                    <v-card 
                        v-if="editor.active && !clone.active"
                        tile 
                        class="grey_sec d-flex align-center"
                    >
                        <div 
                            class="caption ml-5"
                            v-html="$handlers.format.creation(this.l, editor.item)"
                        ></div>
                        <v-spacer></v-spacer>
                        <div class="d-flex">                                
                            <advbtn
                                v-if="select"
                                icon="touch_app"
                                :color_main="editor.item.id ? 'blue_prim' : 'grey_sec'"
                                color_hover="blue_sec"
                                :disabled="!editor.item.id"
                                v-on:click="selectItem(editor.item)"
                            ></advbtn>
                            <advbtn
                                icon="search"
                                tooltip="Back to List"
                                v-on:click="updateEditor(false, null)"
                            ></advbtn>
                            <v-divider vertical></v-divider>
                            <advbtn
                                :icon="editor.item.id ? 'add' : 'clear'"
                                :tooltip="editor.item.id ? 'New empty Record' : 'Reset Form'"
                                v-on:click="updateEditor(true, null);"
                            ></advbtn>
                            <advbtn
                                icon="library_add"
                                tooltip="Copy Record"
                                :disabled="!editor.item.id"
                                v-on:click="updateClone(true, editor.item); updateEditor(false, null)"
                            ></advbtn>
                            <advbtn
                                icon="delete"
                                tooltip="Delete Record"
                                :disabled="!editor.item.id"
                                v-on:click="deleteItem()"
                            ></advbtn>
                        </div>
                    </v-card>
                </v-expand-transition>
            </div>

            <!-- Body -->
            <v-card-text class="mt-n5">
                <!-- Editor -->
                <div v-if="editor.active">
                    <!-- Input -->
                    <div class="pa-3">
                        <div class="d-flex component-wrap align-center mb-3" style="width: 100%">
                            <v-hover>
                                <template v-slot:default="{ hover }" >
                                    <div
                                        class="d-flex"
                                        :class="hover && editor.item.id ? 'blue_sec--text' : ''"
                                        :style="editor.item.id ? 'cursor: pointer;' : ''"
                                        @click="() => { if (editor.item.id) { editor.expand = !editor.expand }}"
                                    >
                                        <div
                                            class="title text-uppercase"
                                            style="white-space: nowrap"
                                            v-html="$root.label('Editor')"
                                        ></div>
                                        <v-icon
                                            :color="hover && editor.item.id ? 'blue_sec' : ''"
                                            class="ml-2"
                                            v-text="'expand_' + (editor.expand ? 'less' : 'more')"
                                        ></v-icon>
                                    </div>
                                </template>
                            </v-hover>
                            <v-divider class="ml-4"></v-divider>
                        </div>
                        <!-- Slot -->
                        <v-expand-transition>
                            <slot
                                v-if="editor.expand"
                                name="editor-body"
                                v-bind:item="editor.item"
                            ></slot>
                            <div
                                v-else
                                class="mt-n4 mb-1 d-flex justify-center"
                                v-html="printEditorSumup()"
                            ></div>
                        </v-expand-transition>
                    </div>
                    <!-- Gallery -->
                    <v-expand-transition>
                        <ItemGallery
                            v-if="editor.item.id"
                            :key="editor.item.id"
                            :entity="entity"
                            :search_key="gallery" 
                            :search_val="editor.item.id ? editor.item.id : 0"
                            color_main="grey_trip"
                            color_hover="marked"
                            class="ml-3"
                            :linking="linking"
                        >
                            <template v-slot:link="slot">
                                <div :key="slot.link.active">
                                    <slot
                                        name="gallery-link"
                                        v-bind:link="slot.link"
                                    ></slot>
                                </div>
                            </template>
                        </ItemGallery>
                    </v-expand-transition>
                </div>

                <!-- Clone -->
                <div v-if="clone.active">
                    <div class="mt-1 mb-2">
                        <span v-if="this.l === 'de'">
                            Bitte wählen Sie die Werte aus, die Sie kopieren möchten (Leereinträge können nicht kopiert werden).<br />
                            Anschließend können Sie die Werte weiter bearbeiten.
                        </span>
                        <span v-else>
                            Please select the values you would like to copy (empty values cannot be copied).<br />
                            You can then edit the copied values.
                        </span>
                    </div>
                    <v-row class="pl-5">
                        <template v-for="(key) in Object.keys(clone.item)">
                            <v-col
                                v-if="clone.item[key].clone !== undefined"
                                :key="key"
                                cols="12" 
                                :sm="6"
                                :md="4"
                                :lg="3"
                            >
                                <div class="d-flex component-wrap align-start">
                                    <v-checkbox
                                        v-model="clone.item[key].clone"
                                        :disabled="clone.item[key].clone === null"
                                        class="mt-0"
                                        color="blue_prim"
                                    ></v-checkbox>
                                    <div class="pt-1 ml-1">
                                        <div 
                                            class="caption font-weight-thin text-uppercase mb-1" 
                                            v-text="clone.item[key].text"></div>
                                        <div 
                                            v-html="clone.item[key].content"
                                        ></div><!-- clone.item[key].value ? clone.item[key].value : '--'-->
                                    </div>
                                </div>
                            </v-col>
                        </template>
                    </v-row>
                </div>

            </v-card-text>
        </v-card>
    </v-expand-transition>

</div>
</template>


<script>

export default {
    data () {
        return {
            loading:        false,
            items_raw:      [],
            items_refresh:  0,
            display:        this.defaultDisplay,
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

            params: {
                offset: 0,
                limit: 12,
                count: 0,
                sort_by: null
            },

            sort: {
                by: this.defaultSortBy?.split('.')?.[0],
                asc: this.defaultSortBy?.split('.')?.[1]?.toLowerCase() === 'desc' ? false : true
            }
        }
    },

    props: {
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

        displays () {
            const displays = []
            if (this.table) { displays.push({ value: 'table', text: this.$root.label('table'), icon: 'view_headline' })}
            if (this.cards) { displays.push({ value: 'cards', text: this.$root.label('tiles'), icon: 'view_comfy' })}
            return displays
        },

        headers () {
            const self = this
            const headers = []
            Object.keys(self.attributes).forEach((key) => {
                if(self.attributes[key].header) { 
                    headers.push({ 
                        value: key, 
                        text: self.attributes[key].text,
                        content: self.attributes[key].content
                    }) 
                }
            })
            return headers
        },

        sorters () {
            const self = this
            const sorters = []
            Object.keys(self.attributes).forEach((key) => {
                if(self.attributes[key].sortable) { sorters.push({ value: key, text: self.attributes[key].text }) }
            })
            return sorters
        },

        filters () {
            const self = this
            const filters = {}
            Object.keys(self.attributes).forEach((key) => {
                if(self.attributes[key].filter !== undefined && self.attributes[key].filter !== null) { filters[key] = self.attributes[key].filter }
            })
            return filters
        },

        items () {
            if (this.fullFetch) {
                const self  = this

                // Filter items
                const filtered = this.items_raw.filter((item) => { return self.filterItems(item) })

                // Sort Items
                const sorted = filtered.sort((a, b) => { return self.sortItems(a, b) })

                // Set Offset/Limit
                const selection = []
                for (let i = this.params.offset; i < (this.params.offset + this.params.limit); i = ++i) {
                    if (sorted[i] === undefined) { break }
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
            if (!this.smallCards) {
                return { sm: 6, md: 4, lg: 3, xl: 2 }
            }
            else {
                return { sm: 4, md: 3, lg: 2, xl: 2 }
            }
        },

        defaultItem () {
            const self = this
            const defaultItem = {}
            Object.keys(self.attributes).forEach((key) => {
                defaultItem[key] = self.attributes[key].default
            })
            return defaultItem
        },

        filterStringified () {
            if (this.fullFetch) {
                return null
            }
            else {
                const self = this
                const strings = []
                Object.keys(self.attributes).forEach((key) => { 
                    if (self.attributes[key].filter !== undefined) { 
                        strings.push(self.attributes[key].filter === null ? '_' : self.attributes[key].filter)
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
            this.params.offset = 0
            this.getItems()
        }
    },

    async created () {
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
            const self = this
            const item = {}
            
            Object.keys(this.defaultItem).forEach((key) => {
                item[key] = input?.[key] ? input[key] : self.defaultItem[key]
            })

            this.editor = {
                active: active ? true : false,
                item: item,
                expand: true
            }
        },
        
        printEditorSumup () {
            const self = this
            const print = []
            this.headers.forEach((header) => {
                if (self.editor.item[header.value] !== undefined) {
                    print.push('<td class="pr-5" style="vertical-align: top">' +
                    '<div class="caption font-weight-thin text-uppercase">' + header.text + '</div>' +
                    '<div>' + self.attributes[header.value].content(self.editor.item) + '</div>' +
                    '</td>')
                }
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

        /*printClone (key) {
            console.log(this.clone)
            const item = {}
            const self = this
            Object.keys(this.clone.item).forEach((k) => { 
                item[k] = self.clone.item[k].value 
            })
            return this.attributes[key].content(item)
        },*/

        setOffset (offset) {
            this.params.offset = offset
            if (!this.fullFetch) {
                this.getItems()
            }
        },

        setLimit (limit) {
            this.params.limit = limit
            if (!this.fullFetch) {
                this.getItems()
            }
        },

        setSortBy (sorter) {
            this.params.offset = 0

            if (sorter.value === this.sort.by) {
                this.sort.asc = !this.sort.asc
            }
            else {
                this.sort = { by: sorter.value, asc: true }
            }

            if (!this.fullFetch) {
                this.getItems()
            }
        },

        close () {
            if (this.clone.active) {
                this.updateClone(false, null)
            }
            else {
                this.updateEditor(false, null)
            }
            if (!this.select && this.$route.params.id) {
                this.$router.push('/' + this.entity)
            }
        },

        selectItem (item) {
            if (item) {
                if (item.id === this.selected) {
                    alert(this.name + ' ID ' + item.id + ' is already selected!')
                }
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
            else {
                alert('ERROR: item is empty!')
            }
        },

        // Handle DBI Output -----------------------------------------------------------------------------
        async getItems () {
            this.loading = true
            let dbi = {}

            if (this.fullFetch) {
                dbi = await this.$root.DBI_SELECT_GET(this.entity, null)
            }
            else {
                const params = {}
                Object.keys(this.params).forEach((key) => {
                    params[key] = key != 'sort_by' ? this.params[key] : (this.sort.by + '.' + (this.sort.asc ? 'ASC' : 'DESC'))
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
                this.params = dbi.pagination
                this.fullFetch = false
            }
            else if (dbi?.pagination?.count !== undefined) {
                this.params.count = dbi.pagination.count
            }
            else {
                this.params.count = dbi?.contents?.[0]?.id ? dbi.contents.length : 0
            }
            
            this.items_raw = dbi?.contents?.[0]?.id ? dbi.contents : []

            this.loading = false
        },

        async getSingleItem (id) {
            this.loading = true

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

            this.loading = false
        },

        filterItems (item) {
            const self = this
            let match = true

            Object.keys(self.filters).forEach((key) => {
                if (typeof self.filters[key] === 'number') {
                    if (self.checkMatching(self.filters[key], item[key])) { match = false }
                }
                else {
                    self.filters[key].split(' ').forEach((filter) => {
                        if (self.checkMatching(filter, item[key])) { match = false }
                    })
                }
            })

            if (match) { return item }
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
            else {
                return false
            }
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
            
            if (a < b) {
                return asc ? -1 : 1
            }
            if (a > b) {
                return asc ? 1 : -1
            }

            return 0;            
        },

        // Provide DBI input -----------------------------------------------------------------------------
        async sendItem () {
            this.$root.loading = true

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

            this.$root.loading = false
        },

        async deleteItem () {
            const confirmed = confirm('Soll Eintrag ID ' + this.editor.item.id + ' wirklich gelöscht werden?')

            if (confirmed) {
                this.$root.loading = true

                    const response = await this.$root.DBI_INPUT_POST(this.entity, 'delete', this.editor.item);

                    if (response.success) {
                        this.$root.snackbar (response.success, 'success');
                        this.updateEditor(false, null)
                        this.getItems()
                    }
                    else {
                        console.log('Data Input Error: response was not ok');
                    }

                this.$root.loading = false
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