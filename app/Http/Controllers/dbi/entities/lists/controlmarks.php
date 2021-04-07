<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class controlmarks implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.controlmarks'))
            -> select([
                // ID
                'id AS id',
                // String
                'controlmark AS string',
                // Image
                DB::raw('IF(image > "", CONCAT("'.config('dbi.url.storage').'Kontrollzeichen/", image), null) AS image'),
                // Search
                DB::raw('LOWER(CONCAT_ws("|",
                    id,
                    controlmark,
                    comment
                )) AS search')
            ]);
        
        // Where
        if (!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('controlmark', 'LIKE', '%'.$search.'%')
                        -> orWhere('comment', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'controlmark IS NULL, '.
            'controlmark ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
