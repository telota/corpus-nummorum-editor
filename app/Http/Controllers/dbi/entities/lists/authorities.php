<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class authorities implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.authorities').' AS m')
            -> select([
                // ID
                'id AS id',
                // String
                'authority_'.$language.' AS string',
                // Search
                DB::raw('LOWER(CONCAT_ws("|",
                    id,
                    authority_en,
                    authority_de,
                    nomisma_id
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('authority_en', 'LIKE', '%'.$search.'%')
                        -> orWhere('authority_de', 'LIKE', '%'.$search.'%')
                        -> orWhere('nomisma_id', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'authority_'.$language.' IS NULL, '.
            'authority_'.$language.' ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
