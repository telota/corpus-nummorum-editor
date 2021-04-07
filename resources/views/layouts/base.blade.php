<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CORPUS NUMMORUM</title>

</head>

<body>
    <div style="width: 100%; display: flex; justify-content: space-between;">
        <div>
            <a href="https://www.corpus-nummorum.eu/">Corpus Nummorum</a>
        </div>

        <div style="display: flex;">
            @auth
                <a href="{{ url('/home') }}" class="text-sm text-gray-700">Home</a>
                <a href="{{ route('logout') }}" class="text-sm text-gray-700" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="text-sm text-gray-700">Log in</a>
                @endif

                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700">Register</a>
                @endif
            @endauth
        </div>
    </div>

    <div>
        @yield('content')
    </div>
</body>

</html>
