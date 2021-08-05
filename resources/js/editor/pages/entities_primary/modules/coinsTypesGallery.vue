<template>
<div ref="ctgallery">
    <template v-for="(e, i) in entities">
        <div
            :key="e"
            class="mb-2"
            :class="i === 1 ? ' mt-10' : ''"
        >
            <!-- Header -->
            <div
                :class="header.class"
                style="width: 100%;"
                :style="header.style"
            >
                <div
                    class="d-flex align-center justify-center"
                    style="width: 100%;"
                    :style="'height:' + (cardHeader ? 50 : 40) + 'px;'"
                >
                    <!-- Label -->
                    <v-hover>
                        <template v-slot:default="{ hover }" >
                            <div
                                class="d-flex"
                                :class="hover && data[e].items[0] ? 'blue_sec--text' : ''"
                                style="z-index: 1"
                                :style="data[e].items[0] ? 'cursor: pointer;' : ''"
                                @click="() => { if (data[e].items[0]) { data[e].expand = !data[e].expand }}"
                            >
                                <div
                                    class="title text-uppercase"
                                    style="white-space: nowrap"
                                    v-html="$root.label(e) + counter[e]"
                                />
                                <v-icon
                                    :color="hover && data[e].items[0] ? 'blue_sec' : ''"
                                    class="ml-2"
                                    v-text="'expand_' + (data[e].expand ? 'less' : 'more')"
                                />
                            </div>
                        </template>
                    </v-hover>

                    <!-- Loading -->
                    <div class="pl-3" :class="linking ? 'pr-3' : ''" style="width: 100%;">
                        <v-progress-linear
                            :indeterminate="data[e].loading"
                            :height="1"
                        />
                    </div>

                    <!-- Add Button -->
                    <v-btn
                        v-if="linking"
                        fab
                        dark
                        x-small
                        depressed
                        color="blue_prim"
                        style="z-index: 1"
                        @click="linkDialog(true, e)"
                    >
                        <v-icon>exposure_plus_1</v-icon>
                    </v-btn>
                </div>

                <div
                    class="d-flex justify-center align-center"
                    style="width: 100%; position: relative"
                    :style="'height:' + (cardHeader ? 50 : 40) + 'px;' + ($vuetify.breakpoint.mdAndUp ? ('margin-top: -' + (cardHeader ? 50 : 40) + 'px;') : '')"
                >
                    <div :class="cardHeader ? color_main : color_bg" style="position: absolute">
                        <pagination
                            :offset="data[e].offset"
                            :limit="limit"
                            :count="data[e].count"
                            :color="cardHeader ? color_main : color_bg"
                            :loading="data[e].loading"
                            :dense="!cardHeader"
                            v-on:offset="(emit) => { setOffset(e, emit) }"
                        />
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="mt-3">
                <v-expand-transition>

                    <v-row v-if="data[e].items[0] && data[e].expand">
                        <v-col
                            v-for="(item, i) in data[e].items"
                            :key="i"
                            :cols="cols"
                        >
                            <tradingcard
                                :key="i + entity + item.id"
                                :entity="e"
                                :item="item"
                                :linking="linking"
                                truncate
                                v-on:unlink="linkItem('unlink', e, { id: item.id })"
                                v-on:inherit="newInheritingType(item)"
                                v-on:represent="newRepresentingCoin(item)"
                            />
                        </v-col>
                    </v-row>

                    <div v-else-if="!data[e].items[0] && data[e].expand && !cardHeader" v-text="'--'" />
                </v-expand-transition>
            </div>
        </div>
    </template>

    <!-- Link Dialog -->
    <small-dialog
        :show="dialog.show"
        :text="linkHeader"
        icon="link"
        v-on:close="linkDialog(false)"
    >
        <div class="mb-5 mt-2" v-text="'Please provide a valid cn ID.'" />

        <coins-types-selector
            :entity="dialog.entity"
            :selected="dialog.item.id"
            v-on:select="(emit) => { this.dialog.item.id = emit }"
        />

        <slot name="link" v-bind:link="dialog" />

        <div class="pa-2 d-flex align-center justify-center">
            <v-btn
                fab
                x-small
                dark
                class="mr-3 grey darken-2"
                @click="linkDialog(false)"
            >
                <v-icon v-text="'clear'" />
            </v-btn>

            <v-btn
                fab
                small
                :dark="dialog.item.id ? true : false"
                color="blue_prim"
                class="ml-3"
                :disabled="!dialog.item.id"
                @click="linkItem('link', dialog.entity, dialog.item)"
            >
                <v-icon v-text="'done'" />
            </v-btn>
        </div>
    </small-dialog>

</div>
</template>



<script>

import linkedInherited from './linkedInherited.vue'
import tradingcard from './layoutTradingcard.vue'

