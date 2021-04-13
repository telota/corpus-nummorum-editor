@extends('base.layout')

@section('content')
<div style="display: flex;">

    <!-- TOC -->
    <div id="wiki-toc">
        {!! implode("\n", $toc) !!}
    </div>

    <!-- Content -->
    <div class="wiki-content">
        <!-- Prescript -->
        <div>
            <h1 id="wiki-h1">CN Editor Wiki</h1>
            <p>
                {{ $date }}
            </p>
        </div>

        <!-- Lemmata -->
        <div>
            {!! $content !!}
        </div>
    </div>

</div>

<style>
    #wiki-toc {
        flex: 0 0 200px;
        margin: 0 50px 80px 0;
    }

    #wiki-content {
        flex: 1;
    }

    #wiki-h1 {
        margin-top: 0;
        padding-top: 0;
    }

    .wiki-h2 {
    }

    .wiki-h3 {
    }

    .wiki-h4 {
    }


    .wiki-l2 {
        font-weight: 800;
        font-size: 15px;
    }

    .wiki-l3 {
        font-weight: 600;
        font-size: 14px;
        margin-left: 20px;
    }

    .wiki-l4 {
        font-weight: 300;
        font-size: 11px;
        margin-left: 40px;
    }
</style>
@endsection
