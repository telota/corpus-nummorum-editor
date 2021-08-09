<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class typology implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        $query = DB::table(config('dbi.tablenames.typology').' AS t')
        ->leftJoin(config('dbi.tablenames.mints').' AS m', 'm.id', '=', 't.id_mint')
        ->leftJoin(config('dbi.tablenames.typology_text').' AS tt', 'tt.id_typology', '=', 't.id')
        /*->select([
            'id         AS id',
            'tribe_de   AS name_de',
            'tribe_en   AS name_en',
            'nomisma_id AS nomisma'
        ])*/;

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

            if (empty($items[$id])) $items[$id] = [];

            $items[$id]['id'] = $id;
            $items[$id]['author'] = $d['author'];
            $items[$id]['id_mint'] = $d['id_mint'];
            $items[$id]['mint'] = $d['mint'];
            $items[$id]['bibliography'] = $d['bibliography'];
            $items[$id]['links'] = $d['links'];

            $items[$id][$l.'_id'] = $d['id'];

            foreach ([
                'topography',
                'research',
                'typology',
                'metrology',
                'chronology',
                'special',
                'classic',
                'hellenistic',
                'imperial'
            ] as $section) $items[$id][$l.'_'.$section] = $d['section_'.$section];
        }

        return array_values($items);
    }

    public function input ($user, $input) {
        $validation = $this -> validateInput($input);

        if(empty($validation['error'][0])) {
            $input = $validation['input'];

            $ID = DB::transaction(function () use ($input) {
                /*$values = [
                    'id'            => $input['id'],
                    'tribe_de'      => $input['name_de'],
                    'tribe_en'      => $input['name_en'],
                    'nomisma_id'    => $input['nomisma']
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    return DB::table(config('dbi.tablenames.tribes')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.tribes')) -> where('id', $input['id']) -> update($values);
                    return $input['id'];
                }*/
            });

            return $ID;
        }
        // Validation Error
        else { return ['error' => $validation['error']]; }
    }

    public function delete ($user, $input) {
        /*DB::transaction(function () use ($input) {
            DB::table(config('dbi.tablenames.tribes')) -> where('id', $input['id']) -> delete();
        });*/

        return true;
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {
        /*if (empty($input['name_de'])) {
            $error[] = config('dbi.responses.validation.general.name_de');
        }*/

        // Return validated input
        if (empty($error)) {
            return ['input' => $input];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
