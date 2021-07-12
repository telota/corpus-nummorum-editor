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
    <link href="{{ env('APP_URL') }}/css/loader.css" rel="stylesheet">
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
    <div id="initial-loader">
        <div class="loader-symbol">
            <div class="loader-circle"></div>
            <div class="loader-mask"></div>
            <div class="loader-text">
                <span class="loader-text-main">CN</span><br/>
                <span class="loader-text-sub">Editor</span>
            </div>
        </div>
        <div class="loader-shine"></div>
        <div class="loader-footer">
            <p>
                CORPUS NUMMORUM&ensp;|&ensp;TELOTA - IT/DH<br/>
                Berlin-Brandenburg Academy of Sciences and Humanities, Germany<br/>
                2020&ndash;{{ date('Y') }}
            </p>
        </div>
    </div>

    <!-- Vue SPA -->
    <div id="editor">

        <app-settings :data='{!! json_encode($data) !!}'></app-settings>

        <template>
            <v-app id="inspire" :style="$vuetify.theme.dark ? 'background-color:#181818' : 'background-color:#d0d0d0'">

                <app-header></app-header>


                <div id="app-routed-component">
                    <!--<v-main class="pa-0 red">-->
                        <!--<v-fade-transition>-->
                            <router-view class="pa-0 ma-0" ></router-view>
                        <!--</v-fade-transition>-->
                    <!--</v-main>-->
                </div>

                <!--<app-loader></app-loader>-->
                <!--<app-dialog></app-dialog>-->
                <app-about></app-about>
                <!--<app-context-menu></app-context-menu>-->

            </v-app>
        </template>

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

    </div>

    <!-- Editor JS -->
    <script src="{{ env('APP_URL') }}/js/editor.js"></script>

</body>

</html>
