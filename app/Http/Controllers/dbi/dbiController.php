<?php

namespace App\Http\Controllers\dbi;

use App\Http\Controllers\Controller;
use App\Http\Controllers\dbi\dbiManager;
use Response;
use Auth;


class dbiController extends Controller {

    public function select ($entity, $id = NULL) {

        $user = $this->identitifyUser();
        $manager = new dbiManager();
        $dbi = $manager->select($user, $entity, $id);

        if (empty($dbi['error'])){

            // Detailed Response (if Contents is given by entity)
            if(isset($dbi['contents'])) {

                // Add url to pagination
                $dbi['pagination']['self']   = '/dbi/'.$entity.(empty($dbi['pagination']['self']) ? '' : ('?'.$dbi['pagination']['self']));
                $dbi['pagination']['pageOf'] = '/dbi/'.$entity.(empty($dbi['pagination']['pageOf']) ? '' : ('?'.$dbi['pagination']['pageOf']));
                foreach(['firstPage', 'previousPage', 'nextPage', 'lastPage'] as $i) {
                    $dbi['pagination'][$i] = (empty($dbi['pagination'][$i]) ? null : ('/dbi/'.$entity.'?'.$dbi['pagination'][$i]));
                }

                $response = [
                    'pagination' => isset($dbi['pagination']) ? $dbi['pagination'] : [],
                    'where' => [
                        'accepeted' => isset($dbi['where']['accepted']) ? $dbi['where']['accepted'] : [],
                        'ignored' => isset($dbi['where']['ignored']) ? $dbi['where']['ignored'] : []
                    ],
                    'contents'  => $dbi['contents']
                ];
            }
            // Minimal Response (just array of results given)
            else {
                $response = [
                    'pagination' => ['count' => count($dbi)],
                    'contents' => $dbi
                ];
            }

            return Response::json($response, 200);
        }
        else {
            return Response::json($dbi, 200);
        }
    }

    public function input ($entity) {

        $user   = $this->identitifyUser();

        $manager = new dbiManager();
        $response = $manager->input($user, $entity);

        if (isset($response['success'])) {
            return Response::json([
                'success' => $response['success'],
                'id' => $response['id']
            ], $response['code']);
        }
        else {
            return Response::json($response, 200);
        }
    }

    public function delete ($entity) {

        $user   = $this->identitifyUser();

        $manager = new dbiManager();
        $response = $manager->delete($user, $entity);

        if (isset($response['success'])) {
            return Response::json([
                'success' => $response['success'],
                'id' => $response['id']
            ], 200);
        }
        else {
            return Response::json($response, 200);
        }
    }

    public function connect ($entity) {

        $user   = $this->identitifyUser();

        $manager = new dbiManager();
        $response = $manager->connect($user, $entity);

        if (isset($response['success'])) {
            return Response::json($response, 200);
        }
        else {
            return Response::json($response, 200);
        }
    }

    public function index () {

        $user   = $this->identitifyUser();

        $manager = new dbiManager();
        $response = $manager->index($user);

        return Response::json($response, 200);
    }

    // Helper Functions ---------------------------------------------------

    public function identitifyUser () {
        return [
            'id' => Auth::user()->id,
            'level' => Auth::user()->access_level
        ];
    }
}
