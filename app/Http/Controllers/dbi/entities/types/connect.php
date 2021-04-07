<?php

namespace App\Http\Controllers\dbi\entities\types;

use DB;


class connect {

    public function link ($user, $input) {
        // JK: Ensure the connection does not exist before writing a new one.
        if (DB::table ($this->DB.'data_coins')
            -> where ([
                    ['id', $input['id_coin']]
                ])
            -> exists()
        ) {
            if (
                DB::table($table)
                    -> where([
                            ['id_type',  $input['id_type']],
                            ['id_coin',  $input['id_coin']]
                        ])
                    -> doesntExist()
            ) {
                $ID = DB::table($table)
                    -> insertGetID([
                        'id_type' => $input['id_type'],
                        'id_coin' => $input['id_coin']
                    ]);

                $message = config('cn_admin_feedback.ok_created').'ID '.$input['id_type'].' was linked to '.$input['id_coin'].'.';

                return [
                    'message' => $message, 
                    'id' => $input['id_type']
                ];
            }
            else
            {
                die (config('cn_admin_feedback.validation_issue').'This Connection already exists!');
            }
        }
        else {
            die(config('cn_admin_feedback.validation_issue').'This Coin does not exist!');
        }
    }
    

    public function unlink ($user, $input) {
        if (
            DB::table ($table)
                -> where([
                        ['id_type',  $input['id_type']],
                        ['id_coin',  $input['id_coin']]
                    ])
                -> exists()
        ) {
            DB::table ($table)
                -> where([
                        ['id_type',  $input['id_type']],
                        ['id_coin',  $input['id_coin']]
                    ])
                -> delete();

                $feedback = config('cn_admin_feedback.ok_created'). 'ID '.$input['id_type'].' was unlinked from '.$input['id_coin'].'.';
            
            return config('cn_admin_feedback.ok_updated').$feedback;
        }
        else {
            die (config('cn_admin_feedback.validation_issue').'This Connection does not exist!');
        }
    }
}