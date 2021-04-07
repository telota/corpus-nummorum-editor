<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class materials implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.materials'))
            -> select([
                // ID
                'id AS id',
                // String
                'material_'.$language.' AS string',
                // Search
                DB::raw('LOWER(CONCAT_ws("|",
                    id,
                    material_en,
                    material_de,
                    nomisma_id
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('material_en', 'LIKE', '%'.$search.'%')
                        -> orWhere('material_de', 'LIKE', '%'.$search.'%')
                        -> orWhere('nomisma_id', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'material_'.$language.' IS NULL, '.
            'material_'.$language.' ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
