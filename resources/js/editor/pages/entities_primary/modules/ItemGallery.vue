<template>
<div>
    <template v-for="(e, i) in entities">
        <div
            :key="e"
            class="mb-2"
            :class="i === 1 ? 'mt-5' : ''"
        >
            <!-- Header -->
            <div style="width: 100%;">

                <div
                    class="d-flex align-center justify-center"
                    style="width: 100%; height: 40px;"
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
                                ></div>
                                <v-icon
                                    :color="hover && data[e].items[0] ? 'blue_sec' : ''"
                                    class="ml-2"
                                    v-text="'expand_' + (data[e].expand ? 'less' : 'more')"
                                ></v-icon>
                            </div>
                        </template>
                    </v-hover>
                    <v-divider
                        v-if="!data[e].loading"
                        class="ml-4 mr-4"
                    ></v-divider>
                    <v-progress-linear
                        v-else
                        indeterminate
                        class="ml-4"
                    ></v-progress-linear>
                    <!-- Add Button -->
                    <v-btn
                        v-if="linking"
                        fab
                        x-small
                        depressed
                        color="blue_prim"
                        class="ml-4"
                        style="z-index: 1"
                        @click="linkDialog(true, e)"
                    >
                        <v-icon>exposure_plus_1</v-icon>
                    </v-btn>
                </div>
            </div>

            <div
                class="d-flex justify-center align-center"
                style="width: 100%; height: 40px;"
                :style="($vuetify.breakpoint.mdAndUp ? 'margin-top: -40px;' : '')"
            >
                <div :class="color_main">
                    <pagination
                        :offset="data[e].offset"
                        :limit="limit"
                        :count="data[e].count"
                        :color="color_main"
                        :loading="data[e].loading"
                        dense
                        v-on:offset="(emit) => { setOffset(e, emit) }"
                    />
                </div>
            </div>

            <!-- Content -->
            <div class="mt-3">
                <v-expand-transition>
                    <div v-if="!data[e].items[0]" v-text="'--'" />

                    <v-row v-else-if="data[e].expand">
                        <v-col
                            v-for="(item, i) in data[e].items"
                            :key="i"
                            cols="12"
                            :sm="cols.sm"
                            :md="cols.md"
                            :xl="cols.xl"
                        >
                            <v-card tile class="appbar">
                                <!-- Image -->
                                <Imager
                                    coin hide_text contain
                                    :key="item.id"
                                    :item="item.images ? item : {images: []}"
                                    :color_background="(item.images ? (item.images[0].obverse.bg_color == 'white' ? 'white' : null) : null)"
                                    :class="(item.images ? (item.images[0].obverse.bg_color == 'white' ? 'white' : 'imgbg') : 'imgbg')+' pa-1'"
                                ></Imager>

                                <!-- Content -->
                                <v-card-text class="pa-3 caption">
                                    <div class="mt-n1 mb-1">
                                        <!-- Name -->
                                        <div class="body-1 font-weight-black d-flex">
                                            <div
                                                class="text-truncate"
                                                v-text="'cn ' + e.slice(0, -1)"
                                            ></div>
                                            <div class="d-flex">
                                                <div v-html="'&nbsp;' + item.id"></div>
                                                <div v-html="$handlers.format.cn_public_link(item)"></div>
                                            </div>
                                        </div>
                                        <!-- Types / Coins -->
                                        <linkedInherited
                                            :entity="e"
                                            :item="item"
                                            v-on:details="(emit) => { dialog_details = { entity: emit.entity, id: emit.id, public: 0 }}"
                                        ></linkedInherited>
                                        <!-- Mint -->
                                        <div
                                            v-if="item.mint.text[l]"
                                            class="text-truncate"
                                            v-text="item.mint.text[l]"
                                        ></div>
                                    </div>

                                    <!-- Diameter and Weight -->
                                    <div
                                        class="caption"
                                        v-html="$handlers.show_item_data(l, e, item, 'card_header')"
                                    ></div>

                                    <!-- Inherited from Type
                                    <v-card
                                        v-if="entity === 'coins' && inheritingType(item)"
                                        tile
                                        flat
                                        class="transparent mt-1"
                                        v-text="'Inherited from Type ' + inheritingType(item)"
                                        @click="details_dialog = { entity: 'types', id: inheritingType(item), public: 0 }"
                                    ></v-card> -->

                                    <!-- Depiction -->
                                    <v-divider class="mb-2 mt-1"></v-divider>
                                        <v-tooltip bottom v-for="(s) in ['obverse', 'reverse']" :key="s">
                                            <template v-slot:activator="{ on }">
                                                <div v-on="on" class="caption mb-2">
                                                    <div
                                                        class="font-weight-bold text-uppercase"
                                                        v-text="s.slice(0, 1)"
                                                        style="position: absolute"
                                                    ></div>
                                                    <div
                                                        class="pl-4 font-weight-thin text-truncate"
                                                        v-text="item[s].legend && item[s].legend.string ? item[s].legend.string : '--'"
                                                    ></div>
                                                    <div
                                                        class="pl-4 text-truncate"
                                                        v-text="item[s].design && item[s].design.text[l] ? item[s].design.text[l] : '--'"
                                                    ></div>
                                                </div>
                                            </template>
                                            <div>
                                                <div
                                                    class="font-weight-bold"
                                                    v-text="$root.label(s)"
                                                ></div>
                                                <div
                                                    class="font-weight-thin"
                                                    v-text="item[s].legend && item[s].legend.string ? item[s].legend.string : '--'"
                                                ></div>
                                                <div v-text="item[s].design && item[s].design.text[l] ? item[s].design.text[l] : '--'"></div>
                                            </div>
                                        </v-tooltip>

                                    <!-- Footer -->
                                    <v-divider class="mt-1"></v-divider>
                                    <v-tooltip bottom>
                                        <template v-slot:activator="{ on }">
                                            <div
                                                v-on="on"
                                                class="caption mt-1 text-truncate"
                                                v-html="$handlers.show_item_data(l, e, item, 'card_footer')"
                                            ></div>
                                        </template>
                                        <div v-html="$handlers.show_item_data(l, e, item, 'card_footer')"></div>
                                    </v-tooltip>
                                </v-card-text>

                                <!-- Actions -->
                                <v-divider></v-divider>
                                <commandbar
                                    :entity="e"
                                    small
                                    :id="item.id"
                                    :link_off="linking"
                                    :key="e + item.id"
                                    v-on:details="dialog_details = { entity: e, id: item.id, public: item.public }"
                                    v-on:unlink="linkItem('unlink', e, { id: item.id })"
                                    v-on:inherit="newInheritingType(item)"
                                    v-on:represent="newRepresentingCoin(item)"
                                ></commandbar>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-expand-transition>
            </div>
        </div>
    </template>

    <!-- Link Dialog
    <v-dialog v-model="dialog_link.active" persistent max-width="400">
        <v-card tile>

            <dialogbartop
                icon="link"
                :text="'new link'"
                v-on:close="dialog_link = { active: false, entity: null, id: null }"
            ></dialogbartop>

            <v-card-text class="d-flex component-wrap pt-8">
                <v-text-field
                    v-if="dialog_link.entity"
                    dense
                    outlined
                    filled
                    v-model="dialog_link.id"
                    prepend-icon="link"
                    :label="'cn ' + dialog_link.entity.slice(0, -1) + ' ID'"
                    :rules="[$handlers.rules.id]"
                ></v-text-field>
                <v-btn
                    text
                    v-text="'OK'"
                    class="ml-3"
                    @click="$emit('link', dialog_link.id); dialog_link = { active: false, entity: null, id: null }"
                ></v-btn>
            </v-card-text>

        </v-card>
    </v-dialog> -->

    <!-- Details Dialog -->
    <detailsdialog
        v-if="dialog_details.entity"
        :entity="dialog_details.entity"
        :id="dialog_details.id"
        :public_state="dialog_details.public"
        v-on:close="dialog_details = { entity: null, id: null, public: 0 }"
    ></detailsdialog>

    <!-- Link Dialog -->
    <simpleSelectDialog
        :active="dialog_link.active"
        :width="500"
        :text="linkHeader"
        icon="link"
        v-on:close="linkDialog(false)"
    >
        <template v-slot:content>
            <div class="mt-2">
                <v-text-field dense outlined filled clearable
                    v-model="dialog_link.item.id"
                    :label="dialog_link.entity ? ($root.label(dialog_link.entity.slice(0, -1)) + ' ID') : 'none'"
                    :prepend-icon="dialog_link.entity === 'coins' ? 'copyright' : 'blur_circular'"
                    :rules="[$handlers.rules.id]"
                    class="mb-2"
                    counter=255
                ></v-text-field>
                <slot
                    name="link"
                    v-bind:link="dialog_link"
                ></slot>
            </div>
            <!--<div class="ml-n6" style="position: absolute; width: 100%; bottom: 0">
                <div class="pa-2 d-flex align-center">
                    <v-spacer></v-spacer>
                    <v-btn
                        fab
                        x-small
                        color="grey_sec"
                        class="mr-3"
                        @click="linkDialog(false)"
                    >
                        <v-icon v-text="'clear'"></v-icon>
                    </v-btn>
                    <v-btn
                        fab
                        small
                        color="blue_prim"
                        class="ml-3"
                        @click="linkItem('link', dialog_link.entity, dialog_link.item)"
                    >
                        <v-icon v-text="'done'"></v-icon>
                    </v-btn>
                    <v-spacer></v-spacer>
                </div>
            </div>-->
        </template>
        <template v-slot:actions>
            <div class="pb-2 d-flex align-center">
                <v-spacer></v-spacer>
                <v-btn
                    fab
                    x-small
                    color="grey_sec"
                    class="mr-3"
                    @click="linkDialog(false)"
                >
                    <v-icon v-text="'clear'"></v-icon>
                </v-btn>
                <v-btn
                    fab
                    small
                    color="blue_prim"
                    class="ml-3"
                    @click="linkItem('link', dialog_link.entity, dialog_link.item)"
                >
                    <v-icon v-text="'done'"></v-icon>
                </v-btn>
                <v-spacer></v-spacer>
            </div>
        </template>
    </simpleSelectDialog>

