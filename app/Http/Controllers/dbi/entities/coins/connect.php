<?php

namespace App\Http\Controllers\dbi\entities\coins;

use DB;


class connect {

    public function link ($user, $input) {        
        if (empty($input['id_type'])) {
            $error = config('dbi.responses.linking.no_id');
        }
        else if (DB::table(config('dbi.tablenames.types')) -> where('id', $input['id_type']) -> doesntExist()) {
            foreach (config('dbi.responses.linking.id_not_existing') AS $key => $val) {
                $error[$key] = 'cn type '.$input['id_type'].' '.$val;
            }
        }
        if (!empty($error)) {
            die(json_encode($error));
        }
        
        $data = [
            'id_type'                   => $input['id_type'],
            'date_inherited'            => 0,
            'period_inherited'          => 0,
            'legend_o_inherited'        => 0,
            'symbol_o_inherited'        => 0,
            'design_o_inherited'        => 0,
            'monogram_o_inherited'      => 0,
            'legend_r_inherited'        => 0,
            'symbol_r_inherited'        => 0,
            'design_r_inherited'        => 0,
            'monogram_r_inherited'      => 0,
            'issuer_inherited'          => 0,
            'authority_inherited'       => 0,
            'authority_person_inherited'=> 0,
            'authority_group_inherited' => 0,
            'mint_inherited'            => 0,
            'material_inherited'        => 0,
            'standard_inherited'        => 0,
            'denomination_inherited'    => 0,
            'person_inherit'            => 0,
            'comment_private_inherited' => 0,
            'comment_public_inherited'  => 0,
        ];

        DB::table('cn_data.data_coins_to_types_inherit') -> updateOrInsert(['id' => $input['id_coin']], $data);
        DB::table('cn_data.data_coins_to_types') -> updateOrInsert(
            ['id_coin' => $input['id_coin'], 'id_type' => $input['id_type']],
            ['id_coin' => $input['id_coin'], 'id_type' => $input['id_type']]
        );

        foreach (config('dbi.responses.linking.linked') AS $key => $val) {
            $message[$key] = 'cn coin '.$input['id_coin'].' '.$val.' cn type '.$input['id_type'];
        }
        die(json_encode(['success' => $message]));
    }
    

    public function unlink ($user, $input) {
        DB::table ('cn_data.data_coins_to_types_inherit')
            -> where ('id', $input['id_coin'])
            -> delete ();
        
        DB::table ('cn_data.data_coins_to_types')
            -> where ([
                ['id_coin', $input['id_coin']], 
                ['id_type', $input['id_type']]
            ])
            -> delete ();

        foreach (config('dbi.responses.linking.unlinked') AS $key => $val) {
            $message[$key] = 'cn coin '.$input['id_coin'].' '.$val.' cn type '.$input['id_type'];
        }
        die(json_encode(['success' => $message]));
    }
}