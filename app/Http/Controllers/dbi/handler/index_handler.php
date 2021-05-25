<?php

namespace App\Http\Controllers\dbi\handler;

use DB;
use App\Http\Controllers\dbi\entities\coins\index_handler as coins;
use App\Http\Controllers\dbi\entities\types\index_handler as types;

class index_handler {

    public function updateOrInsert ($entity, $id)  {

        /*if ($entity === 'coins') $handler = new coins();
        else if ($entity === 'types') $handler = new types();
        else return false;*/

        $divider = '"||"';
        $id_base = 'id_'.rtrim($entity, 's');

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
            'pecularities' => 'JSON_OBJECT(
                "de", base.pecularities_de,
                "en", base.pecularities_en
            )',
            // Comment
            'comment' => 'JSON_OBJECT(
                "de", base.comment_public,
                "en", base.comment_public_en
            )'
        ];

        foreach (['o', 'r'] as $index => $side) {
            // Design
            $select['design_'.$side] = 'JSON_OBJECT(
                "de", sd'.$side.'.design_de,
                "en", sd'.$side.'.design_en
            )';
            // Legend
            $select['legend_'.$side] = 'CONCAT_WS('.$divider.', sl'.$side.'.legend, sl'.$side.'.legend_sort_basis, sl'.$side.'.keywords)';
            // Symbol
            $select['symbol_'.$side] = '(SELECT
                JSON_OBJECT(
                    "de", symbol_de,
                    "en", symbol_en
                )
                FROM '.config('dbi.tablenames.'.$entity.'_to_symbols').' as bts
                LEFT JOIN '.config('dbi.tablenames.symbols').' AS s ON s.id = bts.id_symbol
                WHERE bts.'.$id_base.' = '.$id.' && bts.side = '.$index.'
            )';
        }

        foreach ($select as $alias => $statement) $select_statement[] = DB::Raw($statement.' AS '.$alias);

        // Get Data of related ID
        $raw = DB::table(config('dbi.tablenames.'.$entity).' as base')
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

        $raw = json_decode($raw, true);
        $raw = empty($raw[0]) ? false : $raw[0];

        if (empty($raw)) return false;

        // flatten Data-Array
        foreach ($raw as $key => $val) {
            if (substr($val, 0, 2) === '{"' && substr($val, -1) === '}') {
                foreach (json_decode($val, true) as $language => $val2) {
                    $data[] = [
                        'key' => $key,
                        'language' => $language,
                        'value' => $val2
                    ];
                }
            }
            else {
                $data[] = [
                    'key' => $key,
                    'language' => null,
                    'value' => $val
                ];
            }
        }

        // Write to Index
        foreach ($data as $d) {

            // Delete existing record if value is empty (might happen after update of dataset)
            if (empty($d['value'])) {
                DB::table(config('dbi.tablenames.index_'.$entity))
                ->where([
                    [$id_base,          $id],
                    ['data_key',        $d['key']],
                    ['data_language',   $d['language']],
                ])
                ->delete();
            }

            // Update or insert if value is not empty
            else {
                $data = DB::table(config('dbi.tablenames.index_'.$entity))
                ->updateOrInsert([
                    $id_base        => $id,
                    'data_key'      => $d['key'],
                    'data_language' => $d['language']
                ], [
                    $id_base        => $id,
                    'data_key'      => $d['key'],
                    'data_language' => $d['language'],
                    'data_value'    => $d['value']
                ]);
            }
        }

        return true;
    }
}
