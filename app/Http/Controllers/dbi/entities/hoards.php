<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class hoards implements dbiInterface  { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {        
        $query = DB::table(config('dbi.tablenames.hoards').' AS h')
            -> leftJoin(config('dbi.tablenames.findspots').' AS f', 'f.id', '=', 'h.id_findspot')
            -> select([
                'h.id                   AS id',
                'h.hoard                AS name',
                'h.link                 AS link',
                'h.hoard_reference      AS reference',
                'h.hoard_description    AS description',
                'h.year_terminal        AS year',
                'h.comment              AS comment',
                'h.id_findspot          AS id_findspot',
                DB::Raw('UPPER(f.country) AS country'),
                'f.findspot             AS findspot',
                'f.latitude             AS latitude',
                'f.longitude            AS longitude'
            ]);
        
        // Set condition if ID is given
        if (!empty($id)) { $query -> where('h.id', $id); }

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
                    'id'                => $input['id'],
                    'hoard'             => $input['name'],
                    'id_findspot'       => $input['id_findspot'],
                    'Link'              => $input['link'],
                    'hoard_reference'   => $input['reference'],
                    'hoard_description' => $input['description'],
                    'year_terminal'     => $input['year'],
                    'comment'           => $input['comment']
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    return DB::table(config('dbi.tablenames.hoards')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.hoards')) -> where('id', $input['id']) -> update($values);
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
            DB::table(config('dbi.tablenames.hoards')) -> where('id', $input['id']) -> delete();
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
