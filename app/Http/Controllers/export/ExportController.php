<?php

namespace App\Http\Controllers\export;

use App\Http\Controllers\Controller;
use Response;
use Request;
use DB;


/*
|--------------------------------------------------------------------------
| Define the path of the required helper script (entity)
|--------------------------------------------------------------------------
|
*/

class ExportProcessor {

    public function ExtendEntity ($entity) {    // JK: Define the name(s) of the table(s)
        
        $entity = 'App\Http\Controllers\export\\helper_'.$entity.'';

        return $entity;
    }
}


/*
|--------------------------------------------------------------------------
| Connector to the required helper script via AppInterface
|--------------------------------------------------------------------------
|
*/

class ExportBuilder {

    private $entity;

    // JK: access the related helper script via the interface
    public function __construct (ExportInterface $entity) {
        $this -> entity = $entity;
    }

    public function BuildDbi () {
        return $this -> entity -> ProvideDbi ();
    }

}


/*
|--------------------------------------------------------------------------
| Controller
|--------------------------------------------------------------------------
|
*/

class ExportController extends Controller
{
    // JK: Provide DB data (*) for the requested component
    public function dbi ($entity)
    {
        // JK: Construct the path to the needed helper script
        $ExportProcessor   	= new ExportProcessor;
        $entity        		= $ExportProcessor -> ExtendEntity ($entity);

        // JK: Get the required helper script via AppInterface
        $ExportBuilder     	= new ExportBuilder (new $entity ());
        $dbi            	= $ExportBuilder -> BuildDbi ();


        // JK: Return output as json
        return Response::json ($dbi);
    }


}
