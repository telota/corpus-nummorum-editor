<?php

namespace App\Http\Controllers\dbi\handler;

use DB;
//use App\Http\Controllers\dbi\entities\coins\index_handler as coins;
//use App\Http\Controllers\dbi\entities\types\index_handler as types;

class index_handler {

    public function updateIndex ($entity, $id = null) {
        // Check if primary or secondary entity is called
        $primaries = ['coins', 'types'];
        if ($entity === 'types') unset($primaries[0]);
        if ($entity === 'coins') unset($primaries[1]);
        $single = count($primaries) === 1 ? true : false;

        // Iterate over primary entities
        foreach ($primaries as $primary) {
            $base_id = 'id_'.rtrim($primary, 's');

            // Get instructions
            $secondaries = $this->subqueryInstructions($entity, $single);

            if (!empty($secondaries[0])) {

                // Build Subquery Statements
                foreach ($secondaries as $s) $subqueries[] = 'SELECT
                    base.id as '.$base_id.',
                    "'.$s['key'].'" as data_key,
                    '.(empty($s['language']) ? 'null' : ('"'.$s['language'].'"')).' as data_language,
                    '.$s['value'].' as data_value
                FROM '.$s['sec_table'].' as sec '.
                (empty($s['joins']) ? '' : $s['joins']).'
                LEFT JOIN '.config('dbi.tablenames.'.$primary).' as base ON base.'.$s['sec_id'].' = sec.id
                WHERE base.'.$s['sec_id'].' is not NULL'.
                ($single === true && !empty($id) ? (' AND base.id = '.$id) : '');

                // Build final Statement
                $statement = 'INSERT INTO '.config('dbi.tablenames.index_'.$primary).'
                    ('.$base_id.', data_key, data_language, data_value)
                    SELECT * FROM (
                        ('.implode(') UNION (', $subqueries).')
                    ) as sq
                ON DUPLICATE KEY
                UPDATE '.
                    $base_id.' = sq.'.$base_id.',
                    data_key = sq.data_key,
                    data_language = sq.data_language;';

                // Execute Statements and clean up
                DB::statement($statement);
                DB::table(config('dbi.tablenames.index_coins'))->whereNull('data_value')->delete();
            }
        }

        return true;
    }

    public function subqueryInstructions ($entity, $single) {
        $secondaries = [];

        // Authorities/Coinage
        if ($entity === 'authorities' || $single === true) $secondaries[] = [
            'key'       => 'coinage',
            'value'     => 'CONCAT_WS(
                "||",
                sec.authority_de,
                IF(sec.authority_en = sec.authority_de, NULL, sec.authority_en)
            )',
            'sec_table' => config('dbi.tablenames.authorities'),
            'sec_id'    => 'id_authority'
        ];

