<template>
<div id="app-header">
    <!-- Snack -->
    <v-fade-transition>
        <div
            v-if="snackMessage"
            class=" d-flex align-center justify-center"
            :style="[
                'position: fixed',
                'left: 50%',
                'margin-left: -250px',
                'z-index: 102',
                'height: 40px',
                'width: 500px',
                'background: linear-gradient(to right, rgba(0,0,0,0) 0%, rgba(' + snackBg + ',1) 20%, rgba(' + snackBg + ',1) 80%, rgba(0,0,0,0) 100%)'
            ].join(';\n')"
        >
            <div
                :class="snackColor"
                class="pl-2 pr-2 caption font-weight-medium text-truncate text-center"
                v-html="snackMessage"
                style="width: 300px;"
            />
        </div>
    </v-fade-transition>

    <div class="header_bg d-flex align-center" style="height: 40px; width: 100%;">

        <!-- App Name -->
        <a href="/editor#/dashboard" class="invert--text">
            <v-hover v-slot="{ hover }">
                <div
                    class="pl-3 pr-3 d-flex align-center"
                    :class="hover ? 'header_hover' : ''"
                    style="height: 40px; cursor: pointer;"

                >
                    <div
                        :class="($vuetify.theme.dark ? 'white' : 'black') + '--text'"
                        style="font-size: 24px; line-height: 24px;" v-text="'CN'"
                    />
                </div>
            </v-hover>
        </a>

        <!-- Navigation -->
        <template v-for="(route, r) in routes">
            <v-menu
                :key="'route' + r"
                offset-y
                open-on-hover
                tile
            >
                <template v-slot:activator="{ on, attrs }">
                    <v-hover v-slot="{ hover }">
                        <div
                            v-bind="attrs"
                            v-on="on"
                            class="pr-2 pl-2 d-flex align-center"
                            :class="hover ? 'header_hover' : ''"
                            style="height: 40px;"
                        >
                            <div v-if="$vuetify.breakpoint.mdAndUp" v-text="route.text" />
                            <v-icon v-else :small="$vuetify.breakpoint.xsOnly" v-text="route.icon" />
                        </div>
                    </v-hover>
                </template>

                <v-card
                    tile
                    class="header_bg"
                    :max-width="400"
                    :min-width="200"
                >
                    <v-hover
                        v-for="(item, i) in route.children"
                        :key="'route' + r + '.' + i"
                        v-slot="{ hover }"
                    >
                        <a
                            v-if="item.link"
                            :href="item.link.startsWith('http') ? item.link : ('/editor#/' + item.link)"
                            :target="item.link.startsWith('http') ? '_blank' : ''"
                        >
                            <div
                                class="d-flex align-center pa-2 invert--text"
                                :class="(hover ? 'header_hover' : '') + ($route.name === item.link ? ' blue_sec--text' : '')"
                                :style="'cursor:' + (hover ? 'pointer' : 'default')"
                            >
                                <v-icon class="mr-2" v-text="item.icon" :color="$route.name === item.link ? 'blue_sec' : ''" />
                                <div v-text="item.text" />
                            </div>
                        </a>
                        <div v-else-if="item.action">
                            <div
                                class="d-flex align-center pa-2 invert--text"
                                :class="(hover ? 'header_hover' : '')"
                                :style="'cursor:' + (hover ? 'pointer' : 'default')"
                                @click="item.action"
                            >
                                <v-icon class="mr-2" v-text="item.icon" />
                                <div v-text="item.text" />
                            </div>
                        </div>
                    </v-hover>
                </v-card>
            </v-menu>
        </template>


        <v-spacer />

        <adv-btn
            :text="$root.language.toUpperCase()"
            :tooltip="$root.language === 'de' ? 'Switch to English' : 'Zu Deutsch wechseln'"
            medium
            colorHover="marked"
            v-on:click="$root.changeSettings('language', $root.language === 'de' ? 'en' : 'de')"
        />
        <adv-btn
            :icon="$vuetify.theme.dark ? 'invert_colors' : 'invert_colors_off'"
            :tooltip="$vuetify.theme.dark ? 'Switch to light Theme' : 'Switch to dark Theme'"
            medium
            colorHover="marked"
            v-on:click="$root.changeSettings('color_theme', $vuetify.theme.dark === true ? 0 : 1)"
        />
        <!-- Logout -->
        <adv-btn
            icon="power_settings_new"
            tooltip="Logout"
            medium
            colorHover="marked"
            v-on:click="logout()"
            class="mr-n4"
        />

    </div>

    <!-- Loading -->
    <v-progress-linear :indeterminate="$root.loading" height="1" />
</div>
</template>


<script>

