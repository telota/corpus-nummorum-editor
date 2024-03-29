<template>
<div>
    <v-autocomplete
        :value="selected"
        :search-input.sync="inputSearch"
        :items="items"
        item-value="id"
        item-text="string"
        :filter="filterItems"
        :no-filter="sync"
        dense
        outlined
        filled
        clearable
        :label="label"
        :prepend-icon="icon"
        :no-data-text="loading ? 'Loading ...' : 'Sorry, no matching data'"
        :disabled="inherited || (disabled ? true : false)"
        :hint="inherited || disabled ? (inherited ? 'Value inherited from type' : disabled) : ''"
        :persistent-hint="inherited || (disabled ? true : false)"
        :rules="rules"
        v-on:input="selectItem"
        v-on:click:clear="selectItem"
    >
        <!-- Sticky Dopdown Keyboard -->
        <template v-slot:prepend-item>
            <v-progress-linear :indeterminate="(sync && pending) || loading" :height="1" />

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

                <v-divider class="mb-1" />

                <v-expand-transition>
                    <div v-if="keyboard">
                        <keyboard
                            :string="inputSearch"
                            :layout="sk"
                            small
                            hide_options
                            v-on:input="(emit) => { inputSearch = emit }"
                        ></keyboard>

                        <v-divider class="mb-1 mt-1" />
                    </div>
                </v-expand-transition>
            </v-card>
        </template>

        <!-- List Item -->
        <template v-slot:item="slot">
            <v-list-item-avatar
                v-if="slot.item.image"
                tile
                class="white pa-1"
            >
                <img :src="$handlers.format.image_link(slot.item.image, 40)">
            </v-list-item-avatar>

            <v-list-item-content>
                <div
                    class="body-2"
                    v-text="slot.item.string"
                />
                <div
                    class="caption"
                    v-html="'ID&nbsp;' + slot.item.id + (slot.item.addition ? (', ' + slot.item.addition) : '')"
                />
            </v-list-item-content>
        </template>

        <!-- Sticky Dialog Option at the End -->
        <template v-if="dialog_available" v-slot:append-item>
            <v-card
                v-if="!hide_keyboard"
                tile
                flat
                class="appbar"
                style="position: sticky; bottom: 0; z-index: 10"
            >
                <v-divider />
                <v-btn
                    tile
                    block
                    depressed
                    x-small
                    v-text="'Click to open ' + entity_label + '-Dialog.'"
                    @click="dialog = true"
                />
                <v-divider />
            </v-card>
        </template>

        <!-- Selection Chip -->
        <template v-slot:selection="slot">
            <v-card
                flat
                v-bind="slot.attrs"
                class="transparent d-flex align-center mt-2 mb-1 mr-1"
            >
                <v-img
                    v-if="slot.item.image"
                    :src="$handlers.format.image_link(slot.item.image, 14)"
                    class="white ml-1"
                    max-height="14"
                    max-width="14"
                />
                <div
                    class="body-2 ml-1 mr-1"
                    v-text="printChip(slot.item)"
                />
            </v-card>
        </template>

        <!-- Prepend Icon / Entity Dialog -->
        <template v-if="!inherited && dialog_available" v-slot:prepend>
            <!-- Botton -->
            <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                    <v-hover v-slot="{ hover }">
                        <v-icon
                            v-bind="attrs"
                            v-on="on"
                            :class="hover ? 'blue_sec--text' : 'blue_prim--text'"
                            v-text="icon === 'help_outline' ? 'launch' : icon"
                            @click="dialog = true"
                        />
                    </v-hover>
                </template>
                <span v-text="'Click to open ' + entity_label + '-Dialog.'"></span>
            </v-tooltip>

            <component
                v-if="dialog"
                :is="entity"
                dialog
                select
                :selected="selected"
                :conditions="conditions"
                v-on:select="forwardSelection"
                v-on:close="dialog = false"
            />
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
            //inputSearch:         null,
            //ac_selected:    this.selected,
            //inputValue:     this.selected,
            inputSearch:    null,
            delayedSearch:  null,

            sk_keys:        this.$store.state.screenkeys[this.sk],
            keyboard:       ['monograms', 'legends'].includes(this.entity) ? true : false,

            loading:        false,
            pending:        false,
            dialog:         false,
            list:           this.entity
        }
    },

    props: {
        entity:             { type: String, required: true },
        selected:           { type: [String, Number], default: null },
        inherited:          { type: Boolean, default: false },
        disabled:           { type: [String, Boolean], default: null },
        conditions:         { type: Array, default: () => [] },

        label:              { type: String },
        icon:               { type: String, default: 'help_outline' },

        sk:                 { type: String, default: 'el_uc' },
        hide_keyboard:      { type: Boolean, default: false },
        rules:              { type: Array }
    },

    computed: {
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        entity_label () {
            return this.labels[this.entity] ? this.labels[this.entity] : (this.entity.slice(0, 1).toUpperCase() + this.entity.slice(1))
        },

        sync () {
            return this.items?.[0]?.inputSearch === undefined ? true : false
        },

        search () {
            return this.sync ? this.delayedSearch : this.inputSearch
        },

        dialog_available () {
            return this.inherited ? false : (Vue.options.components[this.entity] ? true : false)
        }
    },

    watch: {
        search: async function () {
            this.pending = this.search !== this.delayedSearch
            if (this.sync) this.items = await this.getItems(this.search, this.selected)
        },
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

            if (this.items && this.sync) {
                if (!this.items.map((row) => { return row.id }).includes(this.selected)) {
                    this.items = await this.getItems(null, this.selected)
                }
            }

            setInterval(() => {
                this.delayedSearch = this.inputSearch
            }, 750)
        },

        async getItems (search, ids) {
            // Define general parameters
            const params = {
                language: this.$root.language,
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
            if (search) params.search = search

            // Add selected IDs to parameters if given
            if (ids) params.id = ids

            // DBI call
            this.loading = true
            const dbi = await this.$root.DBI_SELECT_POST('lists/' + this.entity, params)
            this.loading = false

            if (dbi?.contents?.[0]) return dbi.contents
            else console.error('ERROR: Search Foreign Key "' + this.entity + '" did not receive any data.')
        },

        filterItems (item, queryText, itemText) {
            if (!queryText) return item

            let match = true
            // Split Input by blank spaces
            queryText.split(' ').forEach((queryString) => {
                if (!item.search.includes(queryString.toLowerCase())) match = false
            })
            if (match) return item
        },

        selectItem (input) {
            if (!['number', 'string'].includes(typeof input)) input = null
            this.$emit('select', input)
            this.inputSearch = ''
        },

        async forwardSelection (input) {
            if (input?.id) {
                const id = input.id
                if (!this.items.map((row) => row.id).includes(id)) {
                    if (this.sync) {
                        this.items = await this.getItems(null, id)
                    }
                    else {
                        const data = await this.getItems()
                        this.$store.commit('SET_LIST', { entity: this.list, data: data })
                        this.items = this.$store.state.lists.cache[this.list]
                    }
                }
                this.selectItem(id)
            }
            else alert('No ID received')
        },

        removeChip () {
            this.$emit('select', null)
        },

        printChip (item) {
            if (item.string !== undefined) return item.string ? item.string : ('ID ' + item.id)
            else return 'unknown'
        }
    }
}

</script>


<style>
    .v-autocomplete__content {
        max-width: 0px;
    }
</style>
