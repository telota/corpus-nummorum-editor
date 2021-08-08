<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use App\Http\Controllers\dbi\handler\generic_select;
use Request;
use DB;


class dies implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        $handler = new generic_select;
        $table = [
            'base' => config('dbi.tablenames.dies').' AS di',
            'joins' => [
                [config('dbi.tablenames.legends').' AS le', 'di.id_legend', '=', 'le.id'],
                [config('dbi.tablenames.designs').' AS de', 'di.id_design', '=', 'de.id']
            ]
        ];

        // DB Pre Query if no ID is given
        if (empty($id)) {
            $input = Request::post();

            if (!empty($input['side'])) {
                if ($input['side'] === 'o') $input['side'] = [0, 2];
                else if ($input['side'] === 'r') $input['side'] = [1, 2];
            }

            $allowed_keys = [
                'where' => [
                    'id'                => 'di.id',
                    'name'              => ['di.name_die',     'LIKE', '%', '%'],
                    'id_design'         => 'di.id_design',
                    'id_legend'         => 'di.id_legend',
                    'side'              => 'di.side',
                    'legend'            => [
                        ['raw' => '(SELECT GROUP_CONCAT(le.legend SEPARATOR "|") FROM '.config('dbi.tablenames.legends').' AS le WHERE le.id = di.id_legend GROUP BY le.id)'],
                        'LIKE',
                        '%', '%'
                    ],
                    'design'            => [['raw' => 'CONCAT_WS("|", de.design_de, de.design_en)'], 'LIKE', '%', '%'],
                    'design_de'         => [['raw' => 'CONCAT_WS("|", de.design_de, de.design_en)'], 'LIKE', '%', '%'],
                    'design_en'         => [['raw' => 'CONCAT_WS("|", de.design_de, de.design_en)'], 'LIKE', '%', '%'],
                    'comment'           => ['di.comment',      'LIKE', '%', '%'],
                ],
                'order_by' => [
                    'id'                => 'di.id',
                    'name'              => 'di.name_die',
                    'legend'            => 'di.id_legend',
                    'design_de'         => 'de.design_de',
                    'design_en'         => 'de.design_en',
                    'side'              => 'di.side'
                ]
            ];

            $prequery = $handler -> preQuery($table, $allowed_keys, $input);

            $where = $prequery['ids'];
        }

        // Set condition if ID is given
        else { $where = [$id]; }

        // DB Main Query
        if(!empty($where[0])) {
            $dbi = DB::table(config('dbi.tablenames.dies').' AS di')
                -> leftJoin(config('dbi.tablenames.designs').' AS de', 'de.id', '=', 'di.id_design')
                -> leftJoin(config('dbi.tablenames.coins').' AS c', DB::raw('IF(di.side = 1, c.id_die_r, c.id_die_o)'), '=', 'di.id')
                -> leftJoin(config('dbi.tablenames.images').' AS c_img', 'c_img.CoinID', '=', 'c.id')
                -> select([
                    'di.id              AS  id',
                    'di.name_die        AS  name',
                    'di.comment         AS  comment',
                    'di.id_legend       AS  id_legend',
                    'di.id_design       AS  id_design',
                    DB::raw('IFNULL(di.side, 0) AS side'),
                    'de.design_de       AS  design_de',
                    'de.design_en       AS  design_en',
                    DB::raw('(SELECT le.legend FROM '.config('dbi.tablenames.legends').' AS le WHERE le.id = di.id_legend) AS legend'),
                    DB::raw('MAX(
                        IF(IF(di.side = 1, c_img.ReverseImageFilename, c_img.ObverseImageFilename) > "",
                        CONCAT(IF(c_img.Path > "", CONCAT(c_img.Path, IF(SUBSTRING(c_img.Path, -1, 1) = "/", "", "/")), ""), IF(di.side = 1, c_img.ReverseImageFilename, c_img.ObverseImageFilename)),
                        null)
                    ) AS image')
                ])
                -> whereIntegerInRaw('di.id', $where)
                -> orderByRaw('FIELD(di.id, '.implode(',', $where).')')
                -> groupBy('di.id')
                -> get();

            $dbi = json_decode($dbi, TRUE);
            /*$select = [
                'di.id              AS  id',
                'di.name_die        AS  name',
                'di.comment         AS  comment',
                'di.id_legend       AS  id_legend',
                'di.id_design       AS  id_design',
                DB::raw('IFNULL(di.side, 0) AS side'),
                'de.design_de       AS  design',
                'le.legend          AS  legend'
            ];

            $dbi = $handler -> mainQuery($table, $select, $where);*/
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
                    'name_die'      => $input['name'],
                    'side'          => empty($input['side']) ? 0 : 1,
                    'id_design'     => $input['id_design'],
                    'id_legend'     => $input['id_legend'],
                    'comment'       => $input['comment']
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    return DB::table(config('dbi.tablenames.dies')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.dies')) -> where('id', $input['id']) -> update($values);
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
            DB::table(config('dbi.tablenames.dies')) -> where('id', $input['id']) -> delete();
        });

        return true;
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {
        if (empty($input['name'])) {
            $error[] = config('dbi.responses.validation.general.name');
        }

        // Return validated input
        if (empty($error)) {
            return ['input' => $input];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
