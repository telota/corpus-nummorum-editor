<?php

namespace App\Http\Controllers\dbi\import;

use App\Http\Controllers\Controller;
use Response;
use Request;


class ImportController extends Controller {

    public $resources = [
        'numismaticsorg',
    ];


    public function parse ($entity) {
        $input  = Request::post();

        // Check if required variables were provided
        if (!in_array ($entity, ['coins', 'types'])) {
            die ('Error: unknown entity "'.$entity.'" provided.');
        }
        else if (empty($input['source'])) {
            die ('Error: no source provided.');
        }

        // Handle source link
        $url = strtolower(trim($input['source']));

        if (substr($url, 0, 8) != 'https://' && substr($url, 0, 7) != 'http://') {
            die ('Error: provided source url "'.$url.'" is not valid (url must contain http:// or https://).');
        }

        $explode    = explode ('//', $url);
        $explode    = explode ('/', $explode [1]);
        $root       = $explode [0];
        $resource   = preg_replace('/[^a-z]/i', '', strtolower ($root));

        if (!in_array ($resource, $this->resources)) {
            die ('Error: Data from "'.$root.'" is not supported.');
        }

        $resource    = 'App\Http\Controllers\dbi\import\resources\\'.$resource;
        
        $provider   = new ImportProvider(new $resource());
        $dbi        = $provider -> {$entity}($url);
        
        return Response::json($dbi);
    }    
}


class ImportProvider {

    private $resource;

    function __construct (ImportInterface $resource) {
        $this -> resource = $resource;
    }

    function coins ($url) {
        return $this -> resource -> coins($url);
    }

    function types ($url)  {
        return $this -> resource -> types($url);
    }
}


interface ImportInterface {

    public function coins($url);

    public function types($url);      
}
