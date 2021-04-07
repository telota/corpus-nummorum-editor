<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class hoards implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.hoards').' AS h')
            -> leftJoin(config('dbi.tablenames.findspots').' AS f', 'f.id', '=', 'h.id_findspot')
            -> select([
                // ID
                'h.id AS id',
                // string
                'h.hoard AS string',
                // addition
                DB::raw('CONCAT_WS(", ",
                    UPPER(f.country),
                    f.findspot
                ) AS addition'),
                // Search
                DB::raw('LOWER(CONCAT_ws("|",
                    h.id,
                    h.hoard,
                    h.hoard_description,
                    f.findspot,
                    f.country,
                    f.alias,
                    f.comment,
                    f.geonames_id
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('h.hoard', 'LIKE', '%'.$search.'%')
                        -> orWhere('h.hoard_description', 'LIKE', '%'.$search.'%')
                        -> orWhere('f.findspot', 'LIKE', '%'.$search.'%')
                        -> orWhere('f.alias', 'LIKE', '%'.$search.'%')
                        -> orWhere('f.comment', 'LIKE', '%'.$search.'%')
                        -> orWhere('f.country', $search)
                        -> orWhere('f.geonames_id', $search);
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(h.id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'h.hoard IS NULL, '.
            'h.hoard ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
