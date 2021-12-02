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

            <!-- Persons Index -->
            <v-col cols=4>
                <v-card
                    tile
                    class="index-tile sheet_bg"
                    style="margin: 16px 8px 16px 16px;"
                    :style="'outline: 1px solid ' + ($vuetify.theme.dark ? 'black' : 'grey')"
                >
                    <div style="flex: 0 1 auto;">
                        <div class="pa-3">
                            <v-text-field outlined filled clearable dense
                                ref="searchString"
                                v-model="query.string"
                                :label="$root.label('String')"
                                append-icon="keyboard"
                                class="mb-n4"
                                v-on:click:append="showKB = !showKB"
                            />
                            <v-expand-transition>
                                <div v-if="showKB" class="d-flex justify-center">
                                    <keyboard
                                        :string="query.string"
                                        layout="el_uc"
                                        small
                                        hide_options
                                        v-on:input="keyboardInput"
                                    />
                                </div>
                            </v-expand-transition>
                        </div>

                        <div class="pa-3">
                            <!-- Pseudo-Greek -->
                            <div class="d-flex mb-1">
                                <v-checkbox
                                    v-model="query.transform"
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

                            <div class="d-flex justify-space-between">
                                <div style="max-width: 250px;">
                                    <v-select dense outlined filled
                                        v-model="query.mode"
                                        :items="modes"
                                        label="Mode"
                                        :menu-props="{ offsetY: true }"
                                    />
                                </div>
                                <div v-if="query.mode === 'regex'" class="ml-3 mt-2">
                                    <a
                                        v-if="query.mode === 'regex'"
                                        href="https://en.wikipedia.org/wiki/Regular_expression#Syntax"
                                        target="_blank"
                                        v-text="'HELP'"
                                    />
                                </div>
                            </div>

                            <v-row>
                                <v-col v-for="(key) in ['from', 'to']" :key="key" cols=6>
                                    <v-text-field outlined filled clearable dense
                                        v-model="query[key]"
                                        :label="$root.label('date_' + key)"
                                    />
                                </v-col>
                            </v-row>
                        </div>
                    </div>
                </v-card>
            </v-col>

            <!-- Names -->
            <v-col cols=4>
                <v-card
                    tile
                    class="index-tile sheet_bg"
                    style="margin: 16px 8px 16px 8px;"
                    :style="'outline: 1px solid ' + ($vuetify.theme.dark ? 'black' : 'grey')"
                >
                    <div
                        class="text-center font-bold body-1 pa-3"
                        v-text="
                            pending || loading.index ?
                            'Please wait ...' :
                            ((count > 1000 ? 'More than 1000' : count)  + ' records')
                        "
                    />

                    <v-progress-linear :indeterminate="pending || loading.index" height="1" />

                    <v-virtual-scroll
                        :items="index"
                        :item-height="60"
                        style="flex: 1 1 auto;"
                    >
                        <template v-slot:default="{ item, index }">
                            <div
                                class="pa-3 caption"
                                :class="item.id === Selected ? 'grey_prim' : ''"
                                style="height: 60px; cursor: pointer;"
                                @click="selectItem(item)"
                            >
                                <span
                                    class="font-thin body-1"
                                    v-html="item.name + ' (' + item.number + ')'"
                                /><br />
                                <span v-html="item.variants" />,
                                <span v-html="printDate(item).replaceAll(' ', '&nbsp;')" />
                            </div>
                        </template>
                    </v-virtual-scroll>
                </v-card>
            </v-col>

            <!-- Preview -->
            <v-col cols=4>
                <v-card
                    tile
                    class="index-tile sheet_bg"
                    style="margin: 16px 16px 16px 8px; overflow-y: auto"
                    :style="'outline: 1px solid ' + ($vuetify.theme.dark ? 'black' : 'grey')"
                >
                    <template v-if="Selected">
                        <div class="text-center font-bold body-1 pa-3">
                            <span v-if="loading.details"  v-text="'Please wait ...'" />
                            <template v-else>
                                <div class="d-flex">
                                    <div class="text-truncate" v-text="detailsLabel" />
                                    <div class="ml-1" v-text="'(' + details.length + ')'" />
                                    <v-spacer />
                                    <div class="ml-3">
                                        <a
                                            :href="'http://clas-lgpn2.classics.ox.ac.uk/cgi-bin/lgpn_search.cgi?namenoaccents=' + detailsLabel"
                                            target="_blank"
                                            class="font-weight-bold"
                                            v-text="'LGPN'"
                                        />
                                    </div>
                                </div>
                            </template>
                        </div>

                        <v-progress-linear :indeterminate="loading.details" height="1" />

                        <v-virtual-scroll
                            :items="details"
                            :item-height="60"
                            style="flex: 1 1 auto;"
                        >
                            <template v-slot:default="{ item, index }">
                                <a :href="'http://clas-lgpn2.classics.ox.ac.uk/cgi-bin/lgpn_search.cgi?id=' + item.id" target="_blank">
                                    <div
                                        class="pa-3 caption invert--text"
                                        style="height: 60px; cursor: pointer;"
                                    >
                                        <span class="font-thin body-1" v-html="'LGPN&nbsp;' + item.id.toUpperCase()" /><br />
                                        <span v-html="item.place + ', ' + printDate(item).replaceAll(' ', '&nbsp;')" />
                                    </div>
                                </a>
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

            query: {
                string: null,
                from: null,
                to: null,
                transform: false,
                mode: null
            },
            delayedQuery: '',

            modes: [
                { value: null, text: 'Normal Search' },
                { value: 'regex', text: 'REGEX Search' },
                { value: 'monogram', text: 'Monogram Combination' }
            ],

            showKB: true,
            showMonogram: true,

            loading: {
                index: true,
                details: false
            },
            //listRaw: [],
            index: [],
            Selected: null,
            details: [],
            detailsLabel: null,
            count: 0,

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
        queryString () {
            const params = []
            if (this.query.string) {
                let string = this.query.string
                if (this.query.transform) for (const [key, value] of Object.entries(this.latinGreek)) string = string.replaceAll(key, value)
                params.push('string=' + encodeURI(string))
                if (['regex', 'monogram'].includes(this.query.mode)) params.push('mode=' + this.query.mode)
            }

            if (this.query.from) params.push('from=' + this.query.from)
            if (this.query.to) params.push('to=' + this.query.to)

            return params[0] ? ('?' + params.join('&')) : ''
        },

        pending () {
            return this.delayedQuery !== this.queryString
        }
    },

    watch: {
        delayedQuery () {
            this.getIndex()
        }
    },

    created () {
        this.getIndex()
        setInterval(() => {
            if (this.delayedQuery !== this.queryString) this.delayedQuery = this.queryString
        }, 750)
    },

    methods: {

        async getIndex () {
            const self = this

            this.loading.index = true
            this.index = []
            this.count = 0

    	    const src = '/dbi/persons_index' + this.delayedQuery
            console.log('AXIOS', src)
            const dbi = await axios.get(src).catch((error) => { self.$root.AXIOS_ERROR_HANDLER(error) })

            if (dbi.data?.pagination.count > 0) {
                this.index = dbi.data.contents
                this.count = dbi.data.pagination.count
            }

            self.loading.index = false
        },

        async selectItem (item) {
            const self = this

            this.loading.details = true
            this.detailsLabel = null
            this.Selected = item.id
            this.details = []

            console.log('AXIOS', item.link)
            let dbi = await axios.get(item.link).catch((error) => { self.$root.AXIOS_ERROR_HANDLER(error) })
            dbi = JSON.parse(dbi.data?.slice(5, -3))
            if (dbi?.persons?.[0]) {
                this.detailsLabel = this.index.find((item) => item.id === this.Selected).name ?? null
                this.details = dbi.persons
            }

            this.loading.details = false
        },

        keyboardInput (emit) {
            this.query.string = emit
            this.$refs['searchString'].$refs.input.focus();
        },

        printDate (item) {
            let date = [
                item.notBefore ? parseInt(item.notBefore) : 0,
                item.notAfter ? parseInt(item.notAfter) : 0,
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
    .index-tile {
        height: calc(100vh - 122px);
        display: flex;
        flex-flow: column;
    }
</style>