export default {
    data () {
        return {
            showAccount: false
        }
    },
    computed: {
        user () {
            return this.$root.user
        },
        snackMessage () {
            return this.$store.state.snack?.message ?? null
        },
        snackColor () {
            return (this.$store.state.snack?.color ? this.$store.state.snack.color : 'invert') + '--text'
        },
        snackBg () {
            return this.$vuetify.theme.dark ? '54,54,54' : '230,230,230'
        },
        rank () {
            return this.$root.user?.level ? this.$root.user.level : 10
        },
        routes (){
            const routes = [
                { text: this.$root.label('types'),         icon: 'blur_circular',               children: [
                    { text: this.$root.label('types_search'),     icon: 'search',             link: 'types/search' },
                    { text: this.$root.label('types_new'),        icon: 'add_circle_outline', link: 'types/edit' },
                    { text: this.$root.label('types_import'),     icon: 'arrow_circle_down',  link: 'types/import' }
                ]},
                { text: this.$root.label('coins'),         icon: 'copyright',               children: [
                    { text: this.$root.label('coins_search'),     icon: 'search',             link: 'coins/search' },
                    { text: this.$root.label('coins_new'),        icon: 'add_circle_outline', link: 'coins/edit' },
                    { text: this.$root.label('coins_upload'),     icon: 'drive_folder_upload',link: 'storage/coin-images' },
                    { text: this.$root.label('coins_import'),     icon: 'arrow_circle_down',  link: 'coins/import' }
                ]},
                //null,
                { text: this.$root.label('Features'),         icon: 'info',               children: [
                    { text: this.$root.label('designs'),          icon: 'notes',              link: 'designs' },
                    { text: this.$root.label('legends'),          icon: 'translate',          link: 'legends' },
                    { text: this.$root.label('legends_index'),    icon: 'translate',          link: 'legends-index' },
                    { text: this.$root.label('dies'),             icon: 'gavel',              link: 'dies' },
                    { text: this.$root.label('materials'),        icon: 'palette',            link: 'materials' },
                    { text: this.$root.label('denominations'),    icon: 'bubble_chart',       link: 'denominations' },
                    { text: this.$root.label('standards'),        icon: 'group_work',         link: 'standards' },
                    { text: this.$root.label('monograms'),        icon: 'functions',          link: 'monograms' },
                    { text: this.$root.label('symbols'),          icon: 'flare',              link: 'symbols' },
                    { text: this.$root.label('periods'),          icon: 'watch_later',        link: 'periods' }
                ]},
                { text: this.$root.label('individuals'),      icon: 'supervised_user_circle', children: [
                    { text: this.$root.label('persons'),          icon: 'emoji_people',       link: 'persons' },
                    { text: this.$root.label('owners'),           icon: 'account_circle',     link: 'owners' },
                    { text: this.$root.label('tribes'),           icon: 'groups',     link: 'tribes' }
                ]},
                { text: this.$root.label('locations'),         icon: 'explore',           children: [
                    { text: this.$root.label('mints'),            icon: 'museum',             link: 'mints' },
                    { text: this.$root.label('regions'),          icon: 'panorama',           link: 'regions' },
                    { text: this.$root.label('findspots'),        icon: 'pin_drop',           link: 'findspots' },
                    { text: this.$root.label('hoards'),           icon: 'grain',              link: 'hoards' }
                ]},
                { text: this.$root.label('tools'),            icon: 'build_circle',       children: [
                    //{ text: this.$root.label('file_browser'),     icon: 'folder_open',        link: 'files' },
                    { text: this.$root.label('file_manager'),     icon: 'folder_open',        link: 'storage' },
                    { text: this.$root.label('bibliography'),     icon: 'menu_book',          link: 'bibliography' },
                    { text: this.$root.label('zotero'),           icon: 'auto_stories',       link: 'https://www.zotero.org/groups/163139/thrakien/library' },
                    { text: this.$root.label('object_groups'),    icon: 'control_camera',     link: 'objectgroups' },
                    { text: this.$root.label('SPARQL'),           icon: 'auto_awesome',       link: 'sparql' },
                ]}
            ]
            // PR
            if (this.rank >= 21) {
                //routes.push(null)
                routes.push({ text: 'Website',  icon: 'wifi_tethering',  children: [
                    { text: this.$root.label('coin_of_the_month'),  icon: 'stars',      link: 'coin-of-the-month' },
                    { text: this.$root.label('news'),               icon: 'comment',    link: 'news' }
                ]})
            }
            // Website
            /*if (this.rank >= 22) {
                routes.push({ text: this.$root.label('website'),  icon: 'public',  children: [
                    { text: this.$root.label('team'),   icon: 'portrait',           link: 'team' },
                    { text: this.$root.label('texts'),  icon: 'format_align_left',  link: 'team' }
                ]})
            }*/
            // Admin
            if (this.rank >= 31) {
                //routes.push(null)
                routes.push({ text: 'Admin',  icon: 'font_download',  children: [
                    { text: 'Users',    icon: 'people',                 link: 'users' },
                    { text: 'Logs',     icon: 'format_list_bulleted',   link: 'logs' },
                    { text: 'Gitlab',   icon: 'pending_actions',        link: 'https://gitup.uni-potsdam.de/TELOTA/cluster-alte-welt/corpus-nummorum/cn-dokumentation/issues' }
                ]})
            }

            //routes.push(null)
            routes.push({ text: 'Hilfe',  icon: 'info',  children: [
                { text: 'Wiki',         icon: 'help_outline',           link: this.$root.baseURL + '/wiki' },
                { text: 'Nomisma',      icon: 'monetization_on',        link: 'http://nomisma.org' },
                { text: 'About',        icon: 'info',                   action: () => { this.$store.commit('setAbout', true) } }
            ]})

            return routes
        }
    },

    methods: {
        openInNewTab (link) {
            if (link) { window.open(link) }
        },
        goTo (link) {
            if (link.startsWith('http')) this.openInNewTab('link')
            else if (this.$route.name !== link) this.$router.push('/' + link)
            //else window.location.reload()
        }
    }
}

</script>

<style scoped>
    #app-header {
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 300;
        height: 41px;
    }
</style>
