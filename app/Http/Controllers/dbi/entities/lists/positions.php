<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;
use Auth;


class positions implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.positions'))
            -> select([
                // ID
                'id AS id',
                // String
                'position_'.$language.' AS string',
                // Search
                DB::raw('LOWER(CONCAT_WS("|",
                    id,
                    position_de,
                    position_en
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('position_de', 'LIKE', '%'.$search.'%')
                        -> orWhere('position_en', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'position_'.$language.' IS NULL, '.
            'position_'.$language.' ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
