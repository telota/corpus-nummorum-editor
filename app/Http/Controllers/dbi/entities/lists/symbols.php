<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class symbols implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.symbols'))
            -> select([
                // ID
                'id AS id',
                // String
                DB::raw('IFNULL(symbol_'.$language.', symbol_de) AS string'),
                // Addition
                'comment AS addition',
                // Image
                DB::raw('IF(image > "", CONCAT("'.config('dbi.url.storage').'Symbols/", image), null) AS image'),
                // Search
                DB::raw('LOWER(CONCAT_ws("|",
                    id,
                    symbol_en,
                    symbol_de,
                    comment
                )) AS search')
            ]);

        // Where
        /*if (!empty($input['id'])) {
            $query -> orWhereIn('id', $input['id']);
        }*/
        if (!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery
                        -> orWhere('id', $search)
                        -> orWhere('symbol_en', 'LIKE', '%'.$search.'%')
                        -> orWhere('symbol_de', 'LIKE', '%'.$search.'%')
                        -> orWhere('comment', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'symbol_'.$language.' IS NULL, '.
            'symbol_'.$language.' ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
