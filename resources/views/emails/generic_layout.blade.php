<!doctype html>

<html lang="de">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>CORPUS NUMMORUM{{ empty($data['subject']) ? '' : $data['subject'] }}</title>
</head>

<body>
    <!-- Body -->
    <div style="margin-bottom: 10pt">
    @yield('greeting')
    </div>

    <!-- Body -->
    <div style="margin-bottom: 10pt">
    @yield('body')
    </div>

    <div style="margin-bottom: 10pt">
        Please do not reply to this email, use <a href="mailto:cn-support@bbaw.de">cn-support@bbaw.de</a> instead.<br/>
        For further communication channels, have a look on our <a href="https://www.corpus-nummorum.eu/contact">contact information</a>.
        </ul>
    </div>

    <!-- Footer -->
    ------
    <div style="font-size: 10pt">
    @if (!empty($data['random_type']))
        Do you know <a href="https://www.corpus-nummorum.eu/types/{{ $data['random_type'] }}">cn type {{ $data['random_type'] }}</a>?<br/>
        Have a nice day!       
    @else
        <div>Have a nice Day!</div>
    @endif
    </div>

</body>

</html>