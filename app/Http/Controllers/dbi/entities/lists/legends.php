<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class legends implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.legends'))
            -> select([
                // ID
                'id AS id',
                // String
                'legend AS string',
                // Addition
                DB::raw('CONCAT_WS(", ",
                    CASE
                        WHEN is_type = 2 THEN IF("'.$language.'" = "de", "MÜNZE/TYP", "COIN/TYPE")
                        WHEN is_type = 1 THEN IF("'.$language.'" = "de", "TYP", "TYPE")
                        ELSE IF("'.$language.'" = "de", "MÜNZE", "COIN")
                    END,
                    CASE
                        WHEN side = 0 THEN "Obv."
                        WHEN side = 1 THEN "Rev."
                        ELSE "Obv./Rev."
                    END,
                    LOWER(keywords)
                ) AS addition'),
                // Image
                DB::raw('IF(id_legend_direction IS NOT NULL, CONCAT( "'.config('dbi.url.storage').'", "legend-directions/", LPAD(id_legend_direction, 3, 0), ".svg"), null) AS image'),
                // Search
                /*DB::raw('CONCAT_ws("|",
                    legend_sort_basis,
                    LOWER(REPLACE(TRIM(keywords), ", ", "|"))
                ) AS search')*/
            ]);

        // Where
        /*if (!empty($input['id'])) {
            $query -> orWhereIn('id', $input['id']);
        }*/
        if (!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search, $input) {
                    $subquery
                        -> orWhere('id', $search)
                        -> orWhere('legend', 'LIKE', '%'.$search.'%')
                        -> orWhere('legend_sort_basis', 'LIKE', '%'.$search.'%')
                        -> orWhere('keywords', 'LIKE', '%'.$search.'%');
                    if (!empty($input['id'])) {
                        $subquery -> orWhereIn('id', $input['id']);
                    }
                });
            }
        }

        // Side
        if (!empty($input['side']) && in_array($input['side'], [0, 'o', 'obverse', 1, 'r', 'reverse'])) {
            $query -> whereNotIn('side', [(in_array($input['side'], [1, 'r', 'reverse'])) ? 0 : 1]);
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'legend_sort_basis IS NULL, '.
            'legend_sort_basis ASC'
        )
        -> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
