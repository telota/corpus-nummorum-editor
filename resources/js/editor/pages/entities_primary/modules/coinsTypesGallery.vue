<template>
<div>
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
                                ></div>
                                <v-icon
                                    :color="hover && data[e].items[0] ? 'blue_sec' : ''"
                                    class="ml-2"
                                    v-text="'expand_' + (data[e].expand ? 'less' : 'more')"
                                ></v-icon>
                            </div>
                        </template>
                    </v-hover>
                    <v-progress-linear
                        :indeterminate="data[e].loading"
                        :height="1"
                        class="ml-4"
                    />
                    <!-- Add Button -->
                    <v-btn
                        v-if="linking"
                        fab
                        dark
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
                            cols="12"
                            :sm="cols.sm"
                            :md="cols.md"
                            :xl="cols.xl"
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
                />
                <slot name="link" v-bind:link="dialog_link" />
            </div>
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
        color_main:     { type: String, default: 'tile_bg' },
        color_bg:       { type: String, default: 'sheet_bg' },
        color_hover:    { type: String, default: 'grey_prim' },
        limit:          { type: [String, Number], default: 12 },
        tiles:          { type: [String, Number], default: 6 },
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
