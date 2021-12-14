<template>
<div>
    <!-- Toolbar -->
    <component-toolbar>
        <a href="/editor#/types/edit">
            <v-btn tile depressed :height="50" class="header_bg">
                <v-icon v-text="'add_circle_outline'" />
                <div v-text="'Add Type'" class="ml-2" />
            </v-btn>
        </a>
        <a href="/editor#/coins/edit">
            <v-btn tile depressed :height="50" class="header_bg">
                <v-icon v-text="'add_circle'" />
                <div v-text="'Add coin'" class="ml-2" />
            </v-btn>
        </a>
        <a href="/editor#/storage">
            <v-btn tile depressed :height="50" class="header_bg">
                <v-icon v-text="'folder'" />
                <div v-text="'File Manager'" class="ml-2" />
            </v-btn>
        </a>
        <a href="/wiki" target="_blank">
            <v-btn tile depressed :height="50" class="header_bg">
                <v-icon v-text="'help_outline'" />
                <div v-text="'Wiki'" class="ml-2" />
            </v-btn>
        </a>
        <a href="https://www.corpus-nummorum.eu/" target="_blank">
            <v-btn tile depressed :height="50" class="header_bg">
                <v-icon v-text="'public'" />
                <div v-text="'CN Website'" class="ml-2" />
            </v-btn>
        </a>
    </component-toolbar>

    <!-- Content -->
    <div class="component-content" style="overflow-y: hidden">
        <v-row class="pa-0 ma-0" no-gutters>

            <!-- Entities -->
            <v-col
                v-for="(resource, i) in ['types', 'coins']"
                :key="resource"
                cols=6
                sm=4
            >
                <div
                    class="dashboard-tile"
                    :style="'margin: 16px 8px 16px ' + (i === 0 ? 16 : 8) + 'px;'"
                >
                    <v-card
                        tile
                        class="grey_sec mb-4"
                        :style="'outline: 1px solid ' + ($vuetify.theme.dark ? 'black' : 'grey')"
                    >
                        <div class="d-flex font-bold body-1 pa-3">
                            <div v-text="labels[resource]" />

                            <v-spacer />

                            <a :href="'/editor#/' + resource + '/edit'" class="ml-1 mr-1">
                                <v-btn icon>
                                    <v-icon>add</v-icon>
                                </v-btn>
                            </a>
                            <a :href="'/editor#/' + resource + '/search'">
                                <v-btn icon>
                                    <v-icon>search</v-icon>
                                </v-btn>
                            </a>
                        </div>

                        <div class="d-flex pl-3 pr-3">
                            <coins-types-selector
                                :entity="resource"
                                :selected="search[resource]"
                                no-confirm
                                class="mr-2"
                                @select="(emit) => { search[resource] = emit }"
                                @enter="(emit) => { ctSelect(resource, search[resource], emit) }"
                            />

                            <div
                                v-if="parseInt(search[resource])"
                                class="d-flex"
                            >
                                <a v-for="(icon, link) in search_buttons" :key="link" :href="'/editor#/' + resource + '/' + link + '/' + search[resource]" class="ml-1">
                                    <v-btn icon>
                                        <v-icon v-text="icon" />
                                    </v-btn>
                                </a>
                            </div>

                            <div v-else class="d-flex">
                                <v-btn v-for="(icon, link) in search_buttons" :key="link" icon disabled class="ml-1">
                                    <v-icon v-text="icon" />
                                </v-btn>
                            </div>
                        </div>
                    </v-card>

                    <v-card
                        tile
                        class="sheet_bg"
                        :style="'outline: 1px solid ' + ($vuetify.theme.dark ? 'black' : 'grey')"
                    >
                        <div
                            class="pa-3 body-1 text-center text-truncate"
                            v-text="labels[resource + '_latest_unpublished']"
                        />
                    </v-card>

                    <v-card
                        tile
                        class="sheet_bg"
                        style="flex: 1 1 auto; overflow-y: auto;"
                        :style="'outline: 1px solid ' + ($vuetify.theme.dark ? 'black' : 'grey')"
                    >
                        <div
                            v-if="!data.activities || data.activities['latest_' + resource].length < 1"
                            v-text="'No unpublished ' + resource + ' edited by you.'"
                        />
                        <div v-else>
                            <div
                                v-for="(v) in data.activities['latest_' + resource]"
                                :key="v.id"
                                class="caption pa-2"
                            >
                                <span
                                    style="white-space: nowrap"
                                    class="mr-1"
                                    v-text="$editor_format.date(l, v.date, true)"
                                />
                                <a
                                    :href="'/editor#/' + resource + '/edit/' + v.id"
                                    style="white-space: nowrap"
                                    v-text="'cn ' + resource.slice(0, -1) + ' ' + v.id"
                                />
                                <span
                                    v-if="v.mint"
                                    class="ml-1"
                                    v-html="'(' + v.mint + ')'"
                                />
                            </div>
                        </div>
                    </v-card>
                </div>
            </v-col>

            <!-- Statistics -->
            <v-col v-if="$vuetify.breakpoint.smAndUp" cols=4>
                <v-card
                    tile
                    class="dashboard-tile sheet_bg mb-4"
                    style="margin: 16px 16px 16px 8px;"
                    :style="'outline: 1px solid ' + ($vuetify.theme.dark ? 'black' : 'grey')"
                >
                    <div class="pa-3" style="flex: 1 1 auto; overflow-y: auto">
                        <div
                            class="pt-1 body-1 text-center text-truncate"
                            v-text="labels['statistics_personal']"
                        />

                        <v-simple-table v-if="data.statistics" class="mt-n1" dense>
                            <tr>
                                <td colspan=4><v-divider class="mt-5 mb-2" /></td>
                            </tr>
                            <template v-for="(e) in ['types', 'coins']">
                                <tr :key="'all' + e">
                                    <td :class="td_key" v-text="labels[e]" />
                                    <td :class="td_val" v-text="num(data.statistics.user[e + '_all'])" :title="$root.label('all')" />
                                    <td :class="td_val" v-text="num(data.statistics.user[e + '_pub'])" :title="$root.label('published')" />
                                    <td :class="td_val" v-html="percentage(data.statistics.user[e + '_pub'], data.statistics.user[e + '_all'])" />
                                </tr>
                                <tr :key="'increase' + e">
                                    <td v-text="labels['increase'] + ' (30d)'" />
                                    <td :class="td_val" v-text="data.statistics.user[e + '_trend']" />
                                </tr>
                            </template>
                            <tr>
                                <td colspan=4><v-divider class="mt-5 mb-2" /></td>
                            </tr>
                        </v-simple-table>

                        <div class="pt-3 body-1">
                            <div class="text-center text-truncate" v-text="labels['statistics_general']" />
                            <div class="text-center caption" v-html="'(' + $editor_format.date(l, data.statistics_date) + ')'" />
                        </div>

                        <v-simple-table v-if="data.statistics" dense>
                            <tr>
                                <td colspan=4><v-divider class="mt-5 mb-2" /></td>
                            </tr>
                            <tr>
                                <td colspan="4" v-text="labels['all']" class="font-weight-bold pb-1 body-1" />
                            </tr>
                            <tr>
                                <td :class="td_key" v-text="labels['mints']" />
                                <td :class="td_val" v-text="num(data.statistics.all.mints)" />
                            </tr>

                            <template v-for="(e) in ['types', 'coins']">
                                <tr :key="'all' + e">
                                    <td :class="td_key" v-text="labels[e]" />
                                    <td :class="td_val" v-text="num(data.statistics.all[e + '_all'])" :title="$root.label('all')" />
                                    <td :class="td_val" v-text="num(data.statistics.all[e + '_pub'])" :title="$root.label('published')" />
                                    <td :class="td_val" v-html="percentage(data.statistics.all[e + '_pub'], data.statistics.all[e + '_all'])" />
                                </tr>
                                <tr :key="'increase' + e">
                                    <td v-text="labels['increase'] + ' (30d)'" />
                                    <td :class="td_val" v-text="num(data.statistics.all[e + '_trend'])" />
                                </tr>
                            </template>

                            <tr>
                                <td colspan=4><v-divider class="mt-4 mb-n2" /></td>
                            </tr>

                            <template v-for="(data, region) in data.statistics.regions">
                                <tr :key="region">
                                    <td colspan="4" v-text="labels[region]" class="font-weight-bold pt-5 pb-1 body-1" />
                                </tr>
                                <tr :key="region + 'mint'">
                                    <td :class="td_key" v-text="labels['mints']" />
                                    <td :class="td_val" v-text="num(data.mints)" />
                                </tr>
                                <tr v-for="(e) in ['types', 'coins']" :key="region + e">
                                    <td :class="td_key" v-text="labels[e]" />
                                    <td :class="td_val" v-text="num(data[e + '_all'])" :title="$root.label('all')" />
                                    <td :class="td_val" v-text="num(data[e + '_pub'])" :title="$root.label('published')" />
                                    <td :class="td_val" v-html="percentage(data[e + '_pub'], data[e + '_all'])" />
                                </tr>
                            </template>

                            <tr>
                                <td colspan=4><v-divider class="mt-5 mb-2" /></td>
                            </tr>

                            <tr>
                                <td colspan="4" v-text="$root.label('assets')" class="font-weight-bold pb-1 body-1" />
                            </tr>

                            <tr v-for="(key) in ['designs', 'legends', 'dies', 'monograms', 'persons']" :key="'assets' + key">
                                <td :class="td_key" v-text="$root.label(key)" />
                                <td :class="td_val" v-text="num(data.statistics[key])" />
                            </tr>
                        </v-simple-table>
                    </div>
                </v-card>
            </v-col>

        </v-row>
    </div>
