<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class symbols implements dbiInterface  { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {        
        $query = DB::table(config('dbi.tablenames.symbols')) -> select([
            'id AS id',
            'symbol_de      AS name_de',
            DB::raw('IFNULL(symbol_en, symbol_de) AS name_en'),
            'comment        AS comment',
            DB::raw('IF(image IS NOT NULL, CONCAT(\'Symbols/\', image), NULL) AS image')
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
                    'symbol_de' => $input ['name_de'],
                    'symbol_en' => empty ($input ['name_en']) ? null : $input ['name_en'],
                    'image'     => $input ['image'],
                    'comment'   => empty ($input ['comment']) ? null : $input ['comment']
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    return DB::table(config('dbi.tablenames.symbols')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.symbols')) -> where('id', $input['id']) -> update($values);
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
            DB::table(config('dbi.tablenames.symbols')) -> where('id', $input['id']) -> delete();
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
            if(!empty($input['image'])) {
                $img_explode    = explode('/', $input['image']);
                $input['image'] = end($img_explode);
            }
            
            return ['input' => $input];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
