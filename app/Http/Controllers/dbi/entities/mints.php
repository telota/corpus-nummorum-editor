<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class mints implements dbiInterface  { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        $query = DB::table(config('dbi.tablenames.mints').' AS m')
            -> leftJoin(config('dbi.tablenames.mints_nomisma').' AS mn', 'mn.nomisma_id_mint', '=', 'm.nomisma_id')
            -> leftJoin(config('dbi.tablenames.regions_to_subregions').' AS sr', 'sr.nomisma_id_region', '=', 'm.imported_nomisma_subregion')
            -> leftJoin(config('dbi.tablenames.regions').' AS r', 'r.id', '=', 'sr.id_region')
            -> select([
                'm.id                         AS  id',
                'm.mint                       AS  name',
                'm.nomisma_id                 AS  nomisma',
                'm.imported_nomisma_subregion AS  nomisma_subregion',
                'r.id                         AS  id_region',
                'r.de                         AS  region_de',
                'r.en                         AS  region_en',
                'm.placename                  AS  name_modern',
                DB::raw('(SELECT mt.mint_name FROM cn_data.nomisma_mints_text AS mt WHERE mt.nomisma_id_mint = m.nomisma_id && language = \'de\' ) AS name_de'),
                DB::raw('(SELECT mt.mint_name FROM cn_data.nomisma_mints_text AS mt WHERE mt.nomisma_id_mint = m.nomisma_id && language = \'en\' ) AS name_en'),
                'mn.latitude                AS latitude',
                'mn.longitude               AS longitude'
            ]);
        
        // Set condition if ID is given
        if (!empty($id)) { $query -> where('m.id', $id); }

        $dbi = $query -> get();
        $items = json_decode($dbi, TRUE);

        return $items;
    }

    public function input ($user, $input) {
        $validation = $this -> validateInput($input);

        if(empty($validation['error'][0])) {
            $input = $validation;

            $ID = DB::transaction(function () use ($input) {
                $values = [
                    'mint'          => $input['name'],
                    'nomisma_id'    => $input['nomisma'],
                    'placename'     => $input['name_modern']
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    return DB::table(config('dbi.tablenames.mints')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.mints')) -> where('id', $input['id']) -> update($values);
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
            DB::table(config('dbi.tablenames.mints')) -> where('id', $input['id']) -> delete();
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
            if (empty($input['nomisma'])) { $input['nomisma'] = null; }
            if (empty($input['name_modern'])) { $input['name_modern'] = null; }
            return $input;
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
