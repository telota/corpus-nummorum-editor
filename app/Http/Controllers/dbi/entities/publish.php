<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class publish implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        die (abort(404, 'Not supported!'));
    }

    public function input ($user, $input) {
        // Throw Error for not supported publication state (mode)
        if (!empty($input['mode']) || !in_array($input['mode'], [0, 1, 2, 3])) $this->validateInput('no valid mode provided!');

        // Throw Error for not supported entity
        if (empty($input['entity']) || !in_array($input['entity'], ['coins', 'types'])) $this->validateInput('no valid entity provided!');

        if (empty($input['items'])) return $this->validateInput('no items provided!');
        else if (!is_array($input['items'])) $input['items'] = [$input['items']];

        // If a coin should be published check if linked type is already published
        if ($input['entity'] === 'coins' && $input['mode'] === 1) {

            // Get Coin-Type-Links
            $checks = DB::table(config('dbi.tablenames.coins').' AS c')
                -> leftJoin(config('dbi.tablenames.coins_inherit').' AS cti',   'cti.id',       '=', 'c.id')
                -> leftJoin(config('dbi.tablenames.types').' AS t',             'cti.id_type',  '=', 't.id')
                -> select([
                    'c.id                   AS id',
                    't.id                   AS id_type',
                    't.publication_state    AS type_public',
                    DB::Raw('IF(cti.id IS NULL, 0, 1) AS coin_inherited')
                ])
                -> whereIn('c.id', $input['items'])
                -> get();
            $checks = json_decode($checks, true);

            $items_ok = [];
            $items_not_ok = [];

            // Check inheriting Type
            foreach ($checks as $check) {
                if ($check['coin_inherited'] === 1) {
                    if ($check['type_public'] === 1 ) {
                        $items_ok[] = $check['id'];
                    }
                    else {
                        $items_not_ok[] = [
                            'coin' => $check['id'],
                            'type' => $check['id_type']
                        ];
                    }
                }
                else $items_ok[] = $check['id'];
            }
        }
        else $items_ok = $input['items'];

        sort($items_ok);

        // Update items
        if (!empty($items_ok[0])) {
            DB::table(config('dbi.tablenames.'.$input['entity']))
                -> whereIn('id', $items_ok)
                -> update(['publication_state' => $input['mode']]);
        }

        // Return OK
        if (empty($items_not_ok)) $this->validateInput([
            'success' => true,
            'ok' => $items_ok
        ]);

        // Sort array of errors
        $column = array_column($items_not_ok, 'type');
        array_multisort($column, SORT_ASC, $items_not_ok);

        // Return failure
        return $this->validateInput([
            'success' => true,
            'ok' => $items_ok,
            'not_ok' => $items_not_ok
        ]);
    }

    public function delete ($user, $input) {
        die (abort(404, 'Not supported!'));
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {
        if (is_array($input)) die (json_encode(is_array($input) ? $input : ['error' => $input]));

    }
}
