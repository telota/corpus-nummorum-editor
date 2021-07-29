<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;


class references implements listsInterface  {

    public function select ($user, $input, $language) {
        $select = ['zotero_id AS id'];

        if (!empty($input['reduced']) && $input['reduced'] == 1) {
            $select[] = DB::raw('TRIM(CONCAT_WS(" ",
                IF(is_trash = 1, CONCAT(IF("'.$language.'" = "de", "GELÖSCHT", "DELETED"), " ||"), null),
                IFNULL(author, SUBSTRING(title, 1, 30)),
                year_published
            )) AS string');
            $select[] = DB::raw('CONCAT(
                author, ", ",
                title,
                IF(volume > "", CONCAT(" ", volume), ""),
                IF(place > "" || year_published > "", ",", ""),
                IF(place > "", CONCAT(" ", place), ""),
                IF(year_published > "", CONCAT(" ", year_published), "")
            ) AS addition');
        }
        else {
            $select[] = DB::raw('CONCAT(
                IF(is_trash = 1, CONCAT(IF("'.$language.'" = "de", "GELÖSCHT", "DELETED"), " ||"), null),
                author, ", ",
                title,
                IF(volume > "", CONCAT(" ", volume), ""),
                IF(place > "" || year_published > "", ",", ""),
                IF(place > "", CONCAT(" ", place), ""),
                IF(year_published > "", CONCAT(" ", year_published), "")
            ) AS string');
        }

        $query = DB::table(config('dbi.tablenames.bibliography'))
            -> select($select)
            -> where('is_trash', 0);

        // Where
        /*if (!empty($input['id'])) {
            $query -> orWhereIn('zotero_id', $input['id']);
        }*/
        if (!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search, $input) {
                    $subquery
                        -> orWhere('zotero_id', $search)
                        -> orWhere('author', 'LIKE', '%'.$search.'%')
                        -> orWhere('title', 'LIKE', '%'.$search.'%')
                        -> orWhere('volume', $search)
                        -> orWhere('year_published', $search);
                    if (!empty($input['id'])) {
                        $subquery -> orWhereIn('zotero_id', $input['id']);
                    }
                });
            }
        }

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(zotero_id, "'.implode('","', array_reverse($input['id'])).'") DESC, ').
            'is_trash = 1, '.
            'author IS NULL, '.
            'author ASC'
        )
        -> limit(50);

        return json_decode($query->get(), TRUE);
    }
}
