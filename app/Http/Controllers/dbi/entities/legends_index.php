<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use App\Http\Controllers\dbi\handler\generic_select;
use App\Http\Controllers\dbi\handler\legend_handler;
use Request;
use DB;


class legends_index implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        $handler = new generic_select;

        // Set Conditions
        if (empty($id)) {
            /*$input = Request::post();
            $where = $prequery['ids'];*/
        }
        else $where = [$id];

        $dbi = DB::table(config('dbi.tablenames.legends'))
        ->select([
            'legend_sort_basis AS string',
            DB::RAW('JSON_ARRAYAGG(JSON_OBJECT(
                "id", id,
                "legend", legend,
                "language", legend_language,
                "role", IFNULL(is_type, 0),
                "side", IFNULL(side, 0),
                "direction", id_legend_direction,
                "keywords", keywords,
                "comment", comment
            )) AS records')
        ])
        ->orderBy('legend_sort_basis')
        ->groupBy('legend_sort_basis')
        ->get();
        $dbi = json_decode($dbi, true);


        return array_map(function ($item) {
            $item['records'] = json_decode($item['records'], true);
            return $item;
        }, $dbi);
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
        if (empty($input['legend'])) {
            $error[] = config('dbi.responses.validation.legends.name');
        }

        // Return validated input
        if (empty($error)) {

            $handler = new legend_handler();
            $input['legend_sort'] = $handler->createIndex($input['legend'], $input['language']);

            return ['input' => $input];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
