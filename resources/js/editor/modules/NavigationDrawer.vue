<template>
<div>
    <v-navigation-drawer 
        app 
        clipped
        v-model="$root.drawer.active" 
        :expand-on-hover="$root.drawer.mini && $vuetify.breakpoint.mdAndUp"
    > 
        <v-list :dense="dense" class="mt-n1">

            <!-- Dashbaord ------------------------------------------------------------------ -->
            <v-hover v-slot="{ hover }">
                <a href="/editor#/dashboard" style="width: 100%">
                    <v-list-item :class="hover ? 'secondary' : ''">
                        <v-list-item-action> 
                            <v-icon v-text="'dashboard'" :class="$route.name === 'dashboard' ? 'blue_sec--text' : ''"></v-icon>
                        </v-list-item-action>
                        <v-list-item-content> 
                            <v-list-item-title>Dashboard</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </a>
            </v-hover>

            <v-divider></v-divider>

            <!-- Primaries ---------------------------------------------------- -->
            <div
                v-for="(p, i) in primaries"
                :key="'primary' + i"
                class="d-flex justify-space-between"
            >
                <v-hover v-slot="{ hover }">
                    <a :href="'/editor#/' + p.name + '/search'" style="width: 100%">
                        <v-list-item :class="hover ? 'secondary' : ''">
                            <v-list-item-action> 
                                <v-icon v-text="p.icon" :class="$route.name === p.name + '-search' ? 'blue_sec--text' : ''"></v-icon>
                            </v-list-item-action>
                            <v-list-item-content> 
                                <v-list-item-title v-text="p.text"></v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                    </a>
                </v-hover>

                <a :href="'/editor#/' + p.name + '/edit'">
                    <advbtn 
                        icon="add"
                        :color_main="$route.path === '/editor#/' + p.text + '/edit' ? 'marked' : 'sysbar'"
                        color_hover="secondary"
                    ></advbtn>
                </a>
            </div>

            <!-- Secondaries ---------------------------------------------------- -->
            <template v-for="(group, i) in routes">
                <v-divider v-if="group === null" :key="'group' + i"></v-divider>

                <v-list-group 
                    v-else
                    :key="'group' + i" 
                    :prepend-icon="group.icon" 
                    no-action
                >
                    <template v-slot:activator>
                        <v-list-item-content>
                            <v-list-item-title v-text="group.text"></v-list-item-title>
                        </v-list-item-content>
                    </template>           

                    <v-hover 
                        v-for="(route, r) in group.children" 
                        :key="'group' + i + '.' + r"
                        v-slot="{ hover }"
                    >
                        <a :href="'/editor#/' + route.name" style='width: 100%'>
                            <v-list-item :class="hover ? 'secondary' : ''" class="ml-8">
                                <v-list-item-action>
                                    <v-icon v-text="route.icon" :class="$route.name === route.name ? 'blue_sec--text' : ''"></v-icon>
                                </v-list-item-action>
                                <v-list-item-content>
                                    <v-list-item-title v-text="route.text"></v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </a>
                    </v-hover>
                </v-list-group>
            </template>

            <!-- Logout -->            
            <v-divider></v-divider>

            <v-list-item @click="$root.logout()">
                <v-list-item-action>
                    <v-icon v-text="'power_settings_new'"></v-icon>
                </v-list-item-action>
                <v-list-item-content>
                    <v-list-item-title v-text="'Logout'"></v-list-item-title>
                </v-list-item-content>
            </v-list-item>
        </v-list> 
    </v-navigation-drawer>
</div>
</template>


