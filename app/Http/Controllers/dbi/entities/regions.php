<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class regions implements dbiInterface  { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {        
        $query = DB::table(config('dbi.tablenames.regions').' AS r') -> select([
            'r.id     AS id',
            'r.de     AS name_de',
            'r.en     AS name_en',
            DB::raw('(SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    "id", m.id,
                    "name", m.mint,
                    "nomisma", m.nomisma_id
                )) 
                FROM cn_data.nomisma_subregions_to_regions AS sr
                LEFT JOIN cn_data.data_mints AS m ON m.imported_nomisma_subregion = sr.nomisma_id_region
                WHERE sr.id_region = r.id && m.nomisma_id is not null
            ) AS mints')
        ]);
        
        // Set condition if ID is given
        if (!empty($id)) { $query -> where('r.id', $id); }

        $dbi = $query -> orderBy('r.de', 'asc') -> get();
        $items = json_decode($dbi, TRUE);

        foreach ($items as $key => $val) {
            $items[$key]['mints'] = json_decode ($val['mints'], TRUE);
            $column = array_column($items[$key]['mints'], 'name');
            array_multisort($column, SORT_ASC, $items[$key]['mints']);
        }

        return $items;
    }

    public function input ($user, $input) {
        die (abort(404, 'Not supported!'));
    }

    public function delete ($user, $input) {
        die (abort(404, 'Not supported!'));
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {}
}
