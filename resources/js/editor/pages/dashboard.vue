<template>
<div>
    <!-- Toolbar -->
    <component-toolbar>
        <template v-slot:toolbar>
            <adv-btn
                tooltip="Manage Dashboard Favorites"
                icon="star"
                colorHover="header_hover"
                @click="manageFavorites(true)"
            />
            <a v-for="(item, i) in favorites" :key="'fav' + i" :href="item.link" :target="item.link.startsWith('http') ? '_blank' : ''">
                <v-btn tile depressed class="header_bg" v-html="item.text" :height="50" />
            </a>
        </template>
    </component-toolbar>

    <!-- Content -->
    <component-content>
        <template v-slot:content>
            <v-row class="pa-0 ma-0 mt-2">

                <!-- Entities -->
                <v-col v-for="resource in ['types', 'coins']" :key="resource" cols="12" sm="6" md="4">
                    <!-- Quicksearch -->
                    <v-card tile class="appbar mb-5" height="160">
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

                        <v-card-text class="d-flex">
                            <v-text-field dense filled outlined clearable
                                v-model="search[resource]"
                                :label="'cn ' + resource.slice(0, -1) + ' ID quick search'"
                                hint="Number only"
                                :prepend-icon="resource === 'coins' ? 'copyright' : 'blur_circular'"
                                class="mr-1"
                            ></v-text-field>
                            <div v-if="parseInt(search[resource])" class="d-flex">
                                <a v-for="(icon, link) in search_buttons" :key="link" :href="'/editor#/' + resource + '/' + link + '/' + search[resource]" class="ml-1">
                                    <v-btn icon>
                                        <v-icon v-text="icon"></v-icon>
                                    </v-btn>
                                </a>
                            </div>
                            <div v-else class="d-flex">
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
                                    <div v-for="(v) in data.activities['latest_' + resource]" :key="v.id" class="mb-2 d-flex   align-start">
                                        <div
                                            class="caption mr-5 mt-1"
                                            style="white-space: nowrap"
                                            v-text="$editor_format.date(l, v.date, true)"
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
                                                v-html="'(' + v.mint.replaceAll('_', ' ') + ')'"
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
                    <v-card tile class="mb-5" height="160">
                        <v-card-title class="ma-0   d-flex align-end">
                            <div v-text="labels['statistics_personal']"></div>
                            <v-spacer></v-spacer>
                            <div class="caption" v-html="'(up-to-date)'"></div>
                        </v-card-title>

                        <v-expand-transition>
                            <v-card-text v-if="data.statistics" class="mt-n1">
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
                        <v-card-title class="ma-0   d-flex align-end">
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

    <!-- Manage Favorites -->
    <v-dialog
        tile
        persistent
        scrollable
        v-model="showFavorites"
        width="500px"
    >
        <v-card tile>
            <dialogbartop
                icon="star"
                text="Dashboard Favorites"
                :fullscreen="null"
                v-on:close="manageFavorites(false)"
            ></dialogbartop>

            <div class="pa-3 caption text-center">
                Click on a link in the left column to add it to the active favorites.<br/>Use the arrows to manage the order of your active favorites.
            </div>

            <v-card-text>
                <v-row>
                    <!-- Available -->
                    <v-col cols=6>
                        <div class="font-weight-bold pb-1 text-center" v-text="'Available'" />
                        <div v-for="(fav, f) in availableFavs" :key="'favAv' + f" class="d-flex align-center mt-1" style="cursor: pointer" @click="selectedFavs.push(fav)">
                            <div v-text="fav.text" />
                            <v-spacer />
                            <v-icon small v-text="'arrow_forward'" class="pa-1 mr-5" />
                        </div>
                    </v-col>
                    <!-- Active -->
                    <v-col cols=6>
                        <div class="font-weight-bold pb-1 text-center" v-text="'Active'" />
                        <div v-for="(fav, f) in selectedFavs" :key="'favSe' + f"  class="d-flex align-center mt-1">
                            <div v-text="fav.text" />
                            <v-spacer />
                            <v-icon small v-text="'arrow_upward'" :disabled="f < 1" class="pa-1" @click="shiftFav(f, -1)" />
                            <v-icon small v-text="'arrow_downward'" :disabled="f > (selectedFavs.length - 2)" class="pa-1" @click="shiftFav(f, 1)" />
                            <v-icon small v-text="'clear'" class="pa-1" @click="shiftFav(f, 0)" />
                        </div>
                    </v-col>
                </v-row>
            </v-card-text>

            <div class="d-flex justify-center mb-2" >
                <v-btn text v-text="'cancel'" @click="manageFavorites(false)" class="mr-1" />
                <v-btn text v-text="'save'" @click="saveFavorites()" class="ml-1" />
            </div>
        </v-card>
    </v-dialog>
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

            favorites:      [
                { link: '/editor#/types/edit', text: 'Add Type'},
                { link: '/editor#/coins/edit', text: 'Add Coin'}
            ],
            selectedFavs: [],
            showFavorites: false,
            favList: [
                { link: '/editor#/types/edit', text: 'Add Type'},
                { link: '/editor#/coins/edit', text: 'Add Coin'},
                { link: 'https://www.corpus-nummorum.eu/', text: 'CN Website'},
                { link: '/editor#/designs', text: 'Designs'},
                { link: 'https://ikmk.smb.museum/home?lang=de', text: 'IKMK'},
                { link: '/editor#/storage/coin-images', text: 'Image Upload'},
                { link: '/editor#/legends-index', text: 'Legends'},
                { link: '/editor#/monograms', text: 'Monograms'},
                { link: 'http://nomisma.org/', text: 'Nomisma'},
                { link: '/editor#/coins/search', text: 'Search Coins'},
                { link: '/editor#/types/search', text: 'Search Types'},
                { link: 'https://www.zotero.org/groups/163139/thrakien/library', text: 'Zotero'}
            ]

        }
    },

    created () {
        this.getData();
    },

    mounted () {
        if (this.$root.settings?.dashboardFavorites) {
            const favorites = []
            JSON.parse(this.$root.settings.dashboardFavorites).forEach((link) => {
                const found = this.favList.find((listed) => listed.link === link)
                if (found) favorites.push(found)
            })
            this.favorites = favorites
        }
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },
        data_user () { return this.$root.user },
        availableFavs () {
            const available = []

            this.favList.forEach((fav) => {
                if (!this.selectedFavs.find((select) => select.link === fav.link)) available.push(fav)
            })

            return available
        }
    },

    methods: {
        async getData () {
            this.$root.loading    = true
            this.data       = await this.$root.DBI_SELECT_GET('dashboard')
            this.$root.loading    = false
        },

        num (number) {
            return this.$handlers.format.number(this.l, number)
        },

        percentage (part, sum) {
            return part && sum ? '~&nbsp;' + Math.ceil(part / sum * 100) + '&nbsp;%' : ''
        },

        manageFavorites (toggle) {
            if (toggle) {
                this.selectedFavs = this.favorites.slice(0)
                this.showFavorites = true
            }
            else {
                this.showFavorites = false
            }
        },

        shiftFav (index, step) {
            if (step === 0) {
                if (this.selectedFavs.length > 1) this.selectedFavs.splice(index, 1)
                else this.selectedFavs = []
            }
            else {
                const newFavs = []
                for (let i = 0; i < this.selectedFavs.length; ++i) {
                    if (step > 0) {
                        if (i === index) {
                            newFavs.push(this.selectedFavs[index + step])
                            newFavs.push(this.selectedFavs[index])
                        }
                        else if (i !== index + 1) newFavs.push(this.selectedFavs[i])
                    }
                    else {
                        if (i === index - 1) {
                            newFavs.push(this.selectedFavs[index])
                            newFavs.push(this.selectedFavs[i])
                        }
                        else if (i !== index - 1 && i !== index) newFavs.push(this.selectedFavs[i])
                    }
                }
                this.selectedFavs = newFavs
            }
        },

        async saveFavorites () {
            await this.$root.changeSettings('dashboardFavorites', JSON.stringify(this.selectedFavs.map((fav) => { return fav.link })))
            this.favorites = this.selectedFavs
            this.manageFavorites (false)
        }
    }
}
</script>
