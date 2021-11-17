<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class import_sources implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------
    public function select ($user, $id) {
        if (empty($id)) die ('ID parameter is required');

        else if (strtolower(substr($id, 0, 4)) == 'ikmk') {
            $id = explode(':', $id);
            $id = intval(end($id));

            $query = DB::table(config('dbi.tablenames.coins').' AS c')
                ->leftJoin(config('dbi.tablenames.coins_inherit').' AS cti', 'cti.id', '=', 'c.id')
                ->select([
                    'c.id           AS id',
                    'c.source_link  AS source',
                    'c.created_at   AS created',
                    'c.updated_at   AS updated',
                    'cti.id_type    AS inherited',
                    DB::Raw('(
                        SELECT JSON_ARRAYAGG(ctp.id_type)
                        FROM '.config('dbi.tablenames.coins_to_types').' AS ctp
                        LEFT JOIN '.config('dbi.tablenames.types').' AS t ON t.id = ctp.id_type
                        WHERE ctp.id = c.id AND t.publication_state = 1
                    ) AS types')
                ])
                ->where('c.source_link', 'LIKE', '%/ikmk%id=%')
                ->where('c.publication_state', 1);

            if (!empty($id)) {
                $query->where('c.source_link', 'LIKE', '%'.$id);
            }

            $dbi = $query->get();
            $dbi = json_decode($dbi, TRUE);

            $items = [];
            foreach($dbi as $i) {
                $ikmk = explode('?id=', $i['source']);
                $ikmk = intval(trim(end($ikmk)));

                if ($ikmk) {
                    $types = [];
                    if ($i['inherited']) $types[] = [
                        'id'    => intval($i['inherited']),
                        'link'  => 'https://www.corpus-nummorum.eu/types/'.$i['inherited']
                    ];

                    if ($linked = json_decode($i['types'], true)) foreach($linked as $l) {
                        if ($i['inherited'] !== $l) $types[] = [
                            'id'    => intval($l),
                            'link'  => 'https://www.corpus-nummorum.eu/types/'.$l
                        ];
                    }

                    $items[$ikmk] = [
                        'id'            => $i['id'],
                        'link'          => 'https://www.corpus-nummorum.eu/coins/'.$i['id'],
                        'types'         => $types,
                        'created_at'    => $i['created'],
                        'updated_at'    => $i['updated']
                    ];
                }
            }
            ksort($items);

            return $items;
        }
        else die (abort(404, 'Not supported!'));
    }

    public function input ($user, $input) {
        die (abort(404, 'Not supported!'));
    }

    public function delete ($user, $input) {
        die (abort(404, 'Not supported!'));
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------
    public function validateInput ($input) {
    }
}
