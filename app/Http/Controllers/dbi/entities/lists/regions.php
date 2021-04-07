<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;
use Auth;


class regions implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.regions'))
            -> select([
                // ID
                'id AS id',
                // String
                $language.' AS string',
                // Search
                DB::raw('LOWER(CONCAT_WS("|",
                    id,
                    en,
                    de
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('sr.id', $search)
                        -> orWhere('en', 'LIKE', '%'.$search.'%')
                        -> orWhere('de', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            $language.' IS NULL, '.
            $language.' ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
