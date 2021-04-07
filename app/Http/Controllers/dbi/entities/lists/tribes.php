<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class tribes implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.tribes'))
            -> select([
                // ID
                'id AS id',
                // String
                DB::raw('IFNULL(tribe_'.$language.', tribe_de) AS string'),
                // Search
                DB::raw('LOWER(CONCAT_ws("|",
                    id,
                    tribe_en,
                    tribe_de,
                    nomisma_id
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('tribe_en', 'LIKE', '%'.$search.'%')
                        -> orWhere('tribe_de', 'LIKE', '%'.$search.'%')
                        -> orWhere('nomisma_id', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'tribe_'.$language.' IS NULL, '.
            'tribe_'.$language.' ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
