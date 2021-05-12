<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use App\Http\Controllers\dbi\handler\generic_select;
use Request;
use DB;


class legends implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        $handler = new generic_select;

        // DB Pre Query if no ID is given
        if (empty($id)) {
            $input = Request::post();

            $allowed_keys = [
                'where' => [
                    'id'            => 'id',
                    'legend_sort'   => ['legend_sort_basis',   'LIKE', '%', '%'],
                    'legend'        => [
                        ['raw' => 'CONCAT_WS("||", legend_sort_basis, legend)'], 'LIKE', '%', '%'
                    ],
                    'keywords'      => ['keywords',            'LIKE', '%', '%'],
                    'comment'       => ['comment',             'LIKE', '%', '%'],
                    'side'          => 'side',
                    'role'          => 'is_type',
                    'language'      => 'legend_language',
                    'direction'     => 'id_legend_direction'
                ],
                'order_by' => [
                    'id'            => 'id',
                    'legend'        => 'legend_sort_basis',
                    'language'      => 'legend_language',
                    'side'          => 'side',
                    'role'          => 'is_type',
                    'direction'     => 'id_legend_direction'
                ]
            ];

            $prequery = $handler -> preQuery(config('dbi.tablenames.legends'), $allowed_keys, $input);

            $where = $prequery['ids'];
        }

        // Set condition if ID is given
        else {
            $where = [$id];
        }

        // DB Main Query
        /*if(!empty($where[0])) {
            $dbi = DB::table(config('dbi.tablenames.legends').' AS l')
                -> leftJoin(config('dbi.tablenames.coins').' AS c', DB::raw('IF(l.side = 1, c.id_legend_r, c.id_legend_o)'), '=', 'l.id')
                -> leftJoin(config('dbi.tablenames.images').' AS c_img', 'c_img.CoinID', '=', 'c.id')
                -> leftJoin(config('dbi.tablenames.types').' AS t', DB::raw('IF(l.side = 1, t.id_legend_r, t.id_legend_o)'), '=', 'l.id')
                -> leftJoin(config('dbi.tablenames.images').' AS t_img', 't_img.ImageID', '=', 't.id_imageset')
                -> select([
                    'l.id AS id',
                    'l.legend                     AS legend',
                    'l.legend_sort_basis          AS legend_sort',
                    'l.legend_language            AS language',
                    DB::Raw('IFNULL(l.is_type, 0) AS role'),
                    DB::Raw('IFNULL(l.side, 0)    AS side'),
                    'l.id_legend_direction        AS direction',
                    DB::Raw('IF(l.id_legend_direction IS NOT NULL, CONCAT("'.config('dbi.url.storage').'", "Legenddirections/", l.id_legend_direction, "richtung.jpg"), null) AS direction_img'),
                    'l.fulltext_proposal          AS full_text',
                    'l.keywords                   AS keywords',
                    'l.comment                    AS comment',
                    DB::raw('MAX(
                        IF(l.is_type = 1,
                            IF(IF(l.side = 1, t_img.ReverseImageFilename, t_img.ObverseImageFilename) > "",
                            CONCAT(IF(t_img.Path > "", CONCAT(t_img.Path, IF(SUBSTRING(t_img.Path, -1, 1) = "/", "", "/")), ""), IF(l.side = 1, t_img.ReverseImageFilename, t_img.ObverseImageFilename)),
                            null),
                            IF(IF(l.side = 1, c_img.ReverseImageFilename, c_img.ObverseImageFilename) > "",
                            CONCAT(IF(c_img.Path > "", CONCAT(c_img.Path, IF(SUBSTRING(c_img.Path, -1, 1) = "/", "", "/")), ""), IF(l.side = 1, c_img.ReverseImageFilename, c_img.ObverseImageFilename)),
                            null)
                        )
                    ) AS image')
                ])
                -> whereIntegerInRaw('l.id', $where)
                -> orderByRaw('FIELD(l.id, '.implode(',', $where).')')
                -> groupBy('l.id')
                -> get();

            $dbi = json_decode($dbi, TRUE);
        }
        else {
            $dbi = [];
        }*/
        if(!empty($where[0])) {
            $select = [
                'id AS id',
                'legend                     AS legend',
                'legend_sort_basis          AS legend_sort',
                'legend_language            AS language',
                DB::Raw('IFNULL(is_type, 0) AS role'),
                DB::Raw('IFNULL(side, 0)    AS side'),
                'id_legend_direction        AS direction',
                DB::Raw('IF(id_legend_direction IS NOT NULL, CONCAT("'.config('dbi.url.storage').'", "legend-directions/", LPAD(id_legend_direction, 3, 0), ".svg"), null) AS image'),
                'fulltext_proposal          AS full_text',
                'keywords                   AS keywords',
                'comment                    AS comment',

                'created_at                 AS created_at',
                'updated_at                 AS updated_at',
            ];

            $dbi = $handler -> mainQuery(config('dbi.tablenames.legends'), $select, $where);
        }
        else {
            $dbi = [];
        }

        return empty($id) ? [
            'pagination'    => $prequery['pagination'],
            'where'         => $prequery['where'],
            'contents'      => $dbi
        ] : $dbi;
    }

    public function input ($user, $input) {
        $validation = $this -> validateInput($input);

        if(empty($validation['error'][0])) {
            $input = $validation['input'];

            $ID = DB::transaction(function () use ($input) {
                $values = [
                    'legend'                => $input['legend'],
                    'legend_sort_basis'     => $input['legend_sort'],
                    'legend_language'       => $input['language'],
                    'is_type'               => empty($input['role']) ? 0 : $input['role'],
                    'side'                  => empty($input['side']) ? 0 : $input['side'],
                    'id_legend_direction'   => $input['direction'],
                    'fulltext_proposal'     => $input['full_text'],
                    'keywords'              => $input['keywords'],
                    'comment'               => $input['comment']
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    return DB::table(config('dbi.tablenames.legends')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.legends')) -> where('id', $input['id']) -> update($values);
                    return $input['id'];
                }
            });

            return $ID;
        }
        // Validation Error
        else { return ['error' => $validation['error']]; }
    }

    public function delete ($user, $input) {
        DB::transaction(function () use ($input) {
            DB::table(config('dbi.tablenames.legends')) -> where('id', $input['id']) -> delete();
        });

        return true;
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {
        if (empty($input['legend'])) {
            $error[] = config('dbi.responses.validation.legends.name');
        }

        // Return validated input
        if (empty($error)) {
            return ['input' => $input];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
