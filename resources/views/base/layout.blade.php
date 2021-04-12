<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CN Data (Corpus Nummorum)</title>

    <!-- Styles -->
    {{--<link rel="shortcut icon" href="favicon.ico">--}}
    <link rel="icon" type="image/png" href="/favicon.png" sizes="96x96">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link href="{{ asset('css/base.css') }}" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons' rel="stylesheet">

</head>

<body>
    <div id="base-header">
        <div id="base-header-container">
            <div>
                <a href="{{ env('APP_URL') }}">Corpus Nummorum Data</a>
            </div>

            <div style="display: flex;">
                @auth
                    <a href="{{ url('/') }}">Start</a>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
                @else
                    @if (url()->current() !== env('APP_URL').'/login')
                        <a href="{{ route('login') }}" >Log in</a>
                    @endif

                    @if (url()->current() !== env('APP_URL').'/register')
                        <a href="{{ route('register') }}">Register</a>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <div id="base-container">
        <div class="base-container-helper">
            @yield('content')
        </div>
    </div>

    <div id="base-footer">
        <div id="base-footer-container">
            <div>Test</div>
        </div>
    </div>
</body>

</html>
