<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class designs implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.designs'))
            -> select([
                // ID
                'id AS id',
                // String
                'design_'.$language.' AS string',
                // Addition
                DB::raw('CONCAT_WS(", ",
                    CASE
                        WHEN role = 2 THEN IF("'.$language.'" = "de", "MÜNZE/TYP", "COIN/TYPE")
                        WHEN role = 1 THEN IF("'.$language.'" = "de", "TYP", "TYPE")
                        ELSE IF("'.$language.'" = "de", "MÜNZE", "COIN") 
                    END,
                    IF(border_of_dots = 1, "'.($language === 'de' ? 'Perlkreis' : 'border of dots').'", null)
                ) AS addition')
                // Search
                /*DB::raw('CONCAT_ws("|",
                    legend_sort_basis,
                    LOWER(REPLACE(TRIM(keywords), ", ", "|"))
                ) AS search')*/
            ]);
        
        // Where
        if (!empty($input['id'])) {
            $query -> orWhereIn('id', $input['id']);
        } 
        if (!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('design_de', 'LIKE', '%'.$search.'%')
                        -> orWhere('design_en', 'LIKE', '%'.$search.'%')
                        -> orWhere('comment', 'LIKE', '%'.$search.'%');
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
            'design_'.$language.' IS NULL, '.
            'design_'.$language.' ASC'
        )
        -> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
