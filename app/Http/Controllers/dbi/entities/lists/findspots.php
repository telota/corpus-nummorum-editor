<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class findspots implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.findspots'))
            -> select([
                // ID
                'id AS id',
                // String
                'findspot AS string',
                // Addition
                DB::raw('CONCAT_WS(", ",
                    UPPER(country),
                    alias
                ) AS addition'),
                // Search
                DB::raw('LOWER(CONCAT_ws("|",
                    id,
                    findspot,
                    country,
                    alias,
                    comment,
                    geonames_id
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('findspot', 'LIKE', '%'.$search.'%')
                        -> orWhere('alias', 'LIKE', '%'.$search.'%')
                        -> orWhere('comment', 'LIKE', '%'.$search.'%')
                        -> orWhere('country', $search)
                        -> orWhere('geonames_id', $search);
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'findspot IS NULL, '.
            'findspot ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
