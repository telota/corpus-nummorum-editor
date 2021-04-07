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
        // Check if given Entity
        if (in_array($input['entity'], ['coins', 'types'])) {

            // New Links to write
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

                $items_ok = [];
                $items_not_ok = [];

                foreach (json_decode($checks, TRUE) as $check) {
                    if ($check['coin_inherited'] === 1) {
                        if ($check['type_public'] === 1 ) { 
                            if (!in_array($check['id'], $items_ok) && !in_array($check['id'], $items_not_ok) ) { 
                                $items_ok[] = $check['id'];
                            }
                        }
                        else {
                            $items_not_ok[] = '(cn coin '.$check['id'].' / cn type '.$check['id_type'].')';
                        }
                    }
                    else { 
                        if (!in_array($check['id'], $items_ok) ) { 
                            $items_ok[] = $check['id']; 
                        } 
                    }
                }
            }
            else {
                $items_ok = $input['items'];
            }

            if (!empty($items_ok[0])) {
                DB::table(config('dbi.tablenames.'.$input['entity']))
                    -> whereIn('id', $input['items'])
                    -> update(['publication_state' => $input['mode']]);
            }
        }

        // Throw Error for not supported entity
        else { die (abort(404, 'Not supported!')); }

        // Return OK
        if (empty($items_not_ok)) {
            if (!empty($input['items'][0]) && !empty($input['items'][1])) {
                $message = $input['items'][0].' - '.end($input['items']);
            }
            else if (!empty($input['items'][0])) {
                $message = $input['items'][0];
            }
            die(json_encode(['success' => [
                'de' => $message.' wurde publiziert',
                'en' => $message.' has been published'
            ]]));
        }

        // Throw Error for non public inheriting types
        else {
            die(json_encode([
                'de' => 'Der Typ für folgende erbende Münzen muss zunächst veröffentlicht werden: '.implode(', ', $items_not_ok),
                'en' => 'The inheriting type of following coins is needs to published first: '.implode(', ', $items_not_ok),
            ]));
        }
    }

    public function delete ($user, $input) {
        die (abort(404, 'Not supported!'));
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {}
}
