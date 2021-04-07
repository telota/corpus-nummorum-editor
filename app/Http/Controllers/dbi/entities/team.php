<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class team implements dbiInterface  { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {        
        $query = DB::table(config('dbi.tablenames.team')) -> select([
            'id',
            'lastname',
            'firstname',
            'title',
            DB::raw('SUBSTRING(joined_at, 1, 10) AS joined_at'),
            DB::raw('IF(left_at IS NOT NULL, SUBSTRING(left_at, 1, 10), null) AS left_at'),
            'text_de',
            'text_en',
            'image',
            'id_user'
        ]);
        
        // Set condition if ID is given
        if (!empty($id)) { $query -> where('id', $id); }

        $dbi = $query -> orderBy('lastname', 'asc') -> get();
        $items = json_decode($dbi, TRUE);

        return $items;
    }

    public function input ($user, $input) {        
        $validation = $this -> validateInput($input);

        if(empty($validation['error'][0])) {
            $input = $validation['input'];

            $ID = DB::transaction(function () use ($input) {
                $values = [
                    'lastname'      => $input['lastname'], 
                    'firstname'     => $input['firstname'],
                    'title'         => $input['title'],
                    'joined_at'     => $input['joined_at'],
                    'left_at'       => $input['left_at'],
                    'text_de'       => $input['text_de'],
                    'text_en'       => $input['text_en'],
                    'image'         => $input['image'],
                    'id_user'       => $input['id_user'],
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    return DB::table(config('dbi.tablenames.team')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.team')) -> where('id', $input['id']) -> update($values);
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
            DB::table(config('dbi.tablenames.team')) -> where('id', $input['id']) -> delete();
        });

        return true;
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {
        if (empty($input['lastname']) || empty($input['firstname'])) {
            $error[] = config('dbi.responses.validation.team.name');
        }
        if (empty($input['text_de']) || empty($input['text_en'])) {
            $error[] = config('dbi.responses.validation.team.text');
        }
        if (empty($input['joined_at'])) {
            $error[] = config('dbi.responses.validation.team,joined');
        }

        // Return validated input
        if (empty($error)) {
            return ['input' => $input];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
