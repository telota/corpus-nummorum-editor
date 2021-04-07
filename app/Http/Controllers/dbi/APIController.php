<?php

namespace App\Http\Controllers\dbi;

use App\Http\Controllers\Controller;
use App\Http\Controllers\dbi\dbiManager;
use Response;
use Request;


class APIController extends Controller {

    public function select ($entity, $id = NULL) {
        // Get Scheme if given
        $exploded_entity = explode(":", $entity);
        $entity = $exploded_entity[0];
        if(in_array($entity, ['coin', 'type'])) { $entity .= 's'; } // add s if missing
        $scheme = empty($exploded_entity[1]) ? 0 : $exploded_entity[1];

        $user = ['id' => $scheme, 'level' => 2];
        $manager = new dbiManager();
        $dbi = $manager->select($user, $entity, $id);
        
        if (empty($dbi['error'])){

            // Detailed Response (if Contents is given by entity)
            if (isset($dbi['contents'])) {

                // Add url to pagination
                $dbi['pagination']['self']   = config('dbi.url.api').$entity.(empty($dbi['pagination']['self']) ? '' : ('?'.$dbi['pagination']['self']));
                $dbi['pagination']['pageOf'] = config('dbi.url.api').$entity.(empty($dbi['pagination']['pageOf']) ? '' : ('?'.$dbi['pagination']['pageOf']));
                foreach(['firstPage', 'previousPage', 'nextPage', 'lastPage'] as $i) {
                    $dbi['pagination'][$i] = (empty($dbi['pagination'][$i]) ? null : (config('dbi.url.api').$entity.'?'.$dbi['pagination'][$i]));
                }

                // return Response
                return Response::json([
                    'pagination' => isset($dbi['pagination']) ? $dbi['pagination'] : [],
                    'where' => [
                        'accepeted' => isset($dbi['where']['accepted']) ? $dbi['where']['accepted'] : [],
                        'ignored' => isset($dbi['where']['ignored']) ? $dbi['where']['ignored'] : []
                    ],
                    'contents'  => $dbi['contents']
                ], 200);
            }
            // Minimal Response (just array of results given)
            else {
                if (!isset($dbi[0]['published'])) {
                    if(empty($id)) {
                        return Response::json([
                            'pagination' => [
                                'self'  => config('dbi.url.api').$entity,
                                'count' => count($dbi)
                            ],
                            'contents' => $dbi
                        ], 200);
                    }
                    else {
                        if(!empty($dbi)) {
                            return Response::json(['contents' => $dbi], 200);
                        }
                        else {
                            return Response::json(['error' => $this->errorMessage($id, $entity, config('dbi.responses.api.no_content'))], 404);
                        }
                    }
                }
                else {
                    if ($dbi[0]['published'] == 1) {
                        unset($dbi[0]['published']);
                        return Response::json(['contents' => $dbi], 200);
                    }
                    elseif ($dbi[0]['published'] == 3) {
                        return Response::json(['error' => $this->errorMessage($id, $entity, config('dbi.responses.api.deleted'))], 403);
                    }
                    else {
                        return Response::json(['error' => $this->errorMessage($id, $entity, config('dbi.responses.api.not_published'))], 403);
                    }
                }
            }
        }
        else {
            return Response::json($dbi, 403);
        }
    }


    public function documentation ($entity, $resource = NULL) {
        // Get Scheme if given
        $exploded_entity = explode(":", $entity);
        $entity = $exploded_entity[0];
        if(in_array($entity, ['coin', 'type'])) { $entity .= 's'; } // add s if missing
        $scheme = empty($exploded_entity[1]) ? 0 : $exploded_entity[1];

        if (in_array($entity, ['coins', 'types'])) {
            $classes = [
                'where' => 'App\Http\Controllers\dbi\entities\\'.$entity.'\request_parametric_where',
                'order_by' => 'App\Http\Controllers\dbi\entities\\'.$entity.'\request_parametric_order_by',
                'select_parametric' => 'App\Http\Controllers\dbi\entities\\'.$entity.'\request_parametric_select',
                'select_id' => 'App\Http\Controllers\dbi\entities\\'.$entity.'\request_id_select'
            ];

            if (empty($resource) || in_array($resource, array_keys($classes))) {
                $items = [];

                foreach ((empty($resource) ? $classes : [$classes[$resource]]) as $key => $class) {
                    ${$key} = New $class();
                    $items[$key] = array_keys(${$key} -> instructions(['id' => $scheme, 'level' => 2]));
                }
            }
            else {
                die(abort(404, 'Call to unknown resource "'.$resource.'"!'));
            }
        }
        else {
            die(abort(404, 'Call to unknown entity "'.$entity.'"!'));
        }
        
        return Response::json ($items);
    }

    // ---------------------------------------------------------------------------------------------------
    public function errorMessage ($id, $entity, $errors) {
        foreach ($errors AS $language => $text) {
            $message[$language] = empty($id) ? ucfirst($text) : ('cn '.$entity.' '.$id.' '.$text);
        }
        return $message;
    }
}
