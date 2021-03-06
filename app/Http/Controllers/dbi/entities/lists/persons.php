<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class persons implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.persons'))
            -> select([
                // ID
                'id AS id',
                // String
                'person AS string',
                // addition
                DB::raw('CONCAT_WS(", ",
                    IF(is_authority = 1, (IF("'.$language.'" = "de", "AUTORITÄT", "AUTHORITY")), null),
                    REPLACE(nomisma_id_person, "_", " "),
                    REPLACE(alias, ";", ","),
                    IF(LENGTH(comment) > 120, CONCAT(SUBSTRING(comment, 1, 120), " ..."), comment)
                ) AS addition'),
                // Search
                DB::raw('CONCAT_ws("|",
                    person,
                    nomisma_id_person,
                    alias
                ) AS search')
            ]);

        // Where
        if (!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search, $input) {
                    $subquery
                        -> orWhere('id', $search)
                        -> orWhere('person', 'LIKE', '%'.$search.'%')
                        -> orWhere('alias', 'LIKE', '%'.$search.'%')
                        -> orWhere('nomisma_id_person', 'LIKE', '%'.$search.'%');
                    if (!empty($input['id'])) {
                        $subquery -> orWhereIn('id', $input['id']);
                    }
                });
            }
        }

        // Side
        if (!empty($input['authority'])) {
            $query -> where('is_authority', ($input['authority'] == 1 ? '1' : '0'));
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'person IS NULL, '.
            'person ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
