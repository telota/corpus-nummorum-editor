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
    <link href="{{ env('APP_URL') }}/css/editor.css{{ $version }}" rel="stylesheet">
    <link href="{{ env('APP_URL') }}/css/loader.css{{ $version }}" rel="stylesheet">
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
            <v-app id="inspire" :style="$vuetify.theme.dark ? 'background-color:#181818' : 'background-color:#aaa'">

                <app-header></app-header>

                <div id="app-routed-component">
                    <router-view class="pa-0 ma-0" ></router-view>
                </div>

                <app-about></app-about>
                <app-error></app-error>
                <coin-type-details></coin-type-details>
                <app-eye-protection></app-eye-protection>
            </v-app>
        </template>

    </div>

    <!-- Editor JS -->
    <script src="{{ env('APP_URL') }}/js/editor.js{{ $version }}"></script>

</body>

</html>
