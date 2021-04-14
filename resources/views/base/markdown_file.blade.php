@extends('base.layout')

@section('content')
<div id="md-content">

    @if (!empty($toc))
    <!-- TOC -->
    <div id="md-toc">
        {!! implode("\n", $toc) !!}
    </div>
    @endif

    <!-- Article -->
    <div id="md-article">

        @if (!empty($heading))
        <!-- Heading -->
        <h1 id="md-heading">{!! $heading !!}</h1>
        @endif

        @if (!empty($date))
        <!-- Heading -->
        <div id="md-date">{!! $date !!}</div>
        @endif

        @if (!empty($introduction))
        <!-- Introduction -->
        <div id="md-introduction">{!! $introduction !!}</div>
        @endif

        <!-- File Content -->
        <div id="md-file">
            {!! $content ? $content : 'NO CONTENT' !!}
        </div>
    </div>

</div>

<style>
    /* Basics */
    #md-content {
        display: flex;
    }

    #md-toc {
        flex: 0 0 200px;
        margin: 0 50px 80px 0;
    }

    #md-article {
        flex: 1;
        position: relative;
    }

    #md-heading {
        margin-top: 0;
        padding-top: 0;
    }

    #md-date {
        position: absolute;
        right: 0;
        top: 0;
    }

    #md-introduction {

    }

    #md-file {

    }

    /* Headings */
    .md-h2 {
    }

    .md-h3 {
    }

    .md-h4 {
    }

    /* Toc Links */
    .md-l2 {
        font-weight: 800;
        font-size: 15px;
    }

    .md-l3 {
        font-weight: 600;
        font-size: 14px;
        margin-left: 20px;
    }

    .md-l4 {
        font-weight: 300;
        font-size: 11px;
        margin-left: 40px;
    }
</style>
@endsection
