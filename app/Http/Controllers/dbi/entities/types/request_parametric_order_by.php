<?php

namespace App\Http\Controllers\dbi\entities\types;

use DB;


class request_parametric_order_by {

    public function instructions () {
        $db = 'cn_data.';

        return [
            'id' => 't.id',

            'created' => 't.created_at',
        
            'updated' => 't.updated_at',
        
            'date' => [
                'asc'   => 't.date_start',
                'desc'  => 't.date_end'
            ],
            
            'issuer' => '(
                SELECT p.person 
                FROM '.config('dbi.tablenames.persons').' AS p 
                WHERE p.id = t.id_issuer
            )',
            
            'ruler' => '(
                SELECT p.person 
                FROM '.config('dbi.tablenames.persons').' AS p 
                WHERE p.id = t.id_authority_person
            )',
            
            'mint' => '(
                SELECT m.mint 
                FROM '.config('dbi.tablenames.mints').' AS m 
                WHERE m.id = t.id_mint
            )',
        
            'person' => '(
                SELECT p.person 
                FROM '.config('dbi.tablenames.types_to_persons').'  AS ttp
                LEFT JOIN '.config('dbi.tablenames.persons').'      AS p ON p.id = ttp.id_person
                WHERE ttp.id_type = t.id
                ORDER BY p.person IS NULL, p.person asc
                LIMIT 1
            )'
        ];
    }
}