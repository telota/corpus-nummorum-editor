<template>
<div>
    <component-toolbar>
        <v-spacer />
        <v-hover v-slot="{ hover }">
            <div
                class="d-flex align-center justify-center headline font-weight-bold light-blue--text text--darken-2"
                :class="hover ? 'header_hover' : ''"
                style="width: 200px; height: 50px; cursor: pointer;'"
                @click="$router.push('/persons/new')"
            >
                <v-icon v-text="'add'" class="mr-2" color="light-blue darken-2" />
                <div v-text="'New Item'" />
            </div>
        </v-hover>
    </component-toolbar>

    <component-content style="overflow-y: hidden">
        <v-row class="pt-0 ma-0" no-gutters>

            <!-- Legends Index -->
            <v-col cols=4>
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
                                :label="$root.label('String')"
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
                                    />
                                </div>
                            </v-expand-transition>
                        </div>

                        <div class="pa-3">
                            <!-- Combination -->
                            <v-checkbox
                                v-model="search.combination"
                                label="Monogram Combination"
                                class="ma-0 pa-0"
                            />

                            <!-- Pseudo-Greek -->
                            <div class="d-flex">
                                <v-checkbox
                                    v-model="search.transform"
                                    label="use Pseudo-Greek"
                                    class="ma-0 pa-0"
                                />
                                <sup
                                    class="body-1 ml-1"
                                    style="cursor: pointer;"
                                    v-html="'&nbsp;*'"
                                    @click="showLG = true"
                                />
                            </div>

                            <!-- Regex -->
                            <div class="d-flex">
                                <v-checkbox
                                    v-model="search.regex"
                                    label="use REGEX"
                                    class="ma-0 pa-0"
                                />
                                <sup class="body-1 ml-1">
                                    <a href="https://en.wikipedia.org/wiki/Regular_expression#Syntax" target="_blank">&nbsp;*</a>
                                </sup>
                            </div>
                        </div>
                    </div>
                </v-card>
            </v-col>

            <!-- Legend Variants -->
            <v-col cols=4>
                <v-card
                    tile
                    class="legend-tile sheet_bg"
                    style="margin: 16px 8px 16px 8px;"
                    :style="'outline: 1px solid ' + ($vuetify.theme.dark ? 'black' : 'grey')"
                >
                    <div
                        class="text-center font-bold body-1 pa-3"
                        v-text="
                            loading.list ?
                            'Please wait ...' :
                            ((list.length > 1000 ? 'More than 1000' : list.length)  + ' records' + (searchDisplay ? (' for \'' + searchDisplay + '\'') : ''))
                        "
                    />

                    <v-progress-linear :indeterminate="loading.list" height="1" />

                    <v-virtual-scroll
                        :items="list"
                        :item-height="60"
                        style="flex: 1 1 auto;"
                    >
                        <template v-slot:default="{ item, index }">
                            <div
                                class="pa-3 caption"
                                :class="item.id === listSelect ? 'grey_prim' : ''"
                                style="height: 60px; cursor: pointer;"
                                @click="selectItem(item)"
                            >
                                <span
                                    class="font-thin body-1"
                                    v-html="item.string ? item.string : '<i>EMPTY</i>'"
                                /><br />
                                <span v-html="item.name ? item.name : '--'" />
                                <span v-text="'(' + item.number + ')'" />,
                                <span v-html="item.date" />
                            </div>
                        </template>
                    </v-virtual-scroll>
                </v-card>
            </v-col>

            <!-- Preview -->
            <v-col cols=4>
                <v-card
                    tile
                    class="legend-tile sheet_bg"
                    style="margin: 16px 16px 16px 8px; overflow-y: auto"
                    :style="'outline: 1px solid ' + ($vuetify.theme.dark ? 'black' : 'grey')"
                >
                    <template v-if="listSelect">
                        <div
                            class="text-center font-bold body-1 pa-3"
                            v-text="loading.details ? 'Please wait ...' : (list.find((item) => item.id === listSelect).name + ', ' + details.length + ' occurances')"
                        />

                        <v-progress-linear :indeterminate="loading.details" height="1" />

                        <v-virtual-scroll
                            :items="details"
                            :item-height="60"
                            style="flex: 1 1 auto;"
                        >
                            <template v-slot:default="{ item, index }">
                                <div
                                    class="pa-3 caption"
                                    style="height: 60px; cursor: pointer;"
                                >
                                    <span class="font-thin body-1" v-text="item.id" /><br />
                                    <span v-html="item.place + ', ' + printDate(item).replaceAll(' ', '&nbsp;')" />
                                </div>
                            </template>
                        </v-virtual-scroll>
                    </template>
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
            component:          'LGPN',
            entity:             'persons_index',

            search: {
                string: null,
                from: null,
                to: null,
                combination: false,
                transform: false,
                regex: false
            },

            showKB: true,
            loading: {
                list: true,
                details: false
            },
            listRaw: [],
            listSelect: null,
            details: [],

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
            if (!this.search.string) return this.listRaw

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
        }
    },

    created () {
        this.getlist()
    },

    methods: {

        async getlist () {
            const self = this
            self.loading.list = true
            self.listRaw = []

            console.log('AXIOS', '/dbi/persons_index')
            await axios.get('/dbi/persons_index')
            .then((response) => {
                const dbi = response.data?.contents
                if (dbi[0]) self.listRaw = dbi
            })
            .catch((error) => {
                self.$root.AXIOS_ERROR_HANDLER(error)
            })

            self.loading.list = false
        },

        async selectItem (item) {
            const self = this
            self.loading.details = true
            self.listSelect = item.id
            self.details = []

            console.log('AXIOS', item.link)
            await axios.get(item.link)
            .then((response) => {
                const dbi = JSON.parse(response.data?.slice(5, -3))
                if (dbi?.persons?.[0]) self.details = dbi.persons
            })
            .catch((error) => {
                self.$root.AXIOS_ERROR_HANDLER(error)
            })

            self.loading.details = false
        },

        keyboardInput (emit) {
            this.search.string = emit
            this.$refs['searchString'].$refs.input.focus();
        },

        printDate (item) {
            let date = [
                parseInt(item.notBefore),
                parseInt(item.notAfter)
            ]

            date = date.map((year) => {
                if (year == 0 || year > 998 || year < -998) return '--'
                else if (year < 0) return (year * -1) + '&nbsp;BC'
                else return year + '&nbsp;AD'
            })

            return date.join(' – ')
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
