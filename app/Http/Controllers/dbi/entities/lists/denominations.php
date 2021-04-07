<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class denominations implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.denominations'))
            -> select([
                // ID
                'id AS id',
                // String
                'denomination_'.$language.' AS string',
                // Search
                DB::raw('LOWER(CONCAT_ws("|",
                    id,
                    denomination_en,
                    denomination_de,
                    nomisma_id,
                    comment
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('denomination_en', 'LIKE', '%'.$search.'%')
                        -> orWhere('denomination_de', 'LIKE', '%'.$search.'%')
                        -> orWhere('nomisma_id', 'LIKE', '%'.$search.'%')
                        -> orWhere('comment', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'denomination_'.$language.' IS NULL, '.
            'denomination_'.$language.' ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
