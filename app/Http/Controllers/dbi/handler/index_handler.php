<?php

namespace App\Http\Controllers\dbi\handler;

use DB;


class index_handler {

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
                /*$table_id = 'id_'.rtrim($entity, 's');

                $indexQuery->select([
                    'id',
                    'data_string AS vals'
                    $table_id.' AS id',
                    DB::Raw('json_arrayagg(
                        CONCAT_WS("::",
                            CONCAT_WS("_", data_key, data_language),
                            REPLACE(data_value, \'"\', "")
                        )
                    ) AS vals')
                ])
                ->from(config('dbi.tablenames.index_'.$entity).' AS concated_index');

                // Add where conditions if keys have to be excluded
                //if (!empty($excluded)) foreach ($excluded as $ex) $indexQuery->where('data_key', '!=', trim($ex));

                //$indexQuery->groupBy($table_id);*/

                $indexQuery->select(['id','data_string AS vals'])->from(config('dbi.tablenames.index_'.$entity).' AS concated_index');
            })

            // Add WhereConditions
            ->where(function ($queryByOr) use ($string, $isRegex, $isCs) {

                // Handle given input as int -> ID
                if (preg_match('/^[0-9]+$/', $string)) {
                    $queryByOr->where('id', $string);
                }

                // Handle given input as group of strings
                else {
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
            $string = '\&\&'.$split[0].'[^\&\&]*'.$split[1];
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

    public function updateIndex ($entity, $id = null, $echo = false) {

        // Check if primary or secondary entity is called
        $primaries = ['coins', 'types'];
        if ($entity === 'types') unset($primaries[0]);
        if ($entity === 'coins') unset($primaries[1]);
        $single = count($primaries) === 1 ? true : false;

        // Iterate over primary entities
        foreach ($primaries as $primary) {
            $base_id = 'id_'.rtrim($primary, 's');

            // Get instructions
            $secondaries = $this->subqueryInstructions($entity, $single, $base_id);

            if (!empty($secondaries[0])) {

                if (empty($id) || $single === false) {
                    $start = 0;
                    $end = DB::table(config('dbi.tablenames.'.$primary))->select(DB::Raw('MAX(id) AS ending'))->get();
                    $end = json_decode($end, true);
                    $end = intval($end[0]['ending']) + 1;
                    $interval = 2000;
                }
                else {
                    $start = $id;
                    $end = $id + 1;
                    $interval = 1;
                }

                for ($i = $start; $i < $end; $i += $interval) {
                    $currentInterval = $i + $interval;
                    if ($echo !== false) echo "\t".($i + 1).' - '.$currentInterval.' / '.$end."\n";
                    $subqueries = [];

                    // Build Subquery Statements
                    foreach ($secondaries as $s) $subqueries[] = 'SELECT
                        base.id as id,
                        "'.$s['key'].'" as data_key,
                        '.(empty($s['language']) ? 'null' : ('"'.$s['language'].'"')).' as data_language,
                        '.$s['value'].' as data_value
                    FROM '.$s['sec_table'].' as sec '.
                    (empty($s['joins']) ? '' : $s['joins']).'
                    LEFT JOIN '.config('dbi.tablenames.'.$primary).' as base ON '.$s['sec_id'].' = '.(empty($s['ref_id']) ? 'sec.id' : $s['ref_id']).'
                    WHERE base.publication_state != 3 && base.id >= '.$i.' && base.id < '.$currentInterval.
                    (!empty($id) && $single === false ? (' && '.$s['sec_id'].' = '.$id) : '').
                    (!empty($s['conditions']) ? (' && '.$s['conditions']) : '').
                    (!empty($s['groupBy']) ? (' GROUP BY '.$s['groupBy']) : '');

                    // Build final Statement
                    $statement = 'INSERT INTO '.config('dbi.tablenames.index_'.$primary).' (id, data_string)
                        SELECT * FROM (
                            SELECT
                                sq.id as id,
                                CONCAT(
                                    "&&",
                                    GROUP_CONCAT(
                                        IF(sq.data_value > "", CONCAT(
                                            CONCAT_WS("_", sq.data_key, sq.data_language), "::"
                                        ), NULL),
                                        REPLACE(REPLACE(sq.data_value, "&&", ""), "::", "")
                                        SEPARATOR "&&"
                                    ),
                                    "&&"
                                ) AS concated
                            FROM (
                                ('.implode(') UNION (', $subqueries).')
                            ) as sq
                            GROUP BY sq.id
                            HAVING concated IS NOT NULL
                        ) as fq
                    ON DUPLICATE KEY UPDATE data_string = fq.concated';

                    // Execute Statements
                    DB::statement('SET SESSION group_concat_max_len = 1000000;');
                    DB::statement($statement);

                }
            }
        }

        return true;
    }

    public function subqueryInstructions ($entity, $single, $base_id) {
        $divider = '"||"';
        $secondaries = [];

        // Authorities/Coinage
        if ($entity === 'authorities' || $single === true) $secondaries[] = [
            'key'       => 'coinage',
            'value'     => 'CONCAT_WS(
                '.$divider.',
                sec.authority_de,
                IF(sec.authority_en = sec.authority_de, NULL, sec.authority_en)
            )',
            'sec_table' => config('dbi.tablenames.authorities'),
            'sec_id'    => 'base.id_authority'
        ];

        // mints
        if ($entity === 'mints' || $single === true) $secondaries[] = [
            'key'       => 'mint',
            'value'     => 'CONCAT_WS(
                '.$divider.',
                sec.nomisma_id,
                sec.mint,
                (
                    SELECT GROUP_CONCAT(DISTINCT mnt.mint_name SEPARATOR '.$divider.')
                    FROM '.config('dbi.tablenames.mints_nomisma_text').' AS mnt
                    WHERE mnt.nomisma_id_mint = sec.nomisma_id && mnt.mint_name != sec.mint && mnt.mint_name != sec.placename && (mnt.language = "de" || mnt.language = "en")
                ),
                sec.placename
            )',
            'sec_table' => config('dbi.tablenames.mints'),
            'sec_id'    => 'base.id_mint'
        ];

        // Region
        if ($entity === 'regions' || $single === true) $secondaries[] = [
            'key'       => 'region',
            'value'     => 'CONCAT_WS(
                '.$divider.',
                rts.nomisma_id_region,
                IF(rts.nomisma_id_region = r.region, NULL, r.region),
                r.de, IF(rts.nomisma_id_region = r.en, NULL, r.en)
            )',
            'sec_table' => config('dbi.tablenames.mints'),
            'sec_id'    => 'base.id_mint',
            'joins'     => implode("\n", [
                'LEFT JOIN '.config('dbi.tablenames.regions_to_subregions').' AS rts ON rts.nomisma_id_region = sec.imported_nomisma_subregion',
                'LEFT JOIN '.config('dbi.tablenames.regions').' AS r ON r.id = rts.id_region'
            ])
        ];

        // Individuals
        if ($entity === 'persons' || $single === true) {

            // Ruler
            $secondaries[] = [
                'key'       => 'ruler',
                'value'     => 'CONCAT_WS(
                    '.$divider.',
                    sec.person,
                    sec.nomisma_id_person,
                    sec.alias
                )',
                'sec_table' => config('dbi.tablenames.persons'),
                'sec_id'    => 'base.id_authority_person'
            ];

            // Persons
            $secondaries[] = [
                'key'       => 'persons',
                'value'     => 'GROUP_CONCAT(DISTINCT CONCAT_WS(
                    '.$divider.',
                    p.person,
                    p.nomisma_id_person,
                    p.alias
                ) SEPARATOR '.$divider.')',
                'sec_table' => config('dbi.tablenames.'.$entity.'_to_persons'),
                'sec_id'    => 'base.id',
                'ref_id'    => 'sec.'.$base_id,
                'joins'     => 'LEFT JOIN '.config('dbi.tablenames.persons').' AS p ON p.id = sec.id_person',
                'groupBy'   => 'base.id'
            ];
        }

        // Tribes
        if ($entity === 'tribes' || $single === true) {
            $secondaries[] = [
                'key'       => 'tribe',
                'value'     => 'CONCAT_WS(
                    '.$divider.',
                    sec.tribe_de,
                    IF(sec.tribe_en = sec.tribe_de || sec.tribe_en = sec.nomisma_id, NULL, sec.tribe_en),
                    sec.nomisma_id
                )',
                'sec_table' => config('dbi.tablenames.tribes'),
                'sec_id'    => 'base.id_authority_group'
            ];
        }

        // Standards
        if ($entity === 'standards' || $single === true) {
            $secondaries[] = [
                'key'       => 'standard',
                'value'     => 'CONCAT_WS(
                    '.$divider.',
                    sec.standard_de,
                    sec.standard_en,
                    sec.nomisma_id
                )',
                'sec_table' => config('dbi.tablenames.standards'),
                'sec_id'    => 'base.id_standard'
            ];
        }

        // Denominations
        if ($entity === 'denominations' || $single === true) {
            $secondaries[] = [
                'key'       => 'denomination',
                'value'     => 'CONCAT_WS(
                    '.$divider.',
                    sec.denomination_de,
                    sec.denomination_en,
                    sec.nomisma_id
                )',
                'sec_table' => config('dbi.tablenames.denominations'),
                'sec_id'    => 'base.id_denomination'
            ];
        }

        // Materials
        if ($entity === 'materials' || $single === true) {
            $secondaries[] = [
                'key'       => 'material',
                'value'     => 'CONCAT_WS(
                    '.$divider.',
                    sec.material_de,
                    sec.material_en,
                    sec.nomisma_id
                )',
                'sec_table' => config('dbi.tablenames.materials'),
                'sec_id'    => 'base.id_material'
            ];
        }

        // Period
        if ($entity === 'periods' || $single === true) $secondaries[] = [
            'key'       => 'period',
            'value'     => 'CONCAT_WS(
                '.$divider.',
                sec.period_de,
                sec.period_en,
                sec.nomisma_id
            )',
            'sec_table' => config('dbi.tablenames.periods'),
            'sec_id'    => 'base.id_period'
        ];

        // References
        /*if ($entity === 'literature' || $single === true) $secondaries[] = [
            'key'       => 'literature',
            'value'     => 'GROUP_CONCAT(DISTINCT CONCAT_WS(
                ", ",
                CONCAT(
                    zi.author, ", ",
                    IFNULL(zi.shorttitle, zi.title),
                    IF(zi.volume > "", CONCAT(" ", zi.volume), ""),
                    IF(zi.place > "" || zi.year_published > "", ",", ""),
                    IF(zi.place > "", CONCAT(" ", zi.place), ""),
                    IF(zi.year_published > "", CONCAT(" ", zi.year_published), "")
                ),
                CONCAT(
                    IF(sec.page > "", CONCAT("S. ", sec.page), ""),
                    IF(sec.number > "", CONCAT( IF( sec.page > "", ", ", ""), "Nr. ", sec.number), ""),
                    IF(sec.plate > "", CONCAT( IF( sec.page > "" || sec.number > "", ", ", ""), "Taf. ", sec.plate), ""),
                    IF(sec.picture > "", CONCAT(", Abb. ", sec.picture), ""),
                    IF(sec.annotation > "", CONCAT(", FN ", sec.annotation), "")
                )
            ) SEPARATOR '.$divider.')',
            'sec_table' => config('dbi.tablenames.'.$entity.'_to_zotero'),
            'sec_id'    => 'base.id',
            'ref_id'    => 'sec.'.$base_id,
            'joins'     => 'LEFT JOIN '.config('dbi.tablenames.zotero').' AS zi ON zi.zotero_id = sec.zotero_id',
            'conditions' => 'sec.this_'.rtrim($entity, 's').' = 1',
            'groupBy'   => 'base.id'
        ];*/

        // Groups
        if ($entity === 'objectgroups' || $single === true) $secondaries[] = [
            'key'       => 'objectgroup',
            'value'     => 'GROUP_CONCAT(DISTINCT obj.objectgroup SEPARATOR '.$divider.')',
            'sec_table' => config('dbi.tablenames.'.$entity.'_to_objectgroups'),
            'sec_id'    => 'base.id',
            'ref_id'    => 'sec.'.$base_id,
            'joins'     => 'LEFT JOIN '.config('dbi.tablenames.objectgroups').' AS obj ON obj.id = sec.id_objectgroup',
            'groupBy'   => 'base.id'
        ];


        if ($single === true) {
            foreach (['de', 'en'] as $lang) {
                // Pecularities
                $secondaries[] = [
                    'key'       => 'pecularities',
                    'language'  => $lang,
                    'value'     => 'sec.pecularities_'.$lang,
                    'sec_table' => config('dbi.tablenames.'.$entity),
                    'sec_id'    => 'base.id'
                ];

                // Comment
                $secondaries[] = [
                    'key'       => 'comment',
                    'language'  => $lang,
                    'value'     => 'sec.comment_public'.($lang === 'en' ? ('_'.$lang) : ''),
                    'sec_table' => config('dbi.tablenames.'.$entity),
                    'sec_id'    => 'base.id'
                ];
            }
        }

        // Side related
        foreach (['o', 'r'] as $index => $side) {

            // Legends
            if ($entity === 'legends' || $single === true) $secondaries[] = [
                'key'       => 'legend',
                'value'     => 'CONCAT_WS(
                    '.$divider.',
                    sec.legend,
                    sec.legend_sort_basis,
                    sec.keywords
                )',
                'sec_table' => config('dbi.tablenames.legends'),
                'sec_id'    => 'base.id_legend_'.$side
            ];

            foreach (['de', 'en'] as $lang) {
                // Designs
                if ($entity === 'designs' || $single === true) $secondaries[] = [
                    'key'       => 'design_'.$side,
                    'language'  => $lang,
                    'value'     => 'sec.design_'.$lang,
                    'sec_table' => config('dbi.tablenames.designs'),
                    'sec_id'    => 'base.id_design_'.$side
                ];

                // Symbol
                if ($entity === 'symbols' || $single === true) $secondaries[] = [
                    'key'       => 'symbol_'.$side,
                    'language'  => $lang,
                    'value'     => 'GROUP_CONCAT(DISTINCT symbol_'.$lang.' SEPARATOR '.$divider.')',
                    'sec_table' => config('dbi.tablenames.'.$entity.'_to_symbols'),
                    'sec_id'    => 'base.id',
                    'ref_id'    => 'sec.'.$base_id,
                    'joins'     => 'LEFT JOIN '.config('dbi.tablenames.symbols').' AS s ON s.id = sec.id_symbol && sec.side = '.$index,
                    'groupBy'   => 'base.id'
                ];
            }
        }

        // Owner
        if ($entity === 'coins') $secondaries[] = [
            'key'       => 'owner',
            'value'     => 'CONCAT_WS(
                ", ",
                UPPER(sec.country),
                sec.city,
                IF(sec.is_name_public = 1, sec.owner, null),
                IF(sec.is_name_public = 1, base.collection_id, null)
            )',
            'sec_table' => config('dbi.tablenames.owners'),
            'sec_id'    => 'base.id_owner_original'
        ];

        return $secondaries;
    }
}
