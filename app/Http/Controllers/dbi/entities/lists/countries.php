<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class countries implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.countries'))
            -> select([
                // ID
                DB::raw('LOWER(id_iso3166) AS id'),
                // String
                $language.' AS string',
                // Search
                DB::raw('LOWER(CONCAT_WS("|",
                    id_iso3166,
                    en,
                    de
                )) AS search')
            ]);

        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery
                        -> orWhere('id_iso3166', $search)
                        -> orWhere('de', 'LIKE', '%'.$search.'%')
                        -> orWhere('en', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id_iso3166, "'.implode('","', array_reverse($input['id'])).'") DESC, ').
            $language.' IS NULL, '.
            $language.' ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
