<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class periods implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.periods'))
            -> select([
                // ID
                'id AS id',
                // String
                'period_'.$language.' AS string',
                // Addition
                DB::raw('
                    CASE
                        WHEN SIGN(date_from) = -1 && SIGN(date_to) = -1 
                            THEN CONCAT(date_from * (-1), "–", date_to * (-1), " ", IF("'.$language.'" = "de", "v. Chr.", "BC"))
                        WHEN SIGN(date_from) = -1 && SIGN(date_to) = 1 
                            THEN CONCAT(date_from * (-1), " ", IF("'.$language.'" = "de", "v. Chr.", "BC"), " – ", date_to, " ", IF("'.$language.'" = "de", "n. Chr.", "AD"))
                        ELSE CONCAT(date_from, "–", date_to, " ", IF("'.$language.'" = "de", "n. Chr.", "AD"))
                    END
                AS addition'),
                // Search
                DB::raw('LOWER(CONCAT_ws("|",
                    id,
                    period_en,
                    period_de,
                    nomisma_id
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('period_en', 'LIKE', '%'.$search.'%')
                        -> orWhere('period_de', 'LIKE', '%'.$search.'%')
                        -> orWhere('nomisma_id', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'date_from ASC, '.
            'date_to ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
