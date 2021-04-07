<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class copyrights implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.copyrights'))
            -> select([
                // ID
                'id AS id',
                // String
                'copyright AS string',
                // Search
                DB::raw('LOWER(CONCAT_WS("|",
                    id,
                    copyright
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('copyright', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'copyright IS NULL, '.
            'copyright ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
