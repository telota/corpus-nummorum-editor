<template>
<div>
    <component-toolbar>
        <v-spacer />
        <v-hover v-slot="{ hover }">
            <div
                class="d-flex align-center justify-center headline font-weight-bold light-blue--text text--darken-2"
                :class="hover ? 'header_hover' : ''"
                style="width: 200px; height: 50px; cursor: pointer;'"
                @click="$router.push('/legends/new')"
            >
                <v-icon v-text="'add'" class="mr-2" color="light-blue darken-2" />
                <div v-text="'New Item'" />
            </div>
        </v-hover>
    </component-toolbar>

    <component-content style="overflow-y: hidden">
        <v-row class="pt-0 ma-0" no-gutters>

            <!-- Legends Index -->
            <v-col cols=3>
                <v-card
                    tile
                    class="legend-tile sheet_bg"
                    style="margin: 16px 8px 16px 16px;"
                    :style="'outline: 1px solid ' + ($vuetify.theme.dark ? 'black' : 'grey')"
                >
                    <div style="flex: 0 1 auto;">
                        <div class="pa-3">
                            <v-text-field outlined filled clearable dense
                                ref="searchString"
                                v-model="search.string"
                                :label="$root.label('legend')"
                                append-icon="keyboard"
                                class="mb-n4"
                                v-on:click:append="showKB = !showKB"
                            />
                            <v-expand-transition>
                                <div v-if="showKB" class="d-flex justify-center">
                                    <keyboard
                                        :string="search.string"
                                        layout="el_uc"
                                        small
                                        hide_options
                                        v-on:input="keyboardInput"
                                    ></keyboard>
                                </div>
                            </v-expand-transition>
                        </div>

                        <div class="pl-3 pr-3 mt-n3 d-flex justify-center">
                            <div class="d-flex align-center">
                                <v-checkbox
                                    v-model="search.transform"
                                    label="use Pseudo-Greek"
                                    class="mr-1"
                                />
                                <sup class="body-1" style="cursor: pointer;" @click="showLG = true"> *</sup>
                            </div>
                            <div class="d-flex align-center ml-5">
                                <v-checkbox
                                    v-model="search.regex"
                                    label="use REGEX"
                                    class="mr-1"
                                />
                                <sup class="body-1"><a href="https://en.wikipedia.org/wiki/Regular_expression#Syntax" target="_blank"> *</a></sup>
                            </div>
                        </div>

                        <div class="pl-3 pr-3 pt-2">
                            <v-text-field outlined filled clearable dense
                                v-model="search.keywords"
                                :label="$root.label('Keywords')"
                                class="mb-n3"
                            />
                        </div>

                        <div
                            class="text-center mb-2"
                            v-text="loading ? 'Please wait ...' : (list.length + ' records' + (searchDisplay ? (' for \'' + searchDisplay + '\'') : ''))"
                        />

                        <v-progress-linear :indeterminate="loading" height="1" />
                    </div>

                    <v-virtual-scroll
                        :items="list"
                        item-height="60"
                        style="flex: 1 1 auto;"
                    >
                        <template v-slot:default="{ item, index }">
                            <div
                                class="pl-3 pr-3 d-flex align-center"
                                :class="item.string === listSelect ? 'grey_prim' : ''"
                                style="height: 60px; cursor: pointer;"
                                @click="selectListItem(item)"
                            >
                                <div>
                                    <span v-html="item.string ? item.string : '<i>EMPTY</i>'" />
                                </div>
                            </div>
                        </template>
                    </v-virtual-scroll>

                </v-card>
            </v-col>

            <!-- Legend Variants -->
            <v-col cols=3>
                <v-card
                    tile
                    class="legend-tile sheet_bg"
                    style="margin: 16px 8px 16px 8px;"
                    :style="'outline: 1px solid ' + ($vuetify.theme.dark ? 'black' : 'grey')"
                >
                    <div style="flex: 0 1 auto;">
                        <div class="pa-3">
                            <v-row>
                                <v-col cols="6">
                                    <v-select
                                        v-model="search.role"
                                        :items="[
                                            { value: null, text:'All' },
                                            { value: 0, text:'Coins' },
                                            { value: 1, text:'Types' },
                                            { value: 2, text:'Coins/Types' }
                                        ]"
                                        label="Entity"
                                        outlined dense filled
                                    />
                                </v-col>

                                <v-col cols="6">
                                    <v-select
                                        v-model="search.side"
                                        :items="[
                                            { value: null, text:'All' },
                                            { value: 0, text:'Obverse' },
                                            { value: 1, text:'Reverse' },
                                            { value: 2, text:'Obverse/Reverse' }
                                        ]"
                                        label="Side"
                                        outlined dense filled
                                    />
                                </v-col>
                            </v-row>
                        </div>

                        <div
                            class="text-center mb-2"
                            v-text="listSelect === 0 ? '...' : (records.length + ' variants' + (records.length < recordsRaw.length ? (' (' + recordsRaw.length + ' in total)') : ''))"
                        />

                        <v-divider />
                    </div>

                    <div v-if="listSelect === 0">
                        <div class="body-1 pt-10 text-center" v-text="'Legend Variants'" />
                        <div class="caption pt-1 pb-10 text-center" v-text="'Click on a list item to show variants'" />
                    </div>

                    <v-virtual-scroll
                        v-else-if="records[0]"
                        :items="records"
                        item-height="100"
                        style="flex: 1 1 auto;"
                    >
                        <template v-slot:default="{ item, index }">
                            <div
                                class="pa-3 d-flex justify-space-between"
                                :class="item.id === recordSelect ? 'grey_prim' : ''"
                                style="height: 100px; cursor: pointer;"
                                @click="selectRecord(item)"
                            >
                                <div>
                                    <div class="body-1 font-weight-thin" v-html="item.legend" />
                                    <div class="caption pt-1">
                                        <a :href="'/editor#/legends/' + item.id" class="caption" v-html="'ID&nbsp;' + item.id" />,
                                        <span v-text="item.language === 'el' ? 'Greek' : (item.language === 'la' ? 'Latin' : item.language)" />,
                                        <span v-text="item.role === 0 ? 'Coins' : (item.role === 1 ? 'Types' : 'Coins/Types')" />,
                                        <span v-text="item.side === 0 ? 'Obverse' : (item.side === 1 ? 'Reverse' : 'Obverse/Reverse')" />
                                    </div>
                                    <div class="caption" v-html="item.keywords ? item.keywords : '-'" />
                                </div>

                                <div class="ml-3" v-html="getDirectionImage(item)" />
                            </div>
                            <v-divider />
                        </template>
                    </v-virtual-scroll>
                </v-card>
            </v-col>

            <!-- Preview -->
            <v-col cols=6>
                <v-card
                    tile
                    class="pa-3 legend-tile sheet_bg"
                    style="margin: 16px 16px 16px 8px; overflow-y: auto"
                    :style="'outline: 1px solid ' + ($vuetify.theme.dark ? 'black' : 'grey')"
                >
                    <ItemGallery
                        v-if="recordSelect"
                        :key="recordSelect"
                        :entity="'legends'"
                        :search_key="'id_legend'"
                        :search_val="recordSelect ? recordSelect : 0"
                        :tiles="4"
                        :limit="8"
                        color_main="grey_trip"
                        color_hover="marked"
                    />
                    <div v-else>
                        <div class="body-1 pt-7 text-center" v-text="'Related Types and Coins'" />
                        <div class="caption pt-1 pb-7 text-center" v-text="'Click on a Variant to show Galery'" />
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <small-dialog
            :show="showLG"
            :width="300"
            icon="text_rotation_none"
            text="Pseudo Greek"
            v-on:close="showLG = false"
        >
            <v-row>
                <template v-for="(greek, latin, i) in latinGreek">
                    <v-col cols=2 :key="greek" class="text-end" v-html="greek" />
                    <v-col cols=2 :key="i" class="text-center" v-html="'=>'" />
                    <v-col cols=2 :key="latin" v-html="latin" />
                </template>
            </v-row>
        </small-dialog>

    </component-content>
