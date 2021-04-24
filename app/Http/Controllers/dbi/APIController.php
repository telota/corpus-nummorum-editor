<?php

namespace App\Http\Controllers\dbi;

use App\Http\Controllers\Controller;
use App\Http\Controllers\dbi\dbiManager;
use Response;
use Request;
use App\Http\Controllers\dbi\DataConverter;


class APIController extends Controller {

    public function select ($entity, $id = NULL, $extension = NULL) {
        $handled = $this->handleEntity($entity);
        $entity = $handled['entity'];
        $scheme = $handled['scheme'];
        $user = ['id' => $handled['user'], 'level' => 2];
        $api = env('APP_API').'/';

        $manager = new dbiManager();
        $dbi = $manager->select($user, $entity, $id);

        if (empty($dbi['error'])){

            // Detailed Response (if key "contents" is given by entity)
            if (isset($dbi['contents'])) {

                $contents = $dbi['contents'];

                // Remap list of links if scheme is not website
                if (empty($id) && $scheme !== 'website') {
                    $contents = array_map(function ($item) use ($entity) {
                        $split = explode('/', $item['self']);
                        return env('APP_URL').'/'.'cn_'.rtrim($entity, 's').'_'.end($split).'.jsonld';
                    }, $contents);
                }

                // Remove state_public from pagination
                foreach ($dbi['pagination'] as $key => $val) {
                    if (!is_array($val)) {
                        $val = str_replace('state_public=1', '', $val);
                        $val = str_replace('&&', '&', $val);
                        $dbi['pagination'][$key] = rtrim($val, '&');
                    }
                }

                // Add url to pagination
                $dbi['pagination']['self']   = $api.$entity.(empty($dbi['pagination']['self']) ? '' : ('?'.$dbi['pagination']['self']));
                $dbi['pagination']['pageOf'] = $api.$entity.(empty($dbi['pagination']['pageOf']) ? '' : ('?'.$dbi['pagination']['pageOf']));
                foreach(['firstPage', 'previousPage', 'nextPage', 'lastPage'] as $i) {
                    $dbi['pagination'][$i] = (empty($dbi['pagination'][$i]) ? null : ($api.$entity.'?'.$dbi['pagination'][$i]));
                }

                // AcceptedKeys
                unset($dbi['where']['accepted']['state_public']);
                $dbi['pagination']['acceptedParameters'] = empty($dbi['where']['accepted']) ? [] : $dbi['where']['accepted'];

                // return Response
                return Response::json([
                    'meta' => $this->createMeta($entity),
                    'pagination' => isset($dbi['pagination']) ? $dbi['pagination'] : [],
                    'contents'  => $contents
                ], 200);
            }

            // Minimal Response (just array of results given)
            else {
                // Dataset has no key "published" (secondary entity)
                if (!isset($dbi[0]['published'])) {
                    // Specific ID is requested
                    if(empty($id)) {
                        return Response::json([
                            'pagination' => [
                                'self'  => $api.$entity,
                                'count' => count($dbi)
                            ],
                            'contents' => $dbi
                        ], 200);
                    }
                    // List is rquested
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
                    // Dataset is published
                    if ($dbi[0]['published'] == 1) {
                        unset($dbi[0]['published']);
                        // generic Request
                        if (empty($extension)) {
                            return Response::json(['contents' => $dbi], 200);
                        }
                        // Dataset is request in specific file format
                        else {
                            $converter = new DataConverter;
                            return $converter->output($entity, $id, $extension, $dbi[0]);
                        }
                    }
                    // Dataset is deleted
                    elseif ($dbi[0]['published'] == 3) {
                        return Response::json(['error' => $this->errorMessage($id, $entity, config('dbi.responses.api.deleted'))], 403);
                    }
                    // Dataset is no published yet/anymore
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

    public function documentation ($entity) {
        $keys = $this->getRequestKeys($entity);
        return Response::json($keys, 200);
    }

    public function parameters ($entity) {
        $keys = $this->getRequestKeys($entity);

        $where = [];
        foreach ($keys['where'] as $key) {
            // Exclude private Keys and publications state
            if (strpos($key, 'private') === false && $key !== 'state_public') {
                //Floats
                if (substr($key, 0, 6) === 'weight' || substr($key, 0, 8) === 'diameter') {
                    $where[$key] = 'Float';
                }
                // Signed Integer
                else if (substr($key, 0, 4) === 'date') {
                    $where[$key] = 'Integer, signed';
                }
                // Boolean
                else if (substr($key, 0, 4) === 'has_' || substr($key, 0, 6) === 'state_' || $key === 'imported') {
                    $where[$key] = 'Integer (Boolean), 1/0';
                }
                // Unsigned Integer
                else if (substr($key, 0, 2) === 'id' || substr($key, 0, 4) === 'r_id' || substr($key, 0, 4) === 'l_id' || $key === 'axis' ) {
                    $where[$key] = 'Integer, unsigned';
                }
                // Strings
                else {
                    $where[$key] = 'String';
                }
            }
        }

        $sorters = [];
        foreach ($keys['group_by'] as $key) {
            $sorters[$key] = [
                'asc' => 'sort_by='.$key.'.ASC',
                'desc' => 'sort_by='.$key.'.DESC'
            ];
        }

        return Response::json([
            'queryParameters' => $where,
            'sortingParameters' => $sorters
        ], 200);
    }


    // ---------------------------------------------------------------------------------------------------
    public function handleEntity ($entity) {
        $split = explode(":", $entity);
        $entity = $split[0];

        // Loop over permission to get array of available entities
        $allowed_entities = [];
        foreach (config('dbi.permissions') as $key => $val) {
            if ($val['read'] < 3) {
                $allowed_entities[] = $key;
            }
        };

        // add s if missing
        if (substr($entity, -1) !== 's' && !in_array($entity, $allowed_entities)) {
            $entity .= 's';
        }

        if (!in_array($entity, $allowed_entities)) {
            die(abort(404, 'Call to unknown entity "'.$entity.'"!'));
        }

        $scheme = empty($split[1]) ? 0 : $split[1];

        return [
            'entity' => $entity,
            'scheme' => $scheme,
            'user' => $scheme === 'website' ? 1 : 0
        ];
    }

    public function errorMessage ($id, $entity, $errors) {
        foreach ($errors AS $language => $text) {
            $message[$language] = empty($id) ? ucfirst($text) : ('cn '.$entity.' '.$id.' '.$text);
        }
        return $message;
    }

    public function getRequestKeys ($entity) {
        $handled = $this->handleEntity($entity);
        $entity = $handled['entity'];
        $user = $handled['user'];

        if (in_array($entity, ['coins', 'types'])) {
            $classes = [
                'where' => 'App\Http\Controllers\dbi\entities\\'.$entity.'\request_parametric_where',
                'group_by' => 'App\Http\Controllers\dbi\entities\\'.$entity.'\request_parametric_order_by',
                'select_parametric' => 'App\Http\Controllers\dbi\entities\\'.$entity.'\request_parametric_select',
                'select_ id' => 'App\Http\Controllers\dbi\entities\\'.$entity.'\request_id_select'
            ];

            foreach ($classes as $key => $class) {
                if (empty($resource) || $resource === $key) {
                    ${$key} = New $class();
                    $items[$key] = array_keys(${$key}->instructions(['id' => $user, 'level' => 2]));
                    sort($items[$key]);
                }
            }
        }
        else {
            die(abort(404, 'Call to unknown entity "'.$entity.'"!'));
        }

        return $items;
    }

    public function createMeta ($entity) {
        $api = env('APP_API').'/';

        return [
            'documentation' => env('APP_URL').'/wiki#usage-api',
            'availableParameters' => $api.'parameters/'.$entity
        ];
    }
}