<script>
export default {
    data () {     
        return {
            primaries: [
                { text: this.$root.label('types'),  icon: 'blur_circular',  name: 'types' },
                { text: this.$root.label('coins'),  icon: 'copyright',      name: 'coins' }
            ]
        }
    },

    computed: {
        rank () {
            return this.$root.user?.level ? this.$root.user.level : 10
        },
        routes (){ 
            const routes = [
                null,               
                { text: this.$root.label('Features'),         icon: 'info',               children: [
                    { text: this.$root.label('designs'),          icon: 'notes',              name: 'designs' },
                    { text: this.$root.label('legends'),          icon: 'translate',          name: 'legends' },
                    { text: this.$root.label('dies'),             icon: 'gavel',              name: 'dies' },
                    { text: this.$root.label('materials'),        icon: 'palette',            name: 'materials' },
                    { text: this.$root.label('denominations'),    icon: 'bubble_chart',       name: 'denominations' },
                    { text: this.$root.label('standards'),        icon: 'group_work',         name: 'standards' },
                    { text: this.$root.label('monograms'),        icon: 'functions',          name: 'monograms' },
                    { text: this.$root.label('symbols'),          icon: 'flare',              name: 'symbols' },
                    { text: this.$root.label('periods'),          icon: 'watch_later',        name: 'periods' }
                ]},
                { text: this.$root.label('individuals'),      icon: 'supervised_user_circle', children: [
                    { text: this.$root.label('persons'),          icon: 'emoji_people',       name: 'persons' },
                    { text: this.$root.label('owners'),           icon: 'account_circle',     name: 'owners' },
                    { text: this.$root.label('tribes'),           icon: 'groups',     name: 'tribes' }
                ]},
                { text: this.$root.label('locations'),         icon: 'explore',           children: [
                    { text: this.$root.label('mints'),            icon: 'museum',             name: 'mints' },
                    { text: this.$root.label('regions'),          icon: 'panorama',           name: 'regions' },
                    { text: this.$root.label('findspots'),        icon: 'pin_drop',           name: 'findspots' },
                    { text: this.$root.label('hoards'),           icon: 'grain',              name: 'hoards' }
                ]},
                { text: this.$root.label('tools'),            icon: 'build_circle',       children: [
                    { text: this.$root.label('object_groups'),    icon: 'control_camera',     name: 'objectgroups' },
                    { text: this.$root.label('bibliography'),     icon: 'menu_book',          name: 'bibliography' },
                    { text: this.$root.label('file_browser'),     icon: 'folder_open',        name: 'files' },
                    //{ text: this.$root.label('broken_links'),     icon: 'link_off',           name: 'brokenlinks' }
                ]}              
            ]
            // PR
            if (this.rank >= 21) {
                routes.push(null)
                routes.push({ text: this.$root.label('public_relations'),  icon: 'wifi_tethering',  children: [
                    { text: this.$root.label('coin_of_the_month'),  icon: 'stars',      name: 'coin-of-the-month' },
                    { text: this.$root.label('news'),               icon: 'comment',    name: 'news' }
                ]})
            }
            // Website
            /*if (this.rank >= 22) {
                routes.push({ text: this.$root.label('website'),  icon: 'public',  children: [
                    { text: this.$root.label('team'),   icon: 'portrait',           name: 'team' },
                    { text: this.$root.label('texts'),  icon: 'format_align_left',  name: 'team' }
                ]})
            }*/
            // Admin
            if (this.rank >= 31) {
                routes.push(null)
                routes.push({ text: this.$root.label('administrator'),  icon: 'font_download',  children: [
                    { text: 'Users',    icon: 'people',     name: 'users' },
                 // { text: 'Errorlog', icon: 'bug_report', name: 'errors' }
                ]})
            }

            return routes
        },

        dense () { return true }
    },

    methods: {
        navigate (name) {
            if (this.$route.name === name) {
                this.$root.router_view_refresh++
            }
            else {
                // Resolve given name to check if route is registered
                const resolved = this.$router.resolve({ name: name });
                if (resolved.href.length > 2) {
                    this.$router.push({ name: name })
                }
                else {
                    alert('Error: "' + name + '" is no registered route!')
                }
            }
        }
    }
}
</script>