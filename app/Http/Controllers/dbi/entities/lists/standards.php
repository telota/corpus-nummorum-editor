<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class standards implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.standards'))
            -> select([
                // ID
                'id AS id',
                // String
                DB::raw('IFNULL(standard_'.$language.', standard_de) AS string'),
                // Search
                DB::raw('LOWER(CONCAT_ws("|",
                    id,
                    standard_en,
                    standard_de,
                    nomisma_id
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('standard_en', 'LIKE', '%'.$search.'%')
                        -> orWhere('standard_de', 'LIKE', '%'.$search.'%')
                        -> orWhere('nomisma_id', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'standard_'.$language.' IS NULL, '.
            'standard_'.$language.' ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
