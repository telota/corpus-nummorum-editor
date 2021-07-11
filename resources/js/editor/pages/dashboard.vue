<template>
<div>
    <component-toolbar>
        <template v-slot:toolbar>
                <div
                    class="pa-4 d-flex justify-center title"
                    v-text="labels['welcome' + (data_user.last_login ? '_back' : '')] + ', ' + data_user.fullname + '!'"
                ></div>
        </template>
    </component-toolbar>

    <component-content>
        <template v-slot:content>
            <v-row class="pa-0 ma-0">

                <!-- Entities -->
                <v-col v-for="resource in ['types', 'coins']" :key="resource" cols="12" sm="6" md="4">
                    <!-- Quicksearch -->
                    <v-card tile class="appbar mb-5">
                        <v-card-title class="mb-1">
                            <div v-text="labels[resource]"></div>
                            <v-spacer></v-spacer>
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
                        </v-card-title>

                        <v-card-text class="d-flex component-wrap">
                            <v-text-field dense filled outlined clearable
                                v-model="search[resource]"
                                :label="'cn ' + resource.slice(0, -1) + ' ID quick search'"
                                :rules="[$handlers.rules.numeric]"
                                :prepend-icon="resource === 'coins' ? 'copyright' : 'blur_circular'"
                                class="mr-1"
                            ></v-text-field>
                            <div v-if="parseInt(search[resource])" class="d-flex component-wrap">
                                <a v-for="(icon, link) in search_buttons" :key="link" :href="'/editor#/' + resource + '/' + link + '/' + search[resource]" class="ml-1">
                                    <v-btn icon>
                                        <v-icon v-text="icon"></v-icon>
                                    </v-btn>
                                </a>
                            </div>
                            <div v-else class="d-flex component-wrap">
                                <v-btn v-for="(icon, link) in search_buttons" :key="link" icon disabled class="ml-1">
                                    <v-icon v-text="icon"></v-icon>
                                </v-btn>
                            </div>
                        </v-card-text>
                    </v-card>

                    <!-- Activities -->
                    <v-card tile>
                        <v-card-title class="mb-1" v-text="labels[resource + '_latest_unpublished']"></v-card-title>

                        <v-expand-transition>
                            <v-card-text v-if="data.activities">
                                <div v-if="data.activities['latest_' + resource].length < 1" v-text="'No unpublished ' + resource + ' edited by you.'"></div>
                                <div v-else>
                                    <div v-for="(v) in data.activities['latest_' + resource]" :key="v.id" class="mb-2 d-flex component-wrap align-start">
                                        <div
                                            class="caption mr-5 mt-1"
                                            style="white-space: nowrap"
                                            v-text="$editor_format.date(l, (v.updated_at ? v.updated_at : v.created_at), true)"
                                        ></div>
                                        <div>
                                            <a
                                                :href="'/editor#/' + resource + '/edit/' + v.id"
                                                class="mr-2 body-1"
                                                style="white-space: nowrap"
                                                v-text="'cn ' + resource.slice(0, -1) + ' ' + v.id"
                                            ></a>
                                            <span
                                                v-if="v.mint"
                                                class="caption"
                                                v-html="'(' + v.mint.replace('_', ' ') + ')'"
                                            ></span>
                                        </div>
                                    </div>
                                </div>
                            </v-card-text>
                        </v-expand-transition>
                    </v-card>
                </v-col>

                <!-- Statistics -->
                <v-col cols="12" sm="6" md="4">
                    <v-card tile class="mb-5">
                        <v-card-title class="ma-0 component-wrap d-flex align-end">
                            <div v-text="labels['statistics_personal']"></div>
                            <v-spacer></v-spacer>
                            <div class="caption" v-html="'(up-to-date)'"></div>
                        </v-card-title>

                        <v-expand-transition>
                            <v-card-text v-if="data.statistics">
                                <v-simple-table dense>
                                    <template v-for="(e) in ['types', 'coins']">
                                        <tr :key="'all' + e">
                                            <td :class="td_key" v-text="labels[e]"></td>
                                            <td :class="td_val" v-text="num(data.statistics.user[e + '_all'])" :title="$root.label('all')"></td>
                                            <td :class="td_val" v-text="num(data.statistics.user[e + '_pub'])" :title="$root.label('published')"></td>
                                            <td :class="td_val" v-html="percentage(data.statistics.user[e + '_pub'], data.statistics.user[e + '_all'])"></td>
                                        </tr>
                                        <tr :key="'increase' + e">
                                            <td v-text="labels['increase'] + ' (30d)'"></td>
                                            <td :class="td_val" v-text="data.statistics.user[e + '_trend']"></td>
                                        </tr>
                                    </template>
                                </v-simple-table>
                            </v-card-text>
                        </v-expand-transition>
                    </v-card>

                    <v-card tile>
                        <v-card-title class="ma-0 component-wrap d-flex align-end">
                            <div v-text="labels['statistics_general']"></div>
                            <v-spacer></v-spacer>
                            <div class="caption" v-html="'(' + $editor_format.date(l, data.statistics_date) + ')'"></div>
                        </v-card-title>

                        <v-expand-transition>
                            <v-card-text v-if="data.statistics">
                                <v-simple-table dense>
                                    <tr>
                                        <td colspan="4" v-text="labels['all']" class="font-weight-bold pb-1 body-1"></td>
                                    </tr>
                                    <tr>
                                        <td :class="td_key" v-text="labels['mints']"></td>
                                        <td :class="td_val" v-text="num(data.statistics.all.mints)"></td>
                                    </tr>

                                    <template v-for="(e) in ['types', 'coins']">
                                        <tr :key="'all' + e">
                                            <td :class="td_key" v-text="labels[e]"></td>
                                            <td :class="td_val" v-text="num(data.statistics.all[e + '_all'])" :title="$root.label('all')"></td>
                                            <td :class="td_val" v-text="num(data.statistics.all[e + '_pub'])" :title="$root.label('published')"></td>
                                            <td :class="td_val" v-html="percentage(data.statistics.all[e + '_pub'], data.statistics.all[e + '_all'])"></td>
                                        </tr>
                                        <tr :key="'increase' + e">
                                            <td v-text="labels['increase'] + ' (30d)'"></td>
                                            <td :class="td_val" v-text="num(data.statistics.all[e + '_trend'])"></td>
                                        </tr>
                                    </template>

                                    <tr>
                                        <td colspan=4><v-divider class="mt-4 mb-n2"></v-divider></td>
                                    </tr>

                                    <template v-for="(data, region) in data.statistics.regions">
                                        <tr :key="region">
                                            <td colspan="4" v-text="labels[region]" class="font-weight-bold pt-5 pb-1 body-1"></td>
                                        </tr>
                                        <tr :key="region + 'mint'">
                                            <td :class="td_key" v-text="labels['mints']"></td>
                                            <td :class="td_val" v-text="num(data.mints)"></td>
                                        </tr>
                                        <tr v-for="(e) in ['types', 'coins']" :key="region + e">
                                            <td :class="td_key" v-text="labels[e]"></td>
                                            <td :class="td_val" v-text="num(data[e + '_all'])" :title="$root.label('all')"></td>
                                            <td :class="td_val" v-text="num(data[e + '_pub'])" :title="$root.label('published')"></td>
                                            <td :class="td_val" v-html="percentage(data[e + '_pub'], data[e + '_all'])"></td>
                                        </tr>
                                    </template>

                                    <tr>
                                        <td colspan=4><v-divider class="mt-5 mb-2"></v-divider></td>
                                    </tr>

                                    <tr>
                                        <td colspan="4" v-text="$root.label('assets')" class="font-weight-bold pb-1 body-1"></td>
                                    </tr>

                                    <tr v-for="(key) in ['designs', 'legends', 'dies', 'monograms', 'persons']" :key="'assets' + key">
                                        <td :class="td_key" v-text="$root.label(key)"></td>
                                        <td :class="td_val" v-text="num(data.statistics[key])"></td>
                                    </tr>
                                </v-simple-table>
                            </v-card-text>
                        </v-expand-transition>
                    </v-card>
                </v-col>

                <!-- About you
                <v-col cols="12" sm="6" md="4">


                    <v-card tile>
                        <v-card-title>
                            Account Information
                        </v-card-title>

                        <v-expand-transition>
                            <v-card-text v-if="data_user">
                                <v-simple-table dense>
                                    <tr>
                                        <td>User ID</td>
                                        <td v-text="data_user.id"></td>
                                    </tr><tr>
                                        <td>Username</td>
                                        <td v-text="data_user.name"></td>
                                    </tr><tr>
                                        <td>Level</td>
                                        <td>
                                            {{ data_user.role }}
                                            <v-tooltip bottom>
                                                <template v-slot:activator="{ on }">
                                                    <v-icon v-on="on" class="ml-2">help_outline</v-icon>
                                                </template>
                                                <span v-text="labels[data_user.role.toLowerCase() + '_description']"></span>
                                            </v-tooltip>
                                        </td>
                                    </tr><tr>
                                        <td>&ensp;</td>
                                    </tr><tr>
                                        <td>Name</td>
                                        <td v-text="data_user.fullname"></td>
                                    </tr><tr>
                                        <td>Email</td>
                                        <td>
                                            {{ data_user.email }}
                                            <v-tooltip v-if="!data.verified" bottom>
                                                <template v-slot:activator="{ on }">
                                                    <v-btn v-on="on" @click="" class="red--text ml-2" icon small><v-icon>error_outline</v-icon></v-btn>
                                                </template>
                                                <span>Click to verify your email adress.</span>
                                            </v-tooltip>
                                        </td>
                                    </tr>
                                </v-simple-table>
                            </v-card-text>
                        </v-expand-transition>
                    </v-card>
                </v-col>-->

            </v-row>
        </template>
    </component-content>
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
            td_val:     'text-right'
        }
    },

    created () {
        this.getData();
    },

    mounted () {
        this.$store.commit('setBreadcrumbs',[{ label:'Dashboard',name:'' }])
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },
        data_user () { return this.$root.user }
    },

    methods: {
        async getData () {
            this.loading    = true
            this.data       = await this.$root.DBI_SELECT_GET('dashboard')
            this.loading    = false
        },

        num (number) {
            return this.$handlers.format.number(this.l, number)
        },

        percentage (part, sum) {
            return part && sum ? '~&nbsp;' + Math.ceil(part / sum * 100) + '&nbsp;%' : ''
        }
    }
}
</script>
