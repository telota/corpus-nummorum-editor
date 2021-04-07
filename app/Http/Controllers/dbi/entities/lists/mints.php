<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class mints implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.mints').' AS m')
            -> leftJoin(config('dbi.tablenames.regions_to_subregions').' AS sr', 'sr.nomisma_id_region', '=', 'm.imported_nomisma_subregion')
            -> leftJoin(config('dbi.tablenames.regions').' AS r', 'r.id', '=', 'sr.id_region')
            -> select([
                // ID
                'm.id AS id',
                // String
                'm.mint AS string',
                // Addition
                DB::raw('CONCAT_WS(
                    ", ",
                    r.'.$language.',
                    (
                        SELECT mt.mint_name
                        FROM cn_data.nomisma_mints_text AS mt 
                        WHERE mt.nomisma_id_mint = m.nomisma_id && 
                        language = "'.$language.'"
                    )
                ) AS addition'),
                // Search
                DB::raw('LOWER(CONCAT_ws("|",
                    m.id,
                    m.mint,
                    m.nomisma_id,
                    (
                        SELECT GROUP_CONCAT(mt.mint_name SEPARATOR "|")
                        FROM cn_data.nomisma_mints_text AS mt 
                        WHERE mt.nomisma_id_mint = m.nomisma_id
                    )
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('m.id', $search)
                        -> orWhere('m.mint', 'LIKE', '%'.$search.'%')
                        -> orWhere('m.nomisma_id', 'LIKE', '%'.$search.'%')
                        -> orWhere(DB::Raw('(
                            SELECT GROUP_CONCAT(mt.mint_name SEPARATOR "|")
                            FROM cn_data.nomisma_mints_text AS mt 
                            WHERE mt.nomisma_id_mint = m.nomisma_id
                        )'), 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(m.id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'm.mint IS NULL, '.
            'm.mint ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