</div>
</template>



<script>

import keyboard from './../../modules/keyboard.vue'

export default {
    components: {
        keyboard
    },
    data () {
        return {
            component:          'legend',
            entity:             'legends',

            search: {
                string: null,
                regex: false,
                transform: false,
                keywords: null,
                role: null,
                side: null
            },

            showKB: true,
            listRaw: [],
            listSelect: 0,
            loading: true,
            recordsRaw: [],
            recordSelect: null,

            showLG: false,
            latinGreek: {
                A: 'Α',
                B: 'Β',
                G: 'Γ',
                D: 'Δ',
                E: 'Ε',
                Z: 'Ζ',
                H: 'Η',
                J: 'Θ',
                I: 'Ι',
                K: 'Κ',
                L: 'Λ',
                M: 'Μ',
                N: 'Ν',
                X: 'Ξ',
                O: 'Ο',
                P: 'Π',
                R: 'Ρ',
                S: 'Σ',
                T: 'Τ',
                Y: 'Υ',
                Q: 'Φ',
                C: 'Χ',
                V: 'Ψ',
                W: 'Ω'
            }
        }
    },

    props: {
        select:     { type: Boolean, default: false },
        selected:   { type: [Number, String], default: 0 }
    },

    computed: {

        searchString () {
            if (!this.search.string) return this.search.string

            let string = this.search.string.trim()
            if (this.search.transform) for (const [key, value] of Object.entries(this.latinGreek)) string = string.replaceAll(key, value)
            return string
        },

        searchDisplay () {
            const toReturn = []

            if (this.searchString) toReturn.push(this.searchString)
            if (this.search.keywords) toReturn.push(this.search.keywords)

            return toReturn[0] ? toReturn.join(', ') : null
        },

        list () {
            if (!this.search.string && !this.search.keywords) return this.listRaw

            let string = this.searchString
            if (this.search.regex) string = new RegExp(string)

            return this.listRaw.filter((item) => {

                let match = true

                if (this.search.string) {
                    if (!item.string) match = false

                    else if (this.search.regex) match = item.string.match(string)
                    else {
                        string.trim().split(/\s+/).forEach((frag) => {
                            if (match && frag) match = item.string.includes(frag)
                        })
                    }
                }

                if (this.search.keywords && match) {
                    let keywords = item.records.map((r) => r.keywords).join(', ').toLowerCase()
                    this.search.keywords.trim().split(/\s+/).forEach((frag) => {
                        if (match && frag) match = keywords.includes(frag.toLowerCase())
                    })
                }

                return match
            })
        },

        records () {
            if (!this.recordsRaw[0]) return []

            let items = this.recordsRaw.filter((item) => {

                let match = true

                if (this.search.role !== null) match = item.role === this.search.role ? true : false
                if (match && this.search.side !== null) match = item.side === this.search.side ? true : false

                return match
            })

            return items[1] ? items.sort((a, b) => a.legend?.localeCompare(b.legend, 'el', {ignorePunctuation: true})) : items
        }
    },

    created () {
        this.getlist()
    },

    methods: {

        async getlist () {
            const self = this
            self.loading = true
            self.listRaw = []

            await axios.get('/dbi/legends_index')
            .then((response) => {
                const dbi = response.data?.contents
                console.log(dbi?.length + ' legends fetched')
                if (dbi[0]) self.listRaw = dbi
            })
            .catch((error) => {
                self.$root.AXIOS_ERROR_HANDLER(error)
            })

            self.loading = false
        },

        selectListItem (item) {
            this.recordSelect = null
            this.listSelect = item.string
            this.recordsRaw = item.records
        },

        selectRecord (item) {
            this.recordSelect = item.id
        },

        keyboardInput (emit) {
            this.search.string = emit
            this.$refs['searchString'].$refs.input.focus();
        },

        getDirectionImage (item) {

            if (!item?.direction) return '<div class="d-flex align-center justify-center" style="height: 40px; width: 40px"><div>--</div></div>'

            let direction = '000' + item.direction
            direction = direction.slice(-3)
            direction = '/storage/legend-directions/' + direction + '.svg'
            return this.$handlers.format.image_tile(direction, 40)
        }
    }
}

</script>

<style lang="scss" scoped>
    .legend-tile {
        height: calc(100vh - 122px);
        display: flex;
        flex-flow: column;
    }
</style>
