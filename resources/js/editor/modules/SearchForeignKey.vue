<template>
<div>
    <v-autocomplete
        :value="ac_selected"
        :search-input.sync="search"
        :items="items"
        item-value="id"
        item-text="string"
        :filter="filterItems"
        :no-filter="sync"
        :loading="loading"
        multiple
        dense
        outlined
        filled
        clearable
        :prepend-icon="icon"
        :label="label"
        :no-data-text="loading ? 'Loading ...' : 'Sorry, no matching data'"
        v-on:input="selectItem"
        v-on:click:clear="selectItem"
        v-on:keyup.enter="$emit('keyup_enter', true)"
    >
        <!-- Sticky Dopdown Keyboard -->
        <template v-slot:prepend-item>
            <v-card
                v-if="!hide_keyboard" 
                tile 
                flat
                style="position: sticky; top: 0; z-index: 10"
            >
                <v-btn
                    tile
                    block
                    depressed
                    x-small
                    v-text="(keyboard ? 'Hide' : 'Show') + ' Keyboard'"
                    @click="keyboard = !keyboard"
                ></v-btn>

                <v-divider class="mb-1"></v-divider>
                
                <v-expand-transition>
                    <div v-if="keyboard">
                        <keyboard
                            :string="search"
                            :layout="sk"
                            small
                            hide_options
                            v-on:input="(emit) => { search = emit }"
                        ></keyboard>

                        <v-divider class="mb-1 mt-1"></v-divider>
                    </div>
                </v-expand-transition>
            </v-card>
        </template>

        <!-- List Item -->
        <template v-slot:item="slot">
            <v-list-item-avatar v-if="slot.item.image" class="white pa-1" tile>
                <img :src="$handlers.format.image_link(slot.item.image, 40)">
            </v-list-item-avatar>
            <v-list-item-content>
                <div 
                    class="body-2"
                    v-text="slot.item.string" 
                ></div>
                <div
                    class="caption"
                    v-html="'ID&nbsp;' + slot.item.id + (slot.item.addition ? (', ' + slot.item.addition) : '')"
                ></div>
            </v-list-item-content>
        </template>

        <!-- Selection Chip -->
        <template v-slot:selection="slot">
            <v-card
                v-bind="slot.attrs"
                class="appbg d-flex align-center mt-1 mr-1"
                @click="removeChip(slot.item.id)"
            >
                <v-img
                    v-if="slot.item.image"
                    :src="$handlers.format.image_link(slot.item.image, 14)"
                    class="white ml-1"
                    max-height="14"
                    max-width="14"
                ></v-img>
                <div
                    class="caption ml-1 mr-1 text-truncate"
                    v-text="printChip(slot.item)"
                ></div>                
                <v-icon small v-text="'clear'"></v-icon>
            </v-card>
        </template>

    </v-autocomplete>

</div>
</template>


<script>

import keyboard from './keyboard.vue'

export default {
    components: {
        keyboard
    },

    data () {
        return {
            items:          [],
            search:         null,
            ac_selected:    this.selected?.[this.selected_key] ? this.selected[this.selected_key] : [],

            sk_keys:        this.$store.state.screenkeys[this.sk],
            keyboard:       ['monograms', 'legends'].includes(this.entity) ? true : false,

            loading:        false,
            dialog:         false,
            list:           this.entity
        }
    },

    props: {
        entity:             { type: String, required: true },
        selected:           { type: Object, required: true },
        selected_key:       { type: String, required: true },
        conditions:         { type: Array },

        label:              { type: String },
        icon:               { type: String, default: 'help_outline' },

        sk:                 { type: String, default: 'el_uc' },
        hide_keyboard:      { type: Boolean, default: false }
    },
    
    computed: {
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        sync () { 
            return this.items?.[0]?.search === undefined ? true : false 
        },
        observe () {
            return JSON.stringify(this.selected)
        }
    },

    watch: {
        search: async function (search) {
            if (this.sync) {
                this.items = await this.getItems(search, this.selected?.[this.selected_key])
            }
        },
        observe: function () {
            this.ac_selected = this.selected?.[this.selected_key]?.[0] ? this.selected[this.selected_key] : []
        }
    },

    created () {
        if (this.conditions) {
            const conditions = []
            this.conditions.forEach((condition) => {
                const key = Object.keys(condition)
                conditions.push(key + condition[key])
            })
            this.list = this.entity + conditions.join('_')
        }
        this.init()
    },

    methods: {
        async init () {
            if (!this.$store.state.lists.cache[this.list]) {
                const data = await this.getItems()
                this.$store.commit('SET_LIST', { entity: this.list, data: data })
            }
            this.items = this.$store.state.lists.cache[this.list]

            if (this.items && this.sync && this.ac_selected[0]) {
                if (!this.items.map((row) => { return row.id }).includes(this.ac_selected)) {
                    this.items = await this.getItems(null, this.ac_selected)
                }
            }
        },

        async getItems (search, ids) {
            // Define general parameters
            const params = {
                language: this.l,
                reduced: 1,
            };

            // Add conditions to parameters if given
            if (this.conditions) {
                this.conditions.forEach((condition) => {
                    const key = Object.keys(condition)
                    params[key] = condition[key]
                })
            }

            // Add Search to parameters if given
            if (search) {
                params.search = search
            }

            // Add selected IDs to parameters if given
            if (ids) {
                params.id = ids
            }

            // DBI call
            this.loading = true
            const dbi = await this.$root.DBI_SELECT_POST('lists/' + this.entity, params)
            this.loading = false

            if (dbi?.contents?.[0]) {
                return dbi.contents
            }
            else {
                console.log('ERROR: Search Foreign Key "' + this.entity + '" did not receive any data.')
            }
        },

        filterItems (item, queryText, itemText) {
            if (!queryText) {
                return item
            }
            else {
                let match = true
                // Split Input by blank spaces
                queryText.split(' ').forEach((queryString) => {
                    if (!item.search.includes(queryString.toLowerCase())) {
                       match = false
                    }
                })
                if (match) { return item }
            }
        },

        selectItem (input) {
            this.$emit('select', input)
            this.search = ''
        },

        removeChip (item) {
            const selected = this.selected[this.selected_key]
            const index = selected.indexOf(item)
            if (index >= 0) { selected.splice(index, 1) }
            this.$emit('select', selected)
        },

        printChip (item) {
            if (item.string) {
                return item.string
                //return item.string.length > 60 ? (item.string.slice(0, 60) + ' ...') : item.string
            }
            else {
                const selected = this.selected?.[this.selected_key]
                const index = selected.indexOf(item)
                return 'ID ' + (selected?.[index] ? selected[index] : 'unknown')
            }
        }
    }
}

</script>


<style>
    .v-autocomplete__content {
        max-width: 0px;
    }
</style>