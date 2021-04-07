<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CORPUS NUMMORUM DATA</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #222222;
            color: #ccc;
            font-family: 'Raleway', sans-serif;
            font-weight: 100;
            height: 100vh;
            margin: 0;
        }

        .links > a {
            color: #999;
            padding: 0 25px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }
    </style>

</head>

<body>
    <div style="width: 100%; display: flex; justify-content: space-between;">
        <div>
            <a href="https://www.corpus-nummorum.eu/">Corpus Nummorum</a>
        </div>

        <div style="display: flex;">
            @auth
                <a href="{{ url('/home') }}">Home</a>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" >Log in</a>
                @endif

                @if (Route::has('register'))
                    <a href="{{ route('register') }}">Register</a>
                @endif
            @endauth
        </div>
    </div>

    <div>
        @yield('content')
    </div>
</body>

</html>

<style
