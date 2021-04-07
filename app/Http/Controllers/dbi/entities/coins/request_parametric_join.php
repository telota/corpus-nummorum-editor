<?php

namespace App\Http\Controllers\dbi\entities\coins;

use DB;


class request_parametric_join {

    public function instructions ($query) {
        return $query 
            ->leftJoin(config('dbi.tablenames.owners').'        AS o',      'o.id',     '=', 'c.id_owner_original')

            ->leftJoin(config('dbi.tablenames.mints').'         AS mint',   'mint.id',  '=', 'c.id_mint')

            ->leftJoin(config('dbi.tablenames.materials').'     AS mat',    'mat.id',   '=', 'c.id_material')
            ->leftJoin(config('dbi.tablenames.denominations').' AS de',     'de.id',    '=', 'c.id_denomination')
            ->leftJoin(config('dbi.tablenames.periods').'       AS e',      'e.id',     '=', 'c.id_period')

            // Obverse
            ->leftJoin(config('dbi.tablenames.designs').'       AS do',     'do.id',    '=', 'c.id_design_o')
            //'lo'    =>  [$db.'data_legends',            'lo.id',    '=', 'c.id_legend_o'])

            // Reverse
            ->leftJoin(config('dbi.tablenames.designs').'       AS dr',     'dr.id',    '=', 'c.id_design_r');
    }
}