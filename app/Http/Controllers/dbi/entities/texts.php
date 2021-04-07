<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class texts implements dbiInterface  { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {        
        $query = DB::table(config('dbi.tablenames.texts')) -> select([
            'id',
            'pagename',
            'language',
            'pagecontent'
        ]);
        
        // Set condition if ID is given
        if (!empty($id)) { $query -> where('id', $id); }

        $dbi = $query -> orderBy('pagename', 'asc') -> orderBy('language', 'asc') -> get();
        $items = json_decode($dbi, TRUE);

        return $items;
    }

    public function input ($user, $input) {        
        $validation = $this -> validateInput($input);

        if(empty($validation['error'][0])) {
            $input = $validation['input'];

            $ID = DB::transaction(function () use ($input) {
                $values = [
                    'pagename'      => $input ['pagename'], 
                    'language'      => $input ['language'],
                    'pagecontent'   => $input ['pagecontent']
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    return DB::table(config('dbi.tablenames.texts')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.texts')) -> where('id', $input['id']) -> update($values);
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
            DB::table(config('dbi.tablenames.texts')) -> where('id', $input['id']) -> delete();
        });

        return true;
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {
        /*if (empty($input['name_de'])) {
            $error[] = config('dbi.responses.validation.general.name_de');
        }*/

        // Return validated input
        if (empty($error)) {
            return ['input' => $input];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
