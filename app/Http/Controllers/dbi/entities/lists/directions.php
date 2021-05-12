<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class directions implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.legends_directions'))
            -> select([
                // ID
                'id AS id',
                // String
                'legend_direction AS string',
                // Image
                DB::raw('IF(image > "", CONCAT( "'.config('dbi.url.storage').'", "legend-directions/", LPAD(id, 3, 0), ".svg"), null) AS image'),
                // Search
                DB::raw('LOWER(CONCAT_ws("|",
                    legend_direction
                )) AS search')
            ]);

        // Where
        if (!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery
                        -> orWhere('id', $search)
                        -> orWhere('legend_direction', $search);
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'legend_direction IS NULL, '.
            'legend_direction ASC'
        );
        //-> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
