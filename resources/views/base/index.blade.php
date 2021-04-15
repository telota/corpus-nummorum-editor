@extends('base.layout')

@section('content')
<div class="tiles">

    <!-- Editor -->
    <div class="card tile">
        <div class="card-header">CN Editor App</div>

        <div class="card-body">
            <p>
                The CN Editor is a modern web application to create and manage the Corpus Nummorum Datasets.<br>
                <a href="/editor">Go to App</a>,
                <a href="/wiki#usage-editor">How to use</a>
            </p>
            <p>
                We invite our community to register as an editor and join our common efforts.<br/>
                <a href="/register">Registration</a>
            </p>
            <p>
                It is open source software - feel free to adapt the code as you want.<br>
                <a href="/repository/source-code">Source Code on Github</a>
            </p>
        </div>
    </div>

    <!-- API -->
    <div class="card tile">
        <div class="card-header">API & Data Repository</div>

        <div class="card-body">
            <p>
                We provide a restful json-API, so you can access all our public datasets without registration in a convinient and standardized Way.<br/>
                <a href="/wiki#usage-api">How to use</a>
            </p>
            <p>
                We publish all our versioned types as static files on Github, too.<br/>
                <a href="/repository/data">CN Types on Github</a>
            </p>
        </div>
    </div>

    <!-- sparql -->
    <div class="card tile">
        <div class="card-header">SPARQL Endpoint</div>

        <div class="card-body">
            <p>
                We provide a SPARQL Endpoint so you can query all our public datasets without registration using SPARQL.
                <a href="/sparql">Go to SPARQL</a>,
                <a href="/wiki#usage-sparql">How to use</a>
            </p>
        </div>
    </div>

    <!-- Wiki -->
    <div class="card tile">
        <div class="card-header">Documentation</div>

        <div class="card-body">
            <p>
                We maintain comprehensive documentation on our services.<br/>
                <a href="/wiki#usage">Go to Wiki</a>
            </p>
            <p>
                In the technical documentation we present the Editor App and the other components.<br/>
                <a href="/wiki#dev">Go to Wiki</a>
            </p>
            <p>
                Our changelog provides information about updates and new features.<br/>
                <a href="/changelog">Changelog</a>
            </p>
            </p>
        </div>
    </div>

    <!-- Website -->
    <div class="card tile">
        <div class="card-header">Website</div>

        <div class="card-body">
            <p>
                Our public Website is th right place if you want to browse our collections and ressources.<br/>
                <a href="/public-website">Go to CN Website</a>
            </p>
        </div>
    </div>

    <!-- Github -->
    <div class="card tile">
        <div class="card-header">Github</div>

        <div class="card-body">
            <p>
                We publish the CN Editor App source Code and our cn types data on Github<br/>
                <a href="/repository/source-code">CN Editor App Source Code</a>,
                <a href="/repository/data">CN Type Datasets</a>
            </p>
        </div>
    </div>

</div>

<style scoped>
    .tiles {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        margin-bottom: 10px;
    }

    .tiles > * {
        flex-grow: 1;
        flex-shrink: 1;
        flex-basis: 300px;
        margin: 10px;
    }

    .tile {
        width: 300px;
    }
</style>

@endsection
