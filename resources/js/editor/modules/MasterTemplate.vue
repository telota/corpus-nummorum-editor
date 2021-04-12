<template>
    <v-app id="inspire">

        <!-- Navigation Drawer (left) -->
        <v-navigation-drawer 
            app clipped
            v-model="drawer" 
            :expand-on-hover="$store.state.drawerMini && $vuetify.breakpoint.mdAndUp" 
        >
            <v-list dense>
            </v-list>
        </v-navigation-drawer>
        
        <!-- Appbar (top) -->
        <v-app-bar app clipped-left dense>

            <v-btn icon @click.stop="drawer = !drawer">
                <v-icon>menu</v-icon>
            </v-btn>
            <!--<v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>-->
            <v-btn icon small @click="$store.commit ('SET_DRAWERMINI')" class="mr-2" v-if="$vuetify.breakpoint.mdAndUp">
                <v-icon>{{ $store.state.drawerMini ? 'keyboard_arrow_right' : 'keyboard_arrow_left'}}</v-icon>
            </v-btn>
            <v-toolbar-title>CN <span class="font-weight-thin">Editor</span></v-toolbar-title>

            <v-spacer></v-spacer>

            <v-tooltip bottom>
                <template v-slot:activator="{ on }">                
                    <v-btn v-on="on" icon small class="mr-2"><v-icon>bug_report</v-icon></v-btn>
                </template>
                <span>Report a bug</span>
            </v-tooltip>

            <v-tooltip bottom>
                <template v-slot:activator="{ on }">                
                    <v-btn v-on="on" icon small class="mr-2"><v-icon>help_outline</v-icon></v-btn>
                </template>
                <span>Wiki</span>
            </v-tooltip>

            <v-tooltip bottom>
                <template v-slot:activator="{ on }">                
                    <v-btn v-on="on" icon small class="mr-5" @click="$vuetify.theme.dark=!$vuetify.theme.dark">
                        <v-icon>@{{ $vuetify.theme.dark ? 'invert_colors' : 'invert_colors_off' }}</v-icon>
                    </v-btn>
                </template>
                <span>@{{ $vuetify.theme.dark ? 'Switch to light color theme' : 'Switch back to dark color theme' }}</span>
            </v-tooltip>

            <v-tooltip bottom>
                <template v-slot:activator="{ on }">                
                    <v-btn v-on="on" icon small class="mr-1" @click="" >
                        <v-icon>power_settings_new</v-icon>
                    </v-btn>
                </template>
                <span>Logout</span>
            </v-tooltip>

        </v-app-bar>


        <!-- Vue Component -->
        <v-content class="app_bg">
            <transition name="fade">

                <router-view class="ma-5"></router-view>

            </transition>                
        </v-content>


        <!-- Footer -->
        <v-footer app fixed padless class="pl-4 pr-5">
            <v-row class="flex-nowrap">

                <v-col cols="12" sm="4" md="4" lg="4">
                </v-col>

                <v-col cols="12" sm="4" md="4" lg="4" class="text-center">
                    &copy; 2013| about
                </v-col>

                <v-col cols="12" sm="4" md="4" lg="4" class="text-right">
                    <a href="">
                        <v-icon small class="mr-1">person</v-icon> 
                    </a>
                </v-col>

            </v-row>
        </v-footer>

    </v-app>    
</template>



<script>

export default
{ 
    data ()
    {
        return {
            
        }
    },


    props: 
    {
        prop_user:          {type: Object},
        prop_menu:          {type: Array},
    },


    computed: 
    {
        menu () { 
            return '';
        },
    },


    created () 
    {
        var self = this;

        self.$store.commit ('showLoader'); // JK: Loading screen ON
        
        self.$store.commit ('hideLoader'); // JK: Loading screen OFF
    },

    
    methods: 
    {

    }
}

</script>
