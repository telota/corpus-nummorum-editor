<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class standards implements dbiInterface  { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {        
        $query = DB::table(config('dbi.tablenames.standards')) -> select([
            'id             AS id',
            'standard_de    AS name_de',
            'standard_en    AS name_en',
            'nomisma_id     AS nomisma',
            'comment        AS comment'
        ]);
        
        // Set condition if ID is given
        if (!empty($id)) { $query -> where('id', $id); }

        $dbi = $query -> get();
        $items = json_decode($dbi, TRUE);

        return $items;
    }

    public function input ($user, $input) {        
        $validation = $this -> validateInput($input);

        if(empty($validation['error'][0])) {
            $input = $validation['input'];

            $ID = DB::transaction(function () use ($input) {
                $values = [
                    'standard_de'   => $input['name_de'],
                    'standard_en'   => $input['name_en'],
                    'nomisma_id'    => $input['nomisma'],
                    'comment'       => $input['comment']
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    return DB::table(config('dbi.tablenames.standards')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.standards')) -> where('id', $input['id']) -> update($values);
                    return $input['id'];
                }
            });

            return $ID;
        }
        // Validation Error
        else { return ['error' => $validation['error']]; }
    }

    public function delete ($user, $input) {
        DB::transaction(function () use ($input) {
            DB::table(config('dbi.tablenames.standards')) -> where('id', $input['id']) -> delete();
        });

        return true;
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {
        if (empty($input['name_de'])) {
            $error[] = config('dbi.responses.validation.general.name_de');
        }
        if (empty($input['name_en'])) {
            $error[] = config('dbi.responses.validation.general.name_en');
        }

        // Return validated input
        if (empty($error)) {
            return ['input' => $input];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
