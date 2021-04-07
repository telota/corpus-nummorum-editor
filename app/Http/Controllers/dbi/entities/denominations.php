<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class denominations implements dbiInterface  { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        $select = [
            'id                 AS id',
            'denomination_de    AS name_de',
            'denomination_en    AS name_en',
            'nomisma_id         AS nomisma',
            'comment            AS comment'
        ];

        if ($user['level'] > 9) { 
            $select[] = 'id_creator AS creator';
            $select[] = 'id_editor AS editor';
            $select[] = 'created_at';
            $select[] = 'updated_at';
        }

        $query = DB::table(config('dbi.tablenames.denominations')) -> select($select);
        
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
                    'denomination_de'   => $input ['name_de'],
                    'denomination_en'   => $input ['name_en'],
                    'nomisma_id'        => $input ['nomisma'],
                    'comment'           => $input ['comment']
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    return DB::table(config('dbi.tablenames.denominations')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.denominations')) -> where('id', $input['id']) -> update($values);
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
            DB::table(config('dbi.tablenames.denominations')) -> where('id', $input['id']) -> delete();
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