</div>
</template>



<script>

import linkedInherited from './linkedInherited.vue'

export default {

    components: {
        linkedInherited
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

            /*dialog_options: {
                active: false,
                entity: null,
                id: null
            },*/
            dialog_details: {
                entity: null,
                id: null,
                public: 0
            },
            dialog_link: {
                active: false,
                entity: null,
                item: {}
            }
        }
    },

    props: {
        entity:         { type: String, required: true },
        hide:           { type: String, default: null },
        search_key:     { type: String },
        search_val:     { type: [String, Number] },
        color_main:     { type: String, default: 'appbar' },
        color_hover:    { type: String, default: 'sysbar' },
        limit:          { type: [String, Number], default: 12 },
        tiles:          { type: [String, Number], default: 6 },
        //options:        { type: Boolean, default: false },
        //image_select:   { type: Boolean, default: false },
        //inherit_select: { type: Boolean, default: false },
        //no_inherited:   { type: Boolean, default: false },
        linking:        { type: Boolean, default: false }
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        entities () {
            const entities = []
            if (![this.entity, this.hide].includes('types')) { entities.push('types') }
            if (![this.entity, this.hide].includes('coins')) { entities.push('coins') }
            return entities
        },

        cols () {
            return this.tiles === 4 ? { sm: 6, md: 3, xl: 3 } : { sm: 4, md: 2, xl: 2 }
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
            return 'New Linking between ' + this.$handlers.format.cn_entity(this.entity, this.search_val) + ' and '  + this.$root.label(this.dialog_link.entity?.slice(0, -1))
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

    methods: {
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
        linkDialog (active, entity, data) {
            const item = {}
            Object.keys(this.dialog_link.item).forEach((key) => {
                item[key] = data?.[key] === undefined ? null : data[key]
            })
            this.dialog_link = {
                active: active,
                entity: entity ? entity : null,
                item: item
            }
        },

        async linkItem (mode, e, item) {
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
                    console.log(input)
                    const response = await this.$root.DBI_INPUT_POST('link', 'input', input)
                    if (response.success) {
                        this.GetItems(input.entity)
                        this.linkDialog(false)
                        this.$root.snackbar((response.success[this.l] ? response.success[this.l] : response.success.en), 'success');
                    }
                }
            }
            else {
                alert('mode is missing!')
            }
        },
    }
}

</script>
