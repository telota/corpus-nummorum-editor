<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;
use Auth;


class owners implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.owners'))
            -> select([
                // ID
                'id AS id',
                // String
                'owner AS string',
                // Addition
                DB::raw('CONCAT_WS(", ",
                    UPPER(country),
                    city
                ) AS addition'),
                // Search
                DB::raw('LOWER(CONCAT_WS("|",
                    id,
                    owner,
                    owner_type,
                    city,
                    country
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('owner', 'LIKE', '%'.$search.'%')
                        -> orWhere('owner_type', 'LIKE', '%'.$search.'%')
                        -> orWhere('city', 'LIKE', '%'.$search.'%')
                        -> orWhere('country', $search);
                });
            }
        }

        // Hide non public user for average user
        if(empty(Auth::user()) || Auth::user()->access_level < config('dbi.permissions.owners.read')) {
            $query -> where('is_name_public', 1);
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'owner IS NULL, '.
            'owner ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
