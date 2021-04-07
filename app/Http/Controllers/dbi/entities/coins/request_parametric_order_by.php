<?php

namespace App\Http\Controllers\dbi\entities\coins;

use DB;


class request_parametric_order_by {

    public function instructions () {
        $db = 'cn_data.';

        return [
            'id' => 'c.id',

            'created' => 'c.created_at',
        
            'updated' => 'c.updated_at',
        
            'date' => [
                'asc'   => 'c.date_start',
                'desc'  => 'c.date_end'
            ],
            
            'issuer' => '(
                SELECT p.person 
                FROM '.config('dbi.tablenames.persons').' AS p 
                WHERE p.id = c.id_issuer
            )',
            
            'ruler' => '(
                SELECT p.person 
                FROM '.config('dbi.tablenames.persons').' AS p 
                WHERE p.id = c.id_authority_person
            )',
            
            'mint' => '(
                SELECT m.mint 
                FROM '.config('dbi.tablenames.mints').' AS m 
                WHERE m.id = c.id_mint
            )',
        
            'person' => '(
                SELECT p.person 
                FROM '.config('dbi.tablenames.coins_to_persons').'  AS ctp
                LEFT JOIN '.config('dbi.tablenames.persons').'      AS p ON p.id = ctp.id_person
                WHERE ctp.id_coin = c.id
                ORDER BY p.person IS NULL, p.person asc
                LIMIT 1
            )',
        
            'diameter' =>  'c.diameter_max',
        
            'weight' =>  'c.weight'
        ];
    }
}