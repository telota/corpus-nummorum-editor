<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;
use Auth;


class subregions implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.regions_to_subregions').' AS sr')
            -> leftJoin(config('dbi.tablenames.regions').' AS r', 'r.id', '=', 'sr.id_region')
            -> select([
                // ID
                'sr.id AS id',
                // String
                DB::raw('REPLACE(sr.nomisma_id_region, "_", " ") AS string'),
                // addition
                'r.'.$language.' as addition',
                // Search
                DB::raw('LOWER(CONCAT_WS("|",
                    sr.id,
                    sr.nomisma_id_region,
                    r.en,
                    r.de
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('sr.id', $search)
                        -> orWhere('sr.nomisma_id_region', 'LIKE', '%'.$search.'%')
                        -> orWhere('r.en', 'LIKE', '%'.$search.'%')
                        -> orWhere('r.de', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(sr.id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'sr.nomisma_id_region IS NULL, '.
            'sr.nomisma_id_region ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
