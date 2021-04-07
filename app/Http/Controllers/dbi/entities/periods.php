<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class periods implements dbiInterface  { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        $query = DB::table(config('dbi.tablenames.periods')) -> select([
            'id         AS id',
            'period_de  AS name_de',
            'period_en  AS name_en',
            'nomisma_id AS nomisma',
            DB::raw('CAST(date_from AS SIGNED) AS date_start'),
            DB::raw('CAST(date_to AS SIGNED) AS date_end'),
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
                    'period_de'     => $input['name_de'],
                    'period_en'     => $input['name_en'],
                    'nomisma_id'    => $input['nomisma'],
                    'date_from'     => $input['date_start'],
                    'date_to'       => $input['date_end']
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    return DB::table(config('dbi.tablenames.periods')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.periods')) -> where('id', $input['id']) -> update($values);
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
            DB::table(config('dbi.tablenames.periods')) -> where('id', $input['id']) -> delete();
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
        if (empty($input['name_en']) ) {
            $error[] = config('dbi.responses.validation.general.name_en');
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