export default {

    components: {
        linkedInherited,
        tradingcard
    },

    data () {
        return {
            search: {
                state_public: [0, 1, 2]
            },

            data: {
                types: {
                    items:      [],
                    loading:    false,
                    offset:     0,
                    count:      null,
                    expand:     true
                },

                coins: {
                    items:      [],
                    loading:    false,
                    offset:     0,
                    count:      null,
                    expand:     true
                }
            },

            dialog: {
                show: false,
                entity: null,
                item: { id: null }
            },

            width: 0
        }
    },

    props: {
        entity:         { type: String, required: true },
        hide:           { type: String, default: null },
        search_key:     { type: String },
        search_val:     { type: [String, Number] },
        color_main:     { type: String, default: 'tile_bg' },
        color_bg:       { type: String, default: 'sheet_bg' },
        color_hover:    { type: String, default: 'grey_prim' },
        limit:          { type: [String, Number], default: 12 },
        bigTiles:       { type: Boolean, default: false },
        //options:        { type: Boolean, default: false },
        //image_select:   { type: Boolean, default: false },
        //inherit_select: { type: Boolean, default: false },
        //no_inherited:   { type: Boolean, default: false },
        linking:        { type: Boolean, default: false },
        cardHeader:     { type: Boolean, default: false }
    },

    computed: {
        // Localization
        l () { return this.$root.language },

        entities () {
            const entities = []
            if (![this.entity, this.hide].includes('types')) { entities.push('types') }
            if (![this.entity, this.hide].includes('coins')) { entities.push('coins') }
            return entities
        },

        cols () {
            const width = this.width
            if (width < 250) return 12
            if (width < 750) return this.big ? 12 : 6
            if (width < 1000) return this.big ? 6 : 4
            if (width < 1500) return this.big ? 4 : 3
            return this.big ? 3 : 2
        },

        counter () {
            const self = this
            const counter = {}
            this.entities.forEach((entity) => {
                let count = '-'
                if (self.data[entity].count) {
                    if (self.data[entity].count <= self.limit) {
                        count = self.data[entity].count
                    }
                    else {
                        const start = (self.data[entity].offset + 1) + ''
                        const end = (self.data[entity].offset + self.data[entity].items.length) + ''
                        let string = ''
                        if (start === end) {
                            string = self.$handlers.format.number(self.l, start)
                        }
                        else {
                            string = self.$handlers.format.number(self.l, start) + '–' + (start.slice(-2) === end.slice(-2) ? end.slice(-3) : end.slice(-2))
                        }
                        count = string + '&nbsp;/&nbsp;' + self.$handlers.format.number(self.l, self.data[entity].count)
                    }
                }
                counter[entity] = '&ensp;(' + count + ')'
            })
            return counter
        },

        disabled () {
            return {
                types: {
                    previous: this.data.types.offset > 0 ? false : true,
                    next: this.data.types.offset < this.data.types.count - this.limit ? false : true
                },
                coins: {
                    previous: this.data.offset > 0 ? false : true,
                    next: this.data.coins.offset < this.data.coins.count - this.limit ? false : true
                }
            }
        },

        linkHeader () {
            return 'New Linking between ' + this.$handlers.format.cn_entity(this.entity, this.search_val) + ' and '  + this.$root.label(this.dialog.entity?.slice(0, -1))
        },

        header () {
            const data = { class: '', style: '' }
            if (this.cardHeader) {
                data.class = 'pl-4 mb-6 ' + this.color_main
                data.style = 'box-shadow: 0px 1px 2px rgba(0,0,0,0.3);'
            }
            return data
        }
    },

    created () {
        const self = this

        if (this.search_key && this.search_val) {
            this.search[this.search_key] = this.search_val

            this.entities.forEach((entity) => {
                self.GetItems(entity)
            })
        }
    },

    mounted () {
        this.onResize()
        window.addEventListener('resize', this.onResize, { passive: true })
    },

    methods: {
        async onResize () {
            this.width = this.$refs?.ctgallery?.clientWidth ?? 0
        },

        async GetItems (entity) {
            this.data[entity].loading    = true;

            const params = {
                sort_by: 'id.asc',
                offset: this.data[entity].offset,
                limit:  this.limit
            }

            const dbi = await this.$root.DBI_SELECT_POST(entity, params, this.search)

            if (dbi.contents !== undefined) {
                this.data[entity].offset = dbi.pagination.offset
                this.data[entity].count = dbi.pagination.count
                this.data[entity].items = dbi.contents
            }

            this.data[entity].loading = false;
        },

        async setOffset (entity, offset) {
            this.data[entity].offset = offset
            await this.GetItems(entity)
        },

        newInheritingType (item) {
            this.$emit('inherit', item)
        },

        newRepresentingCoin (item) {
            this.$emit('image', { id: item.id, image: (item.images?.[0]?.id ? item.images : null) })
        },

        // Linking -----------------------------------------------------------------------------
        linkDialog (show, entity, data) {
            const item = {}
            Object.keys(this.dialog.item).forEach((key) => {
                item[key] = data?.[key] === undefined ? null : data[key]
            })
            this.dialog = {
                show,
                entity: entity ?? null,
                item
            }
        },

        async linkItem (mode, e, item) {
            //console.log(this.dialog)
            if (mode) {
                const input = {
                    mode: mode,
                    add_entity: this.entity,
                    add_id: this.search_val,
                    entity: e
                }
                Object.keys(item).forEach((key) => {
                    if (![undefined, null].includes(item[key])) { input[key] = item[key] }
                })
                let confirmed = true
                if (mode === 'unlink') {
                    confirmed = confirm('Are you sure you want to unlink ' + this.$handlers.format.cn_entity(this.entity, this.search_val) + ' and ' + this.$handlers.format.cn_entity(input.entity, input.id) + '?')
                }
                if (confirmed) {
                    //console.log(input)
                    const response = await this.$root.DBI_INPUT_POST('link', 'input', input)
                    if (response.success) {
                        this.GetItems(input.entity)
                        this.linkDialog(false)
                        this.$root.snackbar((response.success[this.l] ? response.success[this.l] : response.success.en), 'success');
                    }
                }
            }
            else alert('mode is missing!')
        },
    }
}

</script>
