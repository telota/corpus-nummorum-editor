<!DOCTYPE html>
<html lang="{{ $data['language'] }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- <!-- CSRF Token-->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @csrf --}}

    <title>CN Editor (Corpus Nummorum)</title>

    <!-- Styles -->
    {{--<link rel="shortcut icon" href="favicon.ico">--}}
    <link rel="icon" type="image/png" href="favicon.png" sizes="96x96">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link href="{{ env('APP_URL') }}/css/editor.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons' rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">

    <!-- App JS -->
    <script type="application/javascript">
        var LSK_APP = {};
        LSK_APP.APP_URL = '{{ env('APP_URL') }}';
    </script>

</head>

<body>

    <!-- Initial Loading Screen -->
    <div class="loader">
        <div class="loader-background-gradient">
            <div class="loader-text"><b>CN</b> Editor</div>
            <div class="loader-spinner"></div>
        </div>
        <div class="loader-footer">
            <p>
                CORPUS NUMMORUM&ensp;|&ensp;TELOTA - IT/DH<br/>
                Berlin-Brandenburg Academy of Sciences and Humanities<br/>
                2020{!! date('y') > 20 ? ('&ndash;'.date('y')) : ('') !!}
            </p>
        </div>
    </div>

    <!-- Vue SPA -->
    <div id="editor">

        <!-- Session -->
        <sessioninfo :data='{!! json_encode($data) !!}'></sessioninfo>

        <!-- Template -->
        <template>
            <v-app id="inspire">

                <!-- Loading Screen -->
                <div v-if="loading" class="loader loader-half-transparent">
                    <div class="loader-background">
                        <div class="loader-text"><b>CN</b> Editor</div>
                        <div class="loader-spinner"></div>
                    </div>
                </div>

                <!-- Snack Message -->
                <div v-if="snack.message" class="d-flex justify-center" style="position: absolute; width: 100%;">
                    <v-fade-transition>
                        <v-card tile :class="(snack.color ? snack.color : 'sysbar')+' mt-1 pa-2 pl-7 pr-7 body-1'" style="z-index: 100;" @click="snack = {color: null, message: null}">
                            @{{ snack.message }}
                        </v-card>
                    </v-fade-transition>
                </div>

                <!-- Appbar -->
                <v-app-bar app dense  style="height: 50px; z-index: 500">
                    <!-- Left Section -->
                    <advbtn
                        v-if="user && user.level > 9"
                        icon="menu"
                        tooltip="Togle Navigation"
                        class="ml-n3"
                        v-on:click="drawer.active = !drawer.active"
                    ></advbtn>
                    <advbtn
                        v-if="user && user.level > 9 && drawer.active && $vuetify.breakpoint.mdAndUp"
                        :icon="drawer.mini ? 'keyboard_arrow_right' : 'keyboard_arrow_left'"
                        :tooltip="drawer.mini ? 'Expand Navigation' : 'Collapse Navigation'"
                        :key="drawer.mini ? 'collapsed' : 'expanded'"
                        v-on:click="drawer.mini = !drawer.mini"
                    ></advbtn>
                    <v-toolbar-title class="ml-3">
                        CN
                        <span class="font-weight-thin">Editor</span>
                    </v-toolbar-title>

                    <v-spacer></v-spacer>

                    <!-- Right Section -->
                    <div v-if="$vuetify.breakpoint.smAndUp" class="pl-2 pr-5">
                        <span v-if="user && user.level < 10">
                            <v-icon small class="mr-1">person</v-icon> {{ $data['user']['fullname'] }}
                        </span>
                        <a v-else href="{{url('/editor#/dashboard')}}">
                            <v-icon small class="mr-1">person</v-icon> {{ $data['user']['fullname'] }}
                        </a>
                    </div>
                    <vertdivider></vertdivider>
                    <advbtn
                        icon="help_outline"
                        tooltip="Wiki"
                        v-on:click="openInNewTab('/wiki')"
                    ></advbtn>
                    <advbtn
                        :icon="$vuetify.theme.dark ? 'invert_colors' : 'invert_colors_off'"
                        :tooltip="$vuetify.theme.dark ? 'Switch to light Theme' : 'Switch to dark Theme'"
                        :key="$vuetify.theme.dark ? 'dark' : 'light'"
                        v-on:click="changePresets('color_theme', $vuetify.theme.dark === true ? 0 : 1)"
                    ></advbtn>
                    <advbtn
                        :text="language.toUpperCase()"
                        :tooltip="language === 'de' ? 'Switch to English' : 'Zu Deutsch wechseln'"
                        :key="language"
                        v-on:click="changePresets('language', language === 'de' ? 'en' : 'de')"
                    ></advbtn>
                    <vertdivider></vertdivider>
                    <advbtn
                        icon="power_settings_new"
                        tooltip="Logout"
                        v-on:click="logout()"
                        class="mr-n4"
                    ></advbtn>

                </v-app-bar>


                <!-- Navigation Drawer -->
                <Navigation-Drawer
                    v-if="user && user.level > 9"
                    dense="true"
                    level="{{ $data['user']['level'] }}"
                ></Navigation-Drawer>


                <!-- Routed Component -->
                <v-main class="app_bg" style="margin-left: 55px;">
                    <v-fade-transition>
                        <div class="pa-5">
                            <router-view id="router-space" :key="'router-view' + router_view_refresh"></router-view>
                        </div>
                    </v-fade-transition>
                </v-main>


                <!-- Footer -->
                <v-footer app fixed class="caption d-flex flex-wrap justify-space-between" style="height: 30px; z-index: 500">
                    <div>
                        <a
                            href="https://www.bbaw.de"
                            target="_blank"
                            class="grey--text text-truncate"
                            v-html="$root.label('bbaw') + '&ensp;2020&ndash;{{ date('y') }}'"
                        ></a>
                    </div>

                    <div>
                        <a
                            href="/imprint"
                            target="_blank"
                            class="grey--text text-truncate mr-3"
                            v-text="'Imprint'"
                        ></a>
                        <a
                            href="/licensing"
                            target="_blank"
                            class="grey--text text-truncate mr-3"
                            v-html="'Licensing'"
                        ></a>
                        <a
                            href="/wiki"
                            target="_blank"
                            class="grey--text text-truncate"
                            v-html="'Wiki'"
                        ></a>
                    </div>
                </v-footer>

            </v-app>

            <!-- Error Dialog -->
            <Error-Dialog v-if="error.active" :error="error" v-on:close="error = {active: false}"></Error-Dialog>

            <!-- 403 -->
            <v-dialog
                v-if="parseInt({!! $data['user']['level'] !!}) < 9 && $route.name !== 'contributions'"
                value="true"
                fullscreen
                persistent
            >
                <div class="app_bg d-flex align-center justify-center" style="height: 100%">
                    <div class="text-center">
                        <div
                            v-html="'Sorry, this area is restricted to CN staff.<br />Please return to the previous page.'"
                            class="mb-5"
                        ></div>
                        <div class="d-flex justify-center">
                            <a href="/editor#/contributions">
                                <v-btn
                                    tile
                                    color="blue_prim"
                                    class="mr-2"
                                    v-text="'Back'"
                                ></v-btn>
                            </a>
                            <v-btn
                                tile
                                class="ml-2"
                                v-text="'logout'"
                                @click="logout()"
                            ></v-btn>
                        </div>
                    </div>
                </div>
            </v-dialog>

        </template>

    </div>

    <!-- Editor JS -->
    <script src="{{ env('APP_URL') }}/js/editor.js"></script>

</body>

</html>
