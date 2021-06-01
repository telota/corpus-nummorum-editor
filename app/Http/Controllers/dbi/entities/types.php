<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;

use App\Http\Controllers\dbi\handler\complex_select;
use App\Http\Controllers\dbi\entities\types\input;
use App\Http\Controllers\dbi\entities\types\connect;


class types implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        $handler = new complex_select;
        return $handler -> handleRequest('types', $user, $id);
    }


    public function input ($user, $input) {
        $validation = $input['id'] === 'new' ? ['input' => $input] : $this -> validateInput($input);

        if(empty($validation['error'][0])) {
            $input = $validation['input'];

            $handler = new input;
            return $handler -> handle($user, $input);
        }
        // Validation Error
        else { return ['error' => $validation['error']]; }
    }

    public function delete ($user, $input) {
        DB::transaction(function () use ($input) {
            DB::table(config('dbi.tablenames.types')) -> where('id', $input['id']) -> update(['publication_state' => 3]);
            DB::table(config('dbi.tablenames.index_types'))->where('id', $input['id'])->delete();
        });

        return true;
    }

    public function connect ($user, $input) {
        /*$handler = new connect;

        if ($input ['mode'] === 'link') {
            return $handler -> link($user, $input);
        }
        else if ($input ['mode'] === 'unlink') {
            return $handler -> unlink($user, $input);
        }
        else {
            die (abort(404, 'Not supported!'));
        }*/
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {

        // Return validated input
        if (empty($error)) {
            return ['input' => $input];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
