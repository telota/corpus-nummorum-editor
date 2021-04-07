<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class persons implements dbiInterface  { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {        
        $query = DB::table(config('dbi.tablenames.persons')) -> select([
            'id                 AS id',
            'person             AS name',
            'nomisma_id_person  AS nomisma_name',
            'nomisma_id_role    AS nomisma_role',
            'alias              AS alias',
            DB::raw('IFNULL(is_authority, 0) AS is_authority'),
            'position           AS position',
            'definition         AS definition',
            DB::raw('CAST(date_start AS SIGNED) AS date_start'),
            DB::raw('CAST(date_end AS SIGNED) AS date_end'),
            'date_string        AS active_time',
            DB::raw('CAST(caesar_start AS SIGNED) AS caesar_start'),
            'caesar_string      AS caesar_uncertain',
            DB::raw('CAST(augustus_start AS SIGNED) AS augustus_start'),
            'augustus_string    AS augustus_uncertain',
            'comment            AS comment'
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
                    'id'                => $input['id'],
                    'nomisma_id_person' => $input['nomisma_name'],
                    'nomisma_id_role'   => $input['nomisma_role'],
                    'person'            => $input['name'],
                    'definition'        => $input['definition'],
                    'position'          => $input['position'],
                    'date_start'        => $input['date_start'],
                    'date_end'          => $input['date_end'],
                    'date_string'       => $input['active_time'],
                    'caesar_start'      => $input['caesar_start'],
                    'caesar_string'     => $input['caesar_uncertain'],
                    'augustus_start'    => $input['augustus_start'],
                    'augustus_string'   => $input['augustus_uncertain'],
                    'alias'             => $input['alias'],
                    'comment'           => $input['comment'],
                    'is_authority'      => empty($input['is_authority']) ? 0 : 1
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    return DB::table(config('dbi.tablenames.persons')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.persons')) -> where('id', $input['id']) -> update($values);
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
            DB::table(config('dbi.tablenames.persons')) -> where('id', $input['id']) -> delete();
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
        if ($input['date_start'] > $input['date_end']) {
            $error[] = config('dbi.responses.validation.general.end_start');
        }
        if (($input['date_start'] !== null && $input['date_start'] == 0) || ($input ['date_end'] !== null && $input ['date_end'] == 0)) {
            $error[] = config('dbi.responses.validation.general.year0');
        }

        // Return validated input
        if (empty($error)) {
            return ['input' => $input];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
