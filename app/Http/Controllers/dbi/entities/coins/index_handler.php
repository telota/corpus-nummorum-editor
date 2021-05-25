<?php

namespace App\Http\Controllers\dbi\entities\coins;
use DB;

class index_handler {

    public function data ($id) {
        $divider = '"||"';
        $entity = 'coins';
        $id_base = 'id_coin';

        $select = [
            // Mint
            'mint' => 'CONCAT_WS(
                '.$divider.',
                sm.nomisma_id,
                sm.mint,
                (
                    SELECT GROUP_CONCAT(DISTINCT mnt.mint_name SEPARATOR '.$divider.')
                    FROM '.config('dbi.tablenames.mints_nomisma_text').' as mnt
                    WHERE mnt.nomisma_id_mint = sm.nomisma_id && mnt.mint_name != sm.mint && mnt.mint_name != sm.placename
                ),
                sm.placename
            )',
            // Region
            'region' => '(
                SELECT CONCAT_WS(
                    '.$divider.',
                    sr.nomisma_id_region,
                    IF(sr.nomisma_id_region = r.region, NULL, r.region),
                    r.de, r.en, r.el, r.bg, r.ru, r.tr, r.ro
                )
                FROM '.config('dbi.tablenames.regions_to_subregions').' as sr
                LEFT JOIN '.config('dbi.tablenames.regions').' AS r ON r.id = sr.id_region
                WHERE sr.nomisma_id_region = sm.imported_nomisma_subregion
            )',
            // Pecularities
            'pecularities' => 'CONCAT_WS('.$divider.', base.pecularities_de, base.pecularities_en)',
            // Comment
            'comment' => 'CONCAT_WS('.$divider.', base.comment_public, base.comment_public_en)'
        ];

        foreach (['o', 'r'] as $index => $side) {
            // Design
            $select['design_'.$side] = 'CONCAT_WS('.$divider.', sd'.$side.'.design_de, sd'.$side.'.design_en)';
            // Legend
            $select['legend_'.$side] = 'CONCAT_WS('.$divider.', sl'.$side.'.legend, sl'.$side.'.legend_sort_basis, sl'.$side.'.keywords)';
            // Symbol
            $select['symbol_'.$side] = '(
                SELECT GROUP_CONCAT(
                    CONCAT_WS(
                        '.$divider.',
                        symbol_de,
                        symbol_en
                    ) SEPARATOR '.$divider.'
                )
                FROM '.config('dbi.tablenames.'.$entity.'_to_symbols').' as bts
                LEFT JOIN '.config('dbi.tablenames.symbols').' AS s ON s.id = bts.id_symbol
                WHERE bts.'.$id_base.' = '.$id.' && bts.side = '.$index.'
            )';
        }

        foreach ($select as $alias => $statement) $select_statement[] = DB::Raw($statement.' AS '.$alias);

        // Get Data of related ID
        $data = DB::table(config('dbi.tablenames.'.$entity).' as base')
        ->leftJoin(config('dbi.tablenames.mints').' AS sm',       'sm.id',    '=',    'base.id_mint')
        ->leftJoin(config('dbi.tablenames.periods').' AS pm',     'pm.id',    '=',    'base.id_period')
        ->leftJoin(config('dbi.tablenames.persons').' AS sap',    'sap.id',   '=',    'base.id_authority_person')
        ->leftJoin(config('dbi.tablenames.tribes').' AS sat',     'sat.id',   '=',    'base.id_authority_group')
        ->leftJoin(config('dbi.tablenames.designs').' AS sdo',    'sdo.id',   '=',    'base.id_design_o')
        ->leftJoin(config('dbi.tablenames.designs').' AS sdr',    'sdr.id',   '=',    'base.id_design_r')
        ->leftJoin(config('dbi.tablenames.legends').' AS slo',    'slo.id',   '=',    'base.id_legend_o')
        ->leftJoin(config('dbi.tablenames.legends').' AS slr',    'slr.id',   '=',    'base.id_legend_r')
        ->select($select_statement)
        ->where([
            ['base.id', $id],
            ['base.publication_state', '!=', 3]
        ])
        ->get();

        $data = json_decode($data, true);
        $data = empty($data[0]) ? false : $data[0];

        if (empty($data)) return false;

        return $data;
    }
}
