<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class dies implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.dies').' AS di')
            -> leftJoin(config('dbi.tablenames.legends').' AS l', 'l.id', '=', 'di.id_legend')
            -> leftJoin(config('dbi.tablenames.designs').' AS d', 'd.id', '=', 'di.id_design')
            -> select([
                // ID
                'di.id AS id',
                // String
                'di.name_die AS string',
                // Addition
                'di.comment AS addition',
                // Search
                /*DB::raw('CONCAT_ws("|",
                    legend_sort_basis,
                    LOWER(REPLACE(TRIM(keywords), ", ", "|"))
                ) AS search')*/
            ]);

        // Where
        if (!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search, $input) {
                    $subquery
                        -> orWhere('di.id', $search)
                        -> orWhere('di.name_die', 'LIKE', '%'.$search.'%')
                        -> orWhere('di.comment', 'LIKE', '%'.$search.'%')
                        -> orWhere('d.design_de', 'LIKE', '%'.$search.'%')
                        -> orWhere('d.design_en', 'LIKE', '%'.$search.'%')
                        -> orWhere('d.comment', 'LIKE', '%'.$search.'%')
                        -> orWhere('l.legend', 'LIKE', '%'.$search.'%')
                        -> orWhere('l.legend_sort_basis', 'LIKE', '%'.$search.'%')
                        -> orWhere('l.keywords', 'LIKE', '%'.$search.'%');
                    if (!empty($input['id'])) {
                        $subquery -> orWhereIn('di.id', $input['id']);
                    }
                });
            }
        }

        // Side
        if (!empty($input['side']) && in_array($input['side'], [0, 'o', 'obverse', 1, 'r', 'reverse'])) {
            $query -> whereNotIn('di.side', [(in_array($input['side'], [1, 'r', 'reverse'])) ? 0 : 1]);
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(di.id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'di.name_die IS NULL, '.
            'di.name_die ASC'
        )
        -> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
