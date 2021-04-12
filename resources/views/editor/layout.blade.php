<!DOCTYPE html>
<html lang="{{ $data['presets']['language'] }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CN Editor (Corpus Nummorum)</title>

    <!-- Styles -->
    {{--<link rel="shortcut icon" href="favicon.ico">--}}
    <link rel="icon" type="image/png" href="favicon.png" sizes="96x96">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link href="{{ asset('css/editor.css') }}" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons' rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">

    <!-- App JS -->
    <script type="application/javascript">
        var LSK_APP = {};
        LSK_APP.APP_URL = '{{env('APP_URL')}}';
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
        <div id="vue">
            @yield('app')
        </div>
    </div>

    <!-- Editor JS -->
    <script src="{{ asset('js/editor.js') }}"></script>

</body>

</html>
