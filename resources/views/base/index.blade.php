@extends('base.layout')

@section('content')

<div style="display: flex; justify-content: center; min-height: 100%; align-items: center; margin-bottom: 50px; margin-top: -25px">
    <div>

        <div class="card" style="margin: 10px">
            <div class="card-header text-center"><h1 style="margin: 0; padding: 0">CORPUS NUMMORUM DATA</h1></div>

            <div class="card-body text-center">
                <p>
                    Hello and welcome to our CN Data Platform!
                </p>
            </div>
        </div>

        <div class="tiles">

            <!-- Editor -->
            <div class="card tile">
                <div class="card-header">CN Editor App</div>

                <div class="card-body text-justify">
                    <p>
                        The <a href="/editor">CN Editor</a> is a modern web application to create and manage the Corpus Nummorum Datasets.
                        Have a look at our <a href="/wiki#usage-editor">Wiki</a> if your want to know how to use it.
                    </p>
                    <!--<p>
                        We invite our community to <a href="/register">register</a> as an editor and join our common efforts.
                    </p>
                    <p>
                        It is open source software - feel free to adapt the <a href="/repository/source-code">source code</a> as you would like to.
                    </p>-->
                </div>
            </div>

            <!-- API -->
            <div class="card tile">
                <div class="card-header">API & Data Repository</div>

                <div class="card-body text-justify">
                    <p>
                        We provide a powerful restful json-API, so you can access all our public datasets without registration in a convinient and standardized Way.
                        Have a look at our <a href="/wiki#usage-api">Wiki</a> if your want to know how to use it.
                    </p>
                    <!--<p>
                        In addition, we publish all our CN Type Datasets as static files on <a href="/repository/data">Github</a>.
                    </p>-->
                </div>
            </div>

            <!-- sparql -->
            <div class="card tile">
                <div class="card-header">SPARQL Endpoint</div>

                <div class="card-body text-justify">
                    <p>
                        We provide a <a href="/sparql">SPARQL Endpoint</a> so you can query all our public datasets without registration.
                        Have a look at our <a href="/wiki#usage-sparql">Wiki</a> if your want to know how to use it.
                    </p>
                </div>
            </div>

            <!-- Wiki -->
            <div class="card tile">
                <div class="card-header">Documentation</div>

                <div class="card-body text-justify">
                    <p>
                        We maintain comprehensive documentation on <a href="/wiki#usage">how to use our services</a> and <a href="/wiki#dev">software aspects</a>.
                    </p>
                    <p>
                        Our <a href="/changelog">changelog</a> provides information about updates and new features.
                    </p>
                    </p>
                </div>
            </div>

            <!-- Website -->
            <div class="card tile">
                <div class="card-header">CN Website</div>

                <div class="card-body text-justify">
                    <p>
                        Our <a href="/public-website">public Website</a> is th right place if you want to browse our collections and resources.
                        In addition, you will find our newsfeed, our Coin of the Month and more information about our project.
                    </p>
                </div>
            </div>

            <!-- Github -->
            <div class="card tile">
                <div class="card-header">Github</div>

                <div class="card-body text-justify">
                    <p>
                        We publish the <a href="/repository/source-code">CN Editor App source Code</a> and our <a href="/repository/data">CN Type datasets</a> on Github for everyone free to use
                    </p>
                    <p style="font-weight: 600">
                        <b>This feature is coming soon!</b>
                    </p>
                </div>
            </div>
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
