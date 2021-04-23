@extends('base.layout')

@section('content')
<div id="start"></div>
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

        <div id="md-to-top">
            <a href="#start">Back to Top</a>
        </div>
    </div>

</div>

<style>
    li {
        margin-bottom: 10px;
    }

    code {
        color: #cfc295;
    }

    /* Basics */
    #md-content {
        display: flex;
    }

    #md-toc {
        flex: 0 0 150px;
        margin: 0 50px 80px 0;
    }

    #md-article {
        flex: 1;
        position: relative;
        margin-bottom: 50px;
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
        margin-bottom: 50px;
    }

    #md-file {
        margin-bottom: 0px;
        padding-bottom: 0px;
    }

    #md-to-top {
        margin-top: 30px;
        text-align: end;
        font-weight: 500;
        font-size: 10pt;
    }

    .md-src {
        background-color: #202020;
        margin: 15px 0 15px 0;
        padding: 2px 15px 2px 15px;
    }

    /* Headings */
    .md-h2 {
        margin-bottom: -7px;
        padding-bottom: 0px;
        margin-top: 30px;
    }
    .md-h3 {
        margin-bottom: -7px;
        padding-bottom: 0px;
        margin-top: 30px;
    }
    .md-h4 {
        margin-bottom: -7px;
        padding-bottom: 0px;
        margin-top: 30px;
    }

    /* Toc Entries */
    .md-t:hover {
        color: #fff;
    }
    .md-t2 {
        font-weight: 800;
        font-size: 15px;
        margin-top: 15px;
    }
    .md-t3 {
        font-weight: 600;
        font-size: 14px;
        margin-left: 10px;
        margin-top: 8px;
    }
    .md-t4 {
        font-weight: 400;
        font-size: 11px;
        margin-left: 20px;
        margin-top: 4px;
    }
</style>
@endsection
