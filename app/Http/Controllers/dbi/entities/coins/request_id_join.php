<?php

namespace App\Http\Controllers\dbi\entities\coins;

use DB;


class request_id_join {

    public function instructions ($query) {
        return $query 
            -> leftJoin(config('dbi.tablenames.owners').'       AS oo',     'oo.id',    '=', 'c.id_owner_original')
            -> leftJoin(config('dbi.tablenames.owners').'       AS or',     'or.id',    '=', 'c.id_owner_reproduction')
            -> leftJoin(config('dbi.tablenames.findspots').'    AS f',      'f.id',     '=', 'c.id_findspot')
            -> leftJoin(config('dbi.tablenames.hoards').'       AS h',      'h.id',     '=', 'c.id_hoard')

            -> leftJoin(config('dbi.tablenames.mints').'        AS mint',   'mint.id',  '=', 'c.id_mint')
            -> leftJoin(config('dbi.tablenames.persons').'      AS ip',     'ip.id',    '=', 'c.id_issuer')
            -> leftJoin(config('dbi.tablenames.authorities').'  AS a',      'a.id',     '=', 'c.id_authority')
            -> leftJoin(config('dbi.tablenames.persons').'      AS ap',     'ap.id',    '=', 'c.id_authority_person')
            -> leftJoin(config('dbi.tablenames.tribes').'       AS at',     'at.id',    '=', 'c.id_authority_group')


            -> leftJoin(config('dbi.tablenames.materials').'    AS mat',    'mat.id',   '=', 'c.id_material')
            -> leftJoin(config('dbi.tablenames.denominations').' AS de',    'de.id',    '=', 'c.id_denomination')
            -> leftJoin(config('dbi.tablenames.standards').'    AS s',      's.id',     '=', 'c.id_standard')

            -> leftJoin(config('dbi.tablenames.periods').'      AS e',      'e.id',     '=', 'c.id_period')

            // Obverse
            -> leftJoin(config('dbi.tablenames.designs').'      AS do',     'do.id',    '=', 'c.id_design_o')
            //'lo'    =>  [$db.'data_legends',            'lo.id',    '=', 'c.id_legend_o'])

            // Reverse
            -> leftJoin(config('dbi.tablenames.designs').'      AS dr',     'dr.id',    '=', 'c.id_design_r');
    }
}