<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;
use Request;


class lists implements dbiInterface  { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $entity) {
        
        if (!empty($entity)) {
            $class = 'App\Http\Controllers\dbi\entities\lists\\'.$entity;

            if (class_exists($class)) {
                $input = $this->validateInput(Request::post());
                $language = $input['language'];
                $provider = new listsProvider(new $class());
                return $provider->provide_select($user, $input, $language);
            }
            else {
                die (abort(404, 'Call to unknown entity "'.$entity.'"!'));
            }
        }
        else {
            die (abort(404, 'Please provide additional entity to specifiy lists output!'));
        }
    }

    public function input ($user, $input) {
        die (abort(404, 'Not supported!'));
    }
    public function delete ($user, $input) {
        die (abort(404, 'Not supported!'));
    }
    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }

    // Helper-Functions ------------------------------------------------------------------
    public function validateInput ($input) {
        $processed = [];
        
        // Language
        $processed['language'] = (!empty($input['language']) && trim(strtolower($input['language'])) === 'de') ? 'de' : 'en';

        foreach ($input AS $key => $val) {
            // ID
            if ($key === 'id') {
                foreach ((is_array($val) ? $val : [$val]) as $value) {
                    if ($value !== null && $value !== '') {
                        $processed['id'][] = $value;
                    }
                }
            }
            // Search
            else if ($key === 'search') {
                $processed['search'] = is_array($val) ? $val : explode(' ', trim($val));
            }
            // Other entity specific keys
            else if ($key != 'language') {
                $processed[$key] = $val;
            }
        }
        
        return $processed;
    }
}


class listsProvider {

    private $entity;

    function __construct (listsInterface $entity)       { $this -> entity = $entity; }

    function provide_select ($user, $input, $language)  { return $this -> entity -> select($user, $input, $language); }
}


interface listsInterface  {

    public function select ($user, $input, $language);
}
