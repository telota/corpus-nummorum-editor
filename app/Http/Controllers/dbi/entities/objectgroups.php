<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class objectgroups implements dbiInterface  { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        $select = [
            'id             AS id',
            'objectgroup    AS name',                
            'description_de AS description_de',
            'description_en AS description_en',
            'comment        AS comment'
        ];

        if ($user['level'] > 9) { 
            $select[] = 'id_creator AS creator';
            $select[] = 'id_editor AS editor';
            $select[] = 'created_at';
            $select[] = 'updated_at';
        }

        $query = DB::table(config('dbi.tablenames.objectgroups')) -> select($select);
        
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

            $ID = DB::transaction(function () use ($input, $user) {
                $values = [
                    'id'                => $input['id'],
                    'objectgroup'       => $input['name'],
                    'description_de'    => $input['description_de'],
                    'description_en'    => $input['description_en'],
                    'comment'           => $input['comment'],
                    'id_editor'         => $user['id']
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    $input['id_creator'] = $user['id'];
                    return DB::table(config('dbi.tablenames.objectgroups')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.objectgroups')) -> where('id', $input['id']) -> update($values);
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
            DB::table(config('dbi.tablenames.objectgroups')) -> where('id', $input['id']) -> delete();
        });

        return true;
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {
        if (empty($input['name'])) {
            $error[] = config('dbi.responses.validation.general.name');
        }

        // Return validated input
        if (empty($error)) {
            return ['input' => $input];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
