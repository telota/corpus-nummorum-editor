@extends('base.layout')

@section('content')

    <div style="display: flex;">
        <div style="flex: 0 0 200px; margin: 0 50px 80px 0;">
            @foreach ($toc as $anchor => $text)
                {!! $anchor !!}
            @endforeach
        </div>

        <div style="flex: 1;">
            {!! $content !!}
        </div>
    </div>
@endsection
