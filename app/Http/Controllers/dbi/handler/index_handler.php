<?php

namespace App\Http\Controllers\dbi\handler;

use DB;
use App\Http\Controllers\dbi\entities\coins\index_handler as coins;
use App\Http\Controllers\dbi\entities\types\index_handler as types;

use Response;

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

    public function handleSearch ($entity, $query, $params) {

        $where = [];

        $query->whereIn(substr($entity, 0, 1).'.id', function ($subquery) use ($entity, $params, &$where) {

            // Process Parameters
            $string = $where['q'] = $params['q'];
            $string = trim(preg_replace('/\s+/', ' ', $string));

            $isRegex = empty($params['qre']) ? false : true;
            $isCs   = empty($params['qcs']) ? false : true;

            if (empty($params['qex'])) $excluded = null;
            else{
                if (is_array($params['qex'])) $excluded = $params['qex'];
                else $excluded = explode(',', $params['qex']);
            }

            // Set Where for Display
            if (isset($params['qex'])) $where['qex'] = $excluded;
            if (isset($params['qcs'])) $where['qcs'] = empty($isCs) ? 0 : 1;
            if (isset($params['qre'])) $where['qre'] = empty($isRegex) ? 0 : 1;

            // Build Query
            $subquery->select('id')

            // Arrange Index Table
            ->from(function ($indexQuery) use ($entity, $excluded) {
                $table_id = 'id_'.rtrim($entity, 's');

                $indexQuery->select([
                    $table_id.' AS id',
                    DB::Raw('json_arrayagg(
                        CONCAT_WS("::",
                            CONCAT_WS("_", data_key, data_language),
                            REPLACE(data_value, \'"\', "")
                        )
                    ) AS vals')
                ])
                ->from(config('dbi.tablenames.index_coins').' AS concated_index');

                // Add where conditions if keys have to be excluded
                if (!empty($excluded)) foreach ($excluded as $ex) $indexQuery->where('data_key', '!=', trim($ex));

                $indexQuery->groupBy($table_id);
            })

            // Add WhereConditions
            ->where(function ($queryByOr) use ($string, $isRegex, $isCs) {

                // Split by OR
                foreach ($this->splitString($string, false) as $byOr) {
                    $queryByOr->orWhere(function ($queryByAnd) use (&$byOr, $isRegex, $isCs) {
                        $byOr = trim($byOr);

                        // Split by AND
                        foreach ($this->splitString($byOr, true) as $byAnd) {
                            $byAnd = trim($byAnd);

                            $queryByAnd->where(function ($subQuery) use ($byAnd, $isRegex, $isCs) {
                                $this->createWhere($subQuery, $byAnd, $isRegex, $isCs);
                            });
                        }
                    });
                }

            });
        });

        return [
            'query' => $query,
            'where' => $where
        ];
    }

    public function splitString ($string, $isAnd = false) {
        if ($isAnd) {
            // Escape Space after NOT to avoid blank split
            $string = str_replace('NOT ', 'NOT___', $string);
            // Replace AND with blanks to make split easier
            $string = str_replace('AND', ' ', $string);
            // Add trailing blanks to enhance quoting-match
            $string = ' '.$string.' ';
            // Replace blanks in quoted strings to avoid being split
            $string = preg_replace_callback('/"(.*?)" /', function ($match) {
                return str_replace(' ', '___', trim($match[1])).' ';
            }, $string);
            // Trim and Replace multiple Spaces to avoid empty matches after split
            $string = trim(preg_replace('/\s+/', ' ', $string));
            $split = explode(' ', $string);
            // Replace Escape-Chars in quoted Strings
            foreach ($split as $key => $val) {
                if (empty($val)) unset($split[$key]);
                else $split[$key] = trim(str_replace('___', ' ', $val));
            }

            return $split;
        }

        else return explode(' OR ', $string);
    }

    public function createWhere ($query, $string, $isRegex = false, $isCs = false) {
        $prefix = '';

        // Look for NOT in String
        if (substr($string, 0, 4) === 'NOT ') {
            $string = trim(substr($string, 3));
            $prefix = 'NOT ';
        }

        // Look for stated columnnames (column::string)
        $split = explode('::', $string);

        if (empty($split[1])) {
            if (empty($isRegex)) $string = '%'.$string.'%';
        }
        else {
            $isRegex = true;
            $string = '"'.$split[0].'[^"]*'.$split[1];
        }

        // Add R if REGEX is required
        if ($isRegex === true) $prefix .= 'R';

        $query->where(DB::Raw('vals COLLATE '.($isCs === true ? 'utf8mb4_bin' : 'utf8mb4_unicode_ci')), $prefix.'LIKE', $string);
    }

    // https://www.php.net/manual/de/regexp.reference.recursive.php
    /*public function recursiveSplit($string, $layer) {

        preg_match_all("/\((([^()]*|(?R))*)\)/",$string,$matches);
        // iterate thru matches and continue recursive split
        if (count($matches) > 1) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                if (is_string($matches[1][$i])) {
                    if (strlen($matches[1][$i]) > 0) {
                        echo $layer.":   ".$matches[1][$i];
                        $this->recursiveSplit($matches[1][$i], $layer + 1);
                    }
                }
            }
        }
    }*/
}
