<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class owners implements dbiInterface  { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {        
        $query = DB::table(config('dbi.tablenames.owners')) -> select([
            'id             AS id',
            'owner_type     AS type',
            'owner          AS name',
            DB::Raw('UPPER(country) AS country'),
            'city           AS city',
            'link           AS link',
            //'id_user        AS  id_user',
            DB::Raw('TRIM(REPLACE(nomisma_id, "'.config('dbi.url.nomisma').'", "")) AS nomisma'),
            DB::Raw('IFNULL(is_name_public, 0)  AS  is_name_public'),
            DB::Raw('IFNULL(is_checked, 0)      AS  is_checked')
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
                    'owner_type'    => $input['type'],
                    'owner'         => $input['name'],
                    'country'       => $input['country'],
                    'city'          => $input['city'],
                    'link'          => $input['link'],
                    'nomisma_id'    => $input ['nomisma'],
                    'is_name_public'=> empty($input['is_name_public']) ? 0 : 1,
                    'is_checked'    => empty($input['is_checked']) ? 0 : 1,
                    //'id_user'       => $input ['id_user'],
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    return DB::table(config('dbi.tablenames.owners')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.owners')) -> where('id', $input['id']) -> update($values);
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
            DB::table(config('dbi.tablenames.owners')) -> where('id', $input['id']) -> delete();
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
            $input['country'] = empty($input['country']) ? null : strtolower($input['country']);
            return ['input' => $input];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
