<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use App\Http\Controllers\dbi\handler\generic_select;
use Request;
use DB;


class designs implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        $handler = new generic_select;

        // DB Pre Query if no ID is given
        if (empty($id)) {
            $input = Request::post();

            if (!empty($input['side'])) {
                if ($input['side'] === 'o') $input['side'] = [0, 2];
                else if ($input['side'] === 'r') $input['side'] = [1, 2];
            }

            $allowed_keys = [
                'where' => [
                    'id'            => 'id',
                    'name_de'       => ['design_de', 'LIKE', '%', '%'],
                    'name_en'       => ['design_en', 'LIKE', '%', '%'],
                    'border_dots'   => 'border_of_dots',
                    'role'          => 'role',
                    'side'          => 'side'
                ],
                'order_by' => [
                    'id'            => 'id',
                    'name_de'       => 'design_de',
                    'name_en'       => 'design_en',
                    'border_dots'   => 'border_of_dot',
                    'side'          => 'side',
                    'role'          => 'role'
                ]
            ];

            $prequery = $handler -> preQuery(config('dbi.tablenames.designs'), $allowed_keys, $input);

            $where = $prequery['ids'];
        }

        // Set condition if ID is given
        else {
            $where = [$id];
        }

        // DB Main Query
        if(!empty($where[0])) {
            $dbi = DB::table(config('dbi.tablenames.designs').' AS d')
                -> leftJoin(config('dbi.tablenames.coins').' AS c', DB::raw('IF(d.side = 1, c.id_design_r, c.id_design_o)'), '=', 'd.id')
                -> leftJoin(config('dbi.tablenames.images').' AS c_img', 'c_img.CoinID', '=', 'c.id')
                -> leftJoin(config('dbi.tablenames.types').' AS t', DB::raw('IF(d.side = 1, t.id_design_r, t.id_design_o)'), '=', 'd.id')
                -> leftJoin(config('dbi.tablenames.images').' AS t_img', 't_img.ImageID', '=', 't.id_imageset')
                -> select([
                    'd.id             AS id',
                    'd.design_de      AS name_de',
                    'd.design_en      AS name_en',
                    'd.border_of_dots AS border_dots',
                    DB::raw('IFNULL(d.role, 0) AS role'),
                    DB::raw('IFNULL(d.side, 0) AS side'),
                    DB::raw('MAX(
                        IF(d.role = 1,
                            IF(IF(d.side = 1, t_img.ReverseImageFilename, t_img.ObverseImageFilename) > "",
                            CONCAT(IF(t_img.Path > "", CONCAT(t_img.Path, IF(SUBSTRING(t_img.Path, -1, 1) = "/", "", "/")), ""), IF(d.side = 1, t_img.ReverseImageFilename, t_img.ObverseImageFilename)),
                            null),
                            IF(IF(d.side = 1, c_img.ReverseImageFilename, c_img.ObverseImageFilename) > "",
                            CONCAT(IF(c_img.Path > "", CONCAT(c_img.Path, IF(SUBSTRING(c_img.Path, -1, 1) = "/", "", "/")), ""), IF(d.side = 1, c_img.ReverseImageFilename, c_img.ObverseImageFilename)),
                            null)
                        )
                    ) AS image')
                ])
                -> whereIntegerInRaw('d.id', $where)
                -> orderByRaw('FIELD(d.id, '.implode(',', $where).')')
                -> groupBy('d.id')
                -> get();

            $dbi = json_decode($dbi, TRUE);
        }
        else {
            $dbi = [];
        }

        /*if(!empty($where[0])) {
            $select = [
                'id             AS id',
                'design_de      AS name_de',
                'design_en      AS name_en',
                'border_of_dots AS border_dots',
                DB::raw('IFNULL(role, 0) AS role'),
                DB::raw('IFNULL(side, 0) AS side')
            ];

            $dbi = $handler -> mainQuery(config('dbi.tablenames.designs'), $select, $where);
        }
        else {
            $dbi = [];
        }*/

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
                    'design_de'     => $input['name_de'],
                    'design_en'     => $input['name_en'],
                    'border_of_dots'=> empty($input['border_dots']) ? 0 : 1,
                    'role'          => empty($input['role'])        ? 0 : $input['role'],
                    'side'          => empty($input['side'])        ? 0 : $input['side']
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    return DB::table(config('dbi.tablenames.designs')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.designs')) -> where('id', $input['id']) -> update($values);
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
            DB::table(config('dbi.tablenames.designs')) -> where('id', $input['id']) -> delete();
        });

        return true;
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {
        if (empty($input['name_de'])) {
            $error[] = config('dbi.responses.validation.general.name_de');
        }
        if (empty($input['name_en'])) {
            $error[] = config('dbi.responses.validation.general.name_en');
        }

        // Return validated input
        if (empty($error)) {
            return ['input' => $input];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
