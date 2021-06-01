<?php

namespace App\Http\Controllers\dbi;

use Request;


class dbiManager {

    public function index ($user) {
        $entities = [];
        foreach (config('dbi.permissions') as $entity => $permissions) {
            if ($user['level'] >= $permissions['read']) {
                $entities[$entity] = [
                    'link' => env('APP_URL').'/dbi/'.$entity,
                    'read' => true,
                    'writeOwn' => $user['level'] >= $permissions['write_own'],
                    'writeAll' => $user['level'] >= $permissions['write_all']
                ];
            }
        }
        ksort($entities);

        return $entities;
    }

    public function select ($user, $entity, $id) {

        $class = $this->checkEntity($entity);

        if ($this->checkUser($user, $entity, 'read')){
            $provider = new dbiProvider(new $class());
            return $provider->provide_select($user, $id);
        }
        else {
            return ['error' => config('dbi.responses.general.insufficient_permissions')];
        }
    }

    public function input ($user, $entity) {

        $class = $this->checkEntity($entity);
        $input = Request::post();

        if ($this->checkUser($user, $entity, 'write', $input)){
            $provider = new dbiProvider(new $class());
            $response = $provider->provide_input($user, $input);

            if (empty($response['error'])) {
                if (!empty($response))          $id = $response;
                else if (!empty($input['id']))  $id = $input['id'];
                else                            $id = null;

                return [
                    'success' => $this->successMessage(empty($input['id']) ? 'created' : 'updated', $id),
                    'id' => isset($response) ? $response : null,
                    'code' => empty($input['id']) ? 201 : 200
                ];
            }
            else {
                return ['error' => $this->errorMessage($response['error'])];
            }
        }
        else {
            return ['error' => config('dbi.responses.general.insufficient_permissions')];
        }
    }

    public function delete ($user, $entity) {

        $class = $this->checkEntity($entity);

        $input  = Request::post();

        if (empty($input['id'])) {
            return ['error' => config('dbi.responses.general.missing_id')];
        }
        else if ($this->checkUser($user, $entity, 'write')){
            $provider = new dbiProvider(new $class());
            $response = $provider->provide_delete($user, $input);

            $id = empty($input['id']) ? null : $input['id'];

            return [
                'success' => $this->successMessage('deleted', $input['id']),
                'id' => $input['id']
            ];
        }
        else {
            return ['error' => config('dbi.responses.general.insufficient_permissions')];
        }
    }

    public function connect ($user, $entity) {

        $class = $this->checkEntity($entity);

        $input  = Request::post();

        if ($this->checkUser($user, $entity, 'connect', $input)){
            $provider = new dbiProvider(new $class());
            $response = $provider -> provide_connect($user, $input);

            return ['success' => $this->successMessage('connected', $input['id'])];
        }
        else {
            return ['error' => config('dbi.responses.general.insufficient_permissions')];
        }
    }

    // Helper Functions ---------------------------------------------------

    public function checkEntity ($entity) {
        $class = 'App\Http\Controllers\dbi\entities\\'.$entity;

        if (class_exists($class)) {
            return $class;
        }
        else {
            die (abort(404, 'Call to unknown resource "'.$entity.'"!'));
        }
    }

    public function checkUser ($user, $entity, $mode, $input = NULL) {

        // Check if permission instructions exist
        if (empty(config('dbi.permissions.'.$entity))) {
            die (abort(404, $entity.' permission do not exist!'));
        }
        // Called Mode is select, delete or connect
        else if (in_array($mode, ['read', 'connect'])) {
            $required = config('dbi.permissions.'.$entity.'.'.$mode);
        }
        // Called Mode is write
        else if ($mode === 'write') {
            // No creator available for this entity -> write_all
            if (empty($input['creator'])) {
                $required = config('dbi.permissions.'.$entity.'.write_all');
            }
            // Creator given and is not current user -> write_all
            else if ( $input['creator'] != $user['id']) {
                $required = config('dbi.permissions.'.$entity.'.write_all');
            }
            // Creator given and is current user -> write own
            else {
                $required = config('dbi.permissions.'.$entity.'.write_own');
            }
        }
        // Throw Error if mode is unknown
        else { die (abort(404, 'Unknown Operation called!')); }

        return $user['level'] < $required ? false : true;
    }

    public function successMessage ($key, $id) {
        $id = empty($id) ? '? ' : ('ID '.$id.' ');
        foreach (config('dbi.responses.general.'.$key) AS $key => $val) {
            $message[$key] = $id.$val;
        }
        return $message;
    }

    public function errorMessage ($errors) {
        foreach ($errors AS $error) {
            foreach ($error AS $key => $val) {
                $message[$key] = (!empty($message[$key]) ? $message[$key].' ' : '').$val;
            }
        }
        return $message;
    }
}


// Provider and Interface ------------------------------------------------

class dbiProvider {

    private $entity;

    function __construct (dbiInterface $entity) { $this -> entity = $entity; }

    function provide_select ($user, $input)     { return $this -> entity -> select($user, $input); }

    function provide_input ($user, $input)      { return $this -> entity -> input($user, $input); }

    function provide_delete ($user, $input)     { return $this -> entity -> delete($user, $input); }

    function provide_connect ($user, $input)    { return $this -> entity -> connect($user, $input); }
}


interface dbiInterface  {

    // Controller-Functions
    public function select ($user, $id);

    public function input ($user, $input);

    public function delete ($user, $input);

    public function connect ($user, $input);

    // Helper-Functions
    public function validateInput ($input);
}
