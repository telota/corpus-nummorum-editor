<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class objectgroups implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.objectgroups'))
            -> select([
                // ID
                'id AS id',
                // String
                'objectgroup AS string',
                // Addition
                'comment AS addition',
                // Search
                DB::raw('LOWER(CONCAT_ws("|",
                    id,
                    objectgroup,
                    description_en,
                    description_de
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('objectgroup', 'LIKE', '%'.$search.'%')
                        -> orWhere('description_en', 'LIKE', '%'.$search.'%')
                        -> orWhere('description_de', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'objectgroup IS NULL, '.
            'objectgroup ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
