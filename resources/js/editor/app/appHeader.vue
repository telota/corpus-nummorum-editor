<template>
<div id="app-header">
    <!-- Snack
    <v-card tile raised height="">
    <div class="d-flex justify-center align-center" style="height: 40px">
        <v-fade-transition>
            <div
                v-if="snackMessage"
                class="pl-2 pr-2 caption font-weight-medium text-truncate"
                v-text="snackMessage"
                :class="snackColor"
            />
        </v-fade-transition>
    </div>-->

    <div class="header_bg d-flex align-end" style="height: 40px">

        <!-- App Name -->
        <a href="/editor#/dashboard" class="invert--text">
            <v-hover v-slot="{ hover }">
                <div
                    class="pl-3 pr-3 d-flex align-end"
                    :class="hover ? 'header_hover' : ''"
                    style="height: 40px; cursor: pointer; padding-bottom: 3px"
                >
                    <div v-text="'CN'" style="font-size: 30px; line-height: 30px" />
                </div>
            </v-hover>
        </a>

        <!-- Navigation -->
        <v-menu
            v-for="(route, r) in routes"
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
                        class="pr-2 pl-2 d-flex align-end"
                        :class="hover ? 'header_hover' : ''"
                        style="height: 40px; padding-bottom: 3px"
                    >
                        <div v-text="route.text" />
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
    <v-progress-linear :indeterminate="$root.loading" height="1" />

    <!--<v-card tile style="position: fixed; top: 0; width: 100%; height: 50px;" />-->
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
        user () { return this.$root.user },
        snackMessage () { return this.$store.state.snack?.message ?? null },
        snackColor () { return this.$store.state.snack?.color ? (this.$store.state.snack.color + '--text') : '' },
        rank () {
            return this.$root.user?.level ? this.$root.user.level : 10
        },
        routes (){
            const routes = [
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
                    { text: this.$root.label('file_browser'),     icon: 'folder_open',        link: 'files' },
                    { text: this.$root.label('file_manager'),     icon: 'folder_open',        link: 'storage' },
                    { text: this.$root.label('bibliography'),     icon: 'menu_book',          link: 'bibliography' },
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
