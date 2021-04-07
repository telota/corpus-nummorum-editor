<?php

namespace App\Http\Controllers\dbi\entities\types;

use DB;


class request_parametric_join {

    public function instructions ($query) {
        return $query
        -> leftJoin(config('dbi.tablenames.mints').'            AS mint',   'mint.id',  '=', 't.id_mint')

        -> leftJoin(config('dbi.tablenames.materials').'        AS mat',    'mat.id',   '=', 't.id_material')
        -> leftJoin(config('dbi.tablenames.denominations').'    AS de',     'de.id',    '=', 't.id_denomination')

        -> leftJoin(config('dbi.tablenames.periods').'          AS e',      'e.id',     '=', 't.id_period')

        // Obverse
        -> leftJoin(config('dbi.tablenames.designs').'          AS do',     'do.id',    '=', 't.id_design_o')
        //'lo'    =>  [$db.'data_legends',            'lo.id',    '=', 't.id_legend_o'])

        // Reverse
        -> leftJoin(config('dbi.tablenames.designs').'          AS dr',     'dr.id',    '=', 't.id_design_r');
    }
}