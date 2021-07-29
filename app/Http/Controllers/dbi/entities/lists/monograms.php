<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class monograms implements listsInterface  {

    public function select ($user, $input, $language) {
        $query = DB::table(config('dbi.tablenames.monograms'))
            -> select([
                // ID
                'id AS id',
                // String
                DB::raw('IFNULL(REPLACE(lettercomb, " ", ""), "-") AS string'),
                // Addition
                DB::raw('
                    REPLACE(
                        REPLACE(
                            LOWER(
                                IFNULL(
                                    monogram,
                                    REPLACE(image, ".svg", "")
                                )
                            ),
                        "mon_", ""),
                    "_", " ") AS addition
                '),
                // Image
                DB::raw('IF(image > "", CONCAT("'.config('dbi.url.storage').'Monograms/", image), null) AS image'),
                // Search
                /*DB::raw('LOWER(CONCAT_ws("|",
                    controlmark,
                    comment
                )) AS search')*/
            ]);

        // Where
        /*if (!empty($input['id'])) {
            $query -> orWhereIn('id', $input['id']);
        }*/
        if (!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search, $input) {
                    $subquery
                        -> orWhere('id', $search)
                        -> orWhere('monogram', 'LIKE', '%'.$search.'%')
                        -> orWhere(function ($lettersubquery) use ($search) {
                            // explode potential Letter combination (unicode!)
                            $letters = preg_split('~~u', $search, -1, PREG_SPLIT_NO_EMPTY);
                            foreach($letters AS $letter) {
                                $lettersubquery -> where('lettercomb', 'LIKE', '%'.$letter.'%');
                            }
                        });
                    if (!empty($input['id'])) {
                        $subquery -> orWhereIn('id', $input['id']);
                    }
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'lettercomb IS NULL, '.
            'lettercomb ASC'
        )
        -> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
