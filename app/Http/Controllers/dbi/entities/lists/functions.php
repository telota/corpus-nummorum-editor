<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class functions implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.persons_functions'))
            -> select([
                // ID
                'id AS id',
                // String
                DB::raw('IFNULL(person_function_'.$language.', person_function_de) AS string'),
                // Search
                DB::raw('LOWER(CONCAT_ws("|",
                    id,
                    person_function_en,
                    person_function_de
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('person_function_en', 'LIKE', '%'.$search.'%')
                        -> orWhere('person_function_de', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'person_function_'.$language.' IS NULL, '.
            'person_function_'.$language.' ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