        return $secondaries;
    }

    public function updateOrInsert ($entity, $id = null)  {

        $divider = '"||"';
        $id_base = 'id_'.rtrim($entity, 's');

        $select = [
            'id' => 'base.id',
            // Period
            'period' => '(
                SELECT CONCAT_WS(
                    '.$divider.',
                    p.period_de,
                    p.period_en,
                    p.nomisma_id
                )
                FROM '.config('dbi.tablenames.periods').' AS p
                WHERE p.id = base.id_period
            )',
            // Region
            'region' => '(
                SELECT CONCAT_WS(
                    '.$divider.',
                    sr.nomisma_id_region,
                    IF(sr.nomisma_id_region = r.region, NULL, r.region),
                    r.de, r.en, r.el, r.bg, r.ru, r.tr, r.ro
                )
                FROM '.config('dbi.tablenames.regions_to_subregions').' AS sr
                LEFT JOIN '.config('dbi.tablenames.regions').' AS r ON r.id = sr.id_region
                WHERE sr.nomisma_id_region = sm.imported_nomisma_subregion
            )',
            // Coinage
            'coinage' => '(
                SELECT CONCAT_WS(
                    '.$divider.',
                    a.authority_de,
                    IF(a.authority_en = a.authority_de, NULL, a.authority_en)
                )
                FROM '.config('dbi.tablenames.authorities').' AS a
                WHERE a.id = base.id_authority
            )',
            // Mint
            'mint' => 'CONCAT_WS(
                '.$divider.',
                sm.nomisma_id,
                sm.mint,
                (
                    SELECT GROUP_CONCAT(DISTINCT mnt.mint_name SEPARATOR '.$divider.')
                    FROM '.config('dbi.tablenames.mints_nomisma_text').' AS mnt
                    WHERE mnt.nomisma_id_mint = sm.nomisma_id && mnt.mint_name != sm.mint && mnt.mint_name != sm.placename
                ),
                sm.placename
            )',
            // Ruler
            'ruler' => '(
                SELECT CONCAT_WS(
                    '.$divider.',
                    p.person,
                    p.nomisma_id_person,
                    p.alias
                )
                FROM '.config('dbi.tablenames.persons').' AS p
                WHERE p.id = base.id_authority_person
            )',
            // Tribe
            'tribe' => '(
                SELECT CONCAT_WS(
                    '.$divider.',
                    t.tribe_de,
                    IF(t.tribe_en = t.tribe_de, NULL, t.tribe_en),
                    t.nomisma_id
                )
                FROM '.config('dbi.tablenames.tribes').' AS t
                WHERE t.id = base.id_authority_group
            )',
            // Standard
            'standard' => '(
                SELECT CONCAT_WS(
                    '.$divider.',
                    s.standard_de,
                    s.standard_en,
                    s.nomisma_id
                )
                FROM '.config('dbi.tablenames.standards').' AS s
                WHERE s.id = base.id_standard
            )',
            // Denomination
            'denomination' => '(
                SELECT CONCAT_WS(
                    '.$divider.',
                    d.denomination_de,
                    d.denomination_en,
                    d.nomisma_id
                )
                FROM '.config('dbi.tablenames.denominations').' AS d
                WHERE d.id = base.id_denomination
            )',
            // material
            'material' => '(
                SELECT CONCAT_WS(
                    '.$divider.',
                    m.material_de,
                    m.material_en,
                    m.nomisma_id
                )
                FROM '.config('dbi.tablenames.materials').' AS m
                WHERE m.id = base.id_material
            )',
            // persons
            'persons' => '(SELECT
                GROUP_CONCAT(DISTINCT CONCAT_WS(
                    '.$divider.',
                    p.person,
                    p.nomisma_id_person,
                    p.alias
                ) SEPARATOR '.$divider.')
                FROM '.config('dbi.tablenames.'.$entity.'_to_persons').' AS btp
                LEFT JOIN '.config('dbi.tablenames.persons').' AS p ON p.id = btp.id_person
                WHERE btp.'.$id_base.' = base.id
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
            )',
            // References
            'literature' => '(SELECT
                GROUP_CONCAT(DISTINCT CONCAT_WS(
                    ", ",
                    CONCAT(
                        zi.author, ", ",
                        zi.title,
                        IF( zi.volume > "", CONCAT(" ", zi.volume), ""),
                        IF( zi.place > "" || zi.year_published > "", ",", ""),
                        IF( zi.place > "", CONCAT(" ", zi.place), ""),
                        IF( zi.year_published > "", CONCAT(" ", zi.year_published), "")
                    ),
                    CONCAT(
                        IF( btz.page > "", CONCAT("S. ", btz.page), ""),
                        IF( btz.number > "", CONCAT( IF( btz.page > "", ", ", ""), "Nr. ", btz.number), ""),
                        IF( btz.plate > "", CONCAT( IF( btz.page > "" || btz.number > "", ", ", ""), "Taf. ", btz.plate), ""),
                        IF( btz.picture > "", CONCAT(", Abb. ", btz.picture), ""),
                        IF( btz.annotation > "", CONCAT(", FN ", btz.annotation), "")
                    )
                ) SEPARATOR '.$divider.')
                FROM '.config('dbi.tablenames.'.$entity.'_to_zotero').' AS btz
                LEFT JOIN '.config('dbi.tablenames.zotero').' AS zi  ON zi.zotero_id = btz.zotero_id
                WHERE btz.'.$id_base.' = base.id
            )',
            // ObjectGroups
            'objectgroups' => '(SELECT
                GROUP_CONCAT(DISTINCT CONCAT_WS(
                    '.$divider.',
                    obj.objectgroup
                ) SEPARATOR '.$divider.')
                FROM '.config('dbi.tablenames.'.$entity.'_to_objectgroups').' AS bto
                LEFT JOIN '.config('dbi.tablenames.objectgroups').' AS obj ON obj.id = bto.id_objectgroup
                WHERE bto.'.$id_base.' = base.id
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
                    "de", GROUP_CONCAT(DISTINCT symbol_de SEPARATOR '.$divider.'),
                    "en", GROUP_CONCAT(DISTINCT symbol_en SEPARATOR '.$divider.')
                )
                FROM '.config('dbi.tablenames.'.$entity.'_to_symbols').' as bts
                LEFT JOIN '.config('dbi.tablenames.symbols').' AS s ON s.id = bts.id_symbol
                WHERE bts.'.$id_base.' = base.id && bts.side = '.$index.'
            )';
        }

        if ($entity === 'coins') {
            $select['owner'] =  '(
                SELECT CONCAT_WS(
                    ", ",
                    UPPER(o.country),
                    o.city,
                    IF(o.is_name_public = 1, o.owner, null),
                    IF(o.is_name_public = 1, base.collection_id, null)
                )
                FROM '.config('dbi.tablenames.owners').' AS o
                WHERE o.id = base.id_owner_original
            )';
        }

        foreach ($select as $alias => $statement) $select_statement[] = DB::Raw($statement.' AS '.$alias);


        $interval = 5;
        for ($i = (empty($id) ?  6700 : $id); $i < 1000000; $i+= $interval) {
            if (empty($id)) $chunk = [['base.id', '>', $i], ['base.id', '<=', $i + $interval]];
            else $chunk = [['base.id', $id]];

            $raw = DB::table(config('dbi.tablenames.'.$entity).' as base')
            ->leftJoin(config('dbi.tablenames.mints').' AS sm',       'sm.id',    '=',    'base.id_mint')
            ->leftJoin(config('dbi.tablenames.designs').' AS sdo',    'sdo.id',   '=',    'base.id_design_o')
            ->leftJoin(config('dbi.tablenames.designs').' AS sdr',    'sdr.id',   '=',    'base.id_design_r')
            ->leftJoin(config('dbi.tablenames.legends').' AS slo',    'slo.id',   '=',    'base.id_legend_o')
            ->leftJoin(config('dbi.tablenames.legends').' AS slr',    'slr.id',   '=',    'base.id_legend_r')
            ->select($select_statement)
            ->where('base.publication_state', '!=', 3)
            ->where($chunk)
            ->get();

            $raw = json_decode($raw, true);
            echo $i."\t".count($raw)."\n";

            if (empty($raw[0])) break;

            // flatten Data-Array
            $insert = [];
            foreach($raw as $r) {
                $currenId = $r['id'];
                unset($r['id']);
                foreach ($r as $key => $val) {
                    if (substr($val, 0, 2) === '{"' && substr($val, -1) === '}') {
                        foreach (json_decode($val, true) as $language => $val2) {
                            if (!empty($val2)) {
                                /*$data[$currenId][] = [
                                    'key' => $key,
                                    'language' => $language,
                                    'value' => $val2
                                ];*/
                                $insert[] = '('.implode(',', [$currenId, '"'.$key.'"', '"'.$language.'"', '"'.$val2.'"']).')';
                            }
                        }
                    }
                    else if (!empty($val)) {
                        $insert[] = '('.implode(',', [$currenId, '"'.$key.'"', 'null', '"'.$val.'"']).')';
                        /*$data[$currenId][] = [
                            'key' => $key,
                            'language' => null,
                            'value' => $val
                        ];*/
                    }
                }
            }

            DB::statement(
                'INSERT INTO '.config('dbi.tablenames.index_'.$entity).'
                ('.$id_base .', data_key, data_language, data_value)
                VALUES '.implode(",",$insert).'
                ON DUPLICATE KEY UPDATE '.$id_base.' = VALUES('.$id_base.'), data_key = VALUES(data_key), data_language = VALUES(data_language)'
            );
            // Write to Index
            /*foreach ($data as $d) {
                DB::table(config('dbi.tablenames.index_'.$entity))
                ->updateOrInsert([[
                    $id_base        => $currenId,
                    'data_key'      => $d['key'],
                    'data_language' => $d['language']
                ], [
                    $id_base        => $currenId,
                    'data_key'      => $d['key'],
                    'data_language' => $d['language'],
                    'data_value'    => $d['value']
                ]]);
            }*/

            //INSERT INTO table (id, name, age) VALUES(1, "A", 19) ON DUPLICATE KEY UPDATE    name="A", age=19
            /*INSERT INTO TABLE (id, name, age) VALUES (1, "A", 19), (2, "B", 17), (3, "C", 22)
ON DUPLICATE KEY UPDATE
name = VALUES (name),*/

            if (!empty($id)) break;
        }

        // Get Data of related ID
        /*$query = DB::table(config('dbi.tablenames.'.$entity).' as base')
        ->leftJoin(config('dbi.tablenames.mints').' AS sm',       'sm.id',    '=',    'base.id_mint')
        ->leftJoin(config('dbi.tablenames.periods').' AS pm',     'pm.id',    '=',    'base.id_period')
        ->leftJoin(config('dbi.tablenames.persons').' AS sap',    'sap.id',   '=',    'base.id_authority_person')
        ->leftJoin(config('dbi.tablenames.tribes').' AS sat',     'sat.id',   '=',    'base.id_authority_group')
        ->leftJoin(config('dbi.tablenames.designs').' AS sdo',    'sdo.id',   '=',    'base.id_design_o')
        ->leftJoin(config('dbi.tablenames.designs').' AS sdr',    'sdr.id',   '=',    'base.id_design_r')
        ->leftJoin(config('dbi.tablenames.legends').' AS slo',    'slo.id',   '=',    'base.id_legend_o')
        ->leftJoin(config('dbi.tablenames.legends').' AS slr',    'slr.id',   '=',    'base.id_legend_r')
        ->select($select_statement)
        ->where('base.publication_state', '!=', 3);
        if (!empty($id)) $query->where('base.id', $id);
        $raw = $query->get();

        $raw = json_decode($raw, true);
        die('test:'.count($raw));
        $raw = empty($raw[0]) ? false : $raw[0];

        if (empty($raw)) return false;

        // flatten Data-Array
        foreach ($raw as $key => $val) {
            if (substr($val, 0, 2) === '{"' && substr($val, -1) === '}') {
                foreach (json_decode($val, true) as $language => $val2) {
                    if (!empty($val2)) {
                        $data[] = [
                            'key' => $key,
                            'language' => $language,
                            'value' => $val2
                        ];
                    }
                }
            }
            else if (!empty($val)) {
                $data[] = [
                    'key' => $key,
                    'language' => null,
                    'value' => $val
                ];
            }
        }

        // Write to Index
        foreach ($data as $d) {
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

        return true;*/
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