</div>
</template>


<script>

export default {
    data () {
        return {
            loading:    false,
            data:       {},

            search:     {
                coins: null,
                types: null
            },
            search_buttons: {
                show: 'preview',
                edit: 'edit'
            },
            td_key:     'caption font-weight-thin text-uppercase',
            td_val:     'text-right',
        }
    },

    computed: {
        // Localization
        l () {
            return this.$root.language
        },
        labels () {
            return this.$root.labels
        },
        data_user () {
            return this.$root.user
        }
    },

    created () {
        this.$root.setTitle('Dashboard')
        this.getData()
    },

    methods: {
        async getData () {
            this.loading = true
            this.data = await this.$root.DBI_SELECT_GET('dashboard')
            this.loading = false
        },

        num (number) {
            return this.$handlers.format.number(this.l, number)
        },

        percentage (part, sum) {
            return part && sum ? '~&nbsp;' + Math.ceil(part / sum * 100) + '&nbsp;%' : ''
        },

        ctSelect (entity, id, shift) {
            if (id) this.$router.push(['', entity, shift ? 'edit' : 'show', id].join('/'))
        }
    }
}
</script>

<style lang="scss" scoped>
    .dashboard-tile {
        height: calc(100vh - 122px);
        display: flex;
        flex-flow: column;
    }
</style>
