<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class typology implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        $query = DB::table(config('dbi.tablenames.typology').' AS t')
        ->leftJoin(config('dbi.tablenames.typology_text').' AS tt', 'tt.id_typology', '=', 't.id')
        ->select([
            '*',
            /*DB::Raw('(
                SELECT JSON_ARRAYAGG(JSON_OBJECT(
                    "id", m.id,
                    "name", m.mint,
                    "nomisma", m.nomisma_id
                ))
                FROM '.config('dbi.tablenames.typology_mints').' AS ttm
                LEFT JOIN '.config('dbi.tablenames.mints').' AS m ON m.id = ttm.id_mint
                WHERE ttm.id_typology = t.id && m.nomisma_id IS NOT NULL
            ) AS mints'),*/
            DB::Raw('(
                SELECT JSON_ARRAYAGG(JSON_OBJECT(
                    "entity", ttn.entity,
                    "id", ttn.nomisma_id
                ))
                FROM '.config('dbi.tablenames.typology_nomisma').' AS ttn
                WHERE ttn.id_typology = t.id
            ) AS nomisma'),
        ]);

        // Set condition if ID is given
        if (!empty($id)) $query->where('t.id', $id);

        $dbi = $query->get();
        $dbi = json_decode($dbi, TRUE);
        $items = [];

        foreach($dbi as $d) {
            $id = $d['id_typology'];
            unset($d['id_typology']);
            $l = $d['language'];
            unset($d['language']);

            if (empty($items[$id])) {
                $items[$id] = [];

                $items[$id]['id'] = $id;
                $items[$id]['author'] = $d['author'];

                $raw_nomisma = json_decode($d['nomisma'], TRUE);
                $raw_nomisma = empty($raw_nomisma) ? [] : $raw_nomisma;
                $nomisma = [];

                foreach($raw_nomisma as $n) $nomisma[$n['id']] = $n;
                ksort($nomisma);
                $nomisma = array_values($nomisma);
                $items[$id]['nomisma'] = $nomisma;
                $items[$id]['nomisma_concated'] = empty($nomisma[0]) ? $id : implode(', ', array_map(function ($n) { return $n['id']; }, $nomisma));

                $items[$id]['bibliography'] = $d['bibliography'];
                $items[$id]['links'] = $d['links'];
            }

            $items[$id][$l.'_id'] = $d['id'];
            foreach ([
                'label',
                'topography',
                'research',
                'typology',
                'metrology',
                'chronology',
                'special',
                'classic',
                'hellenistic',
                'imperial'
            ] as $section) {
                $items[$id][$l.'_'.$section] = empty($d[($section === 'label' ? '' : 'section_').$section]) ? null : trim($d[($section === 'label' ? '' : 'section_').$section]);
            }
        }

        return array_values($items);
    }

    public function input ($user, $input) {
        $validation = $this->validateInput($input);
        //die(print_r($validation));

        if(empty($validation['error'][0])) {
            $input = $validation['input'];
            //die (print_r($input));

            $ID = DB::transaction(function () use ($input) {
                $values = [
                    'bibliography'  => $input['bibliography'],
                    'links'         => $input['links']
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    $ID = DB::table(config('dbi.tablenames.typology'))->insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.typology'))->where('id', $input['id'])->update($values);
                    $ID = $input['id'];
                }

                // Text
                foreach ($input['strings'] as $language => $strings) {
                    $ids = [
                        'id_typology'   => $ID,
                        'language'      => $language
                    ];
                    $strings += $ids;

                    DB::table(config('dbi.tablenames.typology_text'))->updateOrInsert($ids, $strings);
                }

                // Nomisma
                DB::table(config('dbi.tablenames.typology_nomisma'))->where('id_typology', $ID)->delete();

                foreach ($input['nomisma'] as $nomisma) {
                    if (!empty($nomisma['id'])) {
                        DB::table(config('dbi.tablenames.typology_nomisma'))->insert([
                            'id_typology'   => $ID,
                            'entity'        => $nomisma['entity'],
                            'nomisma_id'    => $nomisma['id']
                        ]);
                    }
                }

                return $ID;
            });

            return $ID;
        }
        // Validation Error
        else { return ['error' => $validation['error']]; }
    }

    public function delete ($user, $input) {
        DB::transaction(function () use ($input) {
            DB::table(config('dbi.tablenames.typology_nomisma'))->where('id_typology', $input['id'])->delete();
            DB::table(config('dbi.tablenames.typology_mints'))->where('id_typology', $input['id'])->delete();
            DB::table(config('dbi.tablenames.typology_persons'))->where('id_typology', $input['id'])->delete();
            DB::table(config('dbi.tablenames.typology'))->where('id', $input['id'])->delete();
        });

        return true;
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {
        $nomisma = empty($input['nomisma'][0]) ? null : implode('', array_map(function ($item) { return $item['id']; }, $input['nomisma']));

        if (empty($nomisma)) $error[] = config('dbi.responses.validation.general.nomisma');
        if (empty($input['en_label'])) $error[] = config('dbi.responses.validation.general.name_en');
        if (empty($input['de_label'])) $error[] = config('dbi.responses.validation.general.name_de');

        // Return validated input
        if (empty($error)) {
            $input['strings'] = [];

            foreach(['en', 'de'] as $language) {
                $input['strings'][$language] = [];

                foreach ([
                    'label',
                    'topography',
                    'research',
                    'typology',
                    'metrology',
                    'chronology',
                    'special',
                    'classic',
                    'hellenistic',
                    'imperial'
                ] as $section) {
                    $key = $language.'_'.$section;
                    $input['strings'][$language][($section === 'label' ? '' : 'section_').$section] = empty($input[$key]) ? NULL : trim($input[$key]);
                    unset($input[$key]);
                }
            }
            return ['input' => $input];
        }
        // Return Error
        else return ['error' => $error];
    }
}
