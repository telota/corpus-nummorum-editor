<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB; 


class link implements dbiInterface { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        die (abort(404, 'Not supported!'));
    }

    public function input ($user, $input) {
        if (in_array($input['add_entity'], ['coins', 'types', 'monograms', 'persons', 'symbols'])) {
            if ($input['mode'] === 'link') {
                $handler = new linkHandler;
                return $handler -> link($user, $input);
            }
            else if ($input['mode'] === 'unlink') { 
                $handler = new linkHandler;           
                return $handler -> unlink($user, $input);
            }
            else {
                die (abort(404, 'Not supported!')); 
            }
        }
        else {
            die (abort(404, $input['add_entity'].' and '.$input['entity'].' not supported!'));
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

class linkHandler {
    public function link ($user, $input) {
        if (empty($input['id']) || empty($input['add_id'])) {
            $error = config('dbi.responses.linking.no_id');
        }
        else {
            $where = [
                'id_'.rtrim($input['entity'], 's') => $input['id'],
                'id_'.rtrim($input['add_entity'], 's') => $input['add_id'],
            ];
            if (DB::table(config('dbi.tablenames.'.$input['entity'])) -> where('id', $input['id']) -> doesntExist()) {
                foreach (config('dbi.responses.linking.id_not_existing') AS $key => $val) {
                    $error[$key] = 'cn '.$input['entity'].' '.$input['id'].' '.$val;
                }
            }
            elseif (in_array($input['add_entity'], ['monograms', 'symbols'])) {
                if (empty($input['position'])) {
                    $error = config('dbi.responses.linking.no_position');
                }
                elseif (!isset($input['side'])) {
                    $error = config('dbi.responses.linking.no_side');
                }
                else {
                    $where['id_position'] = $input['position'];
                    $where['side'] = $input['side'];
                }
            }
            elseif (in_array($input['add_entity'], ['persons'])) {
                if (empty($input['function'])) {
                    $error = config('dbi.responses.linking.no_function');
                }
                else {
                    $where['id_function'] = $input['function'];
                }
            }
        }

        if (empty($error)) {
            $table = $input['entity'] === 'types' && $input['add_entity'] === 'coins' ? 'coins_to_types' : ($input['entity'].'_to_'.$input['add_entity']);
            if (!empty(config('dbi.tablenames.'.$table))) {
                $table = config('dbi.tablenames.'.$table);
                DB::table($table) -> updateOrInsert($where, $where);
    
                foreach (config('dbi.responses.linking.unlinked') AS $key => $val) {
                    $message[$key] = 'cn '.$input['add_entity'].' '.$input['add_id'].' '.$val.' cn '.$input['entity'].' '.$input['id'];
                }
                die(json_encode(['success' => $message]));
            }
            else {
                die (abort(404, $table.' not found!'));
            }
        }
        else {
            die(json_encode($error));
        }
    }

    public function unlink ($user, $input) {
        $table = $input['entity'] === 'types' && $input['add_entity'] === 'coins' ? 'coins_to_types' : ($input['entity'].'_to_'.$input['add_entity']);
        if (!empty(config('dbi.tablenames.'.$table))) {
            $table = config('dbi.tablenames.'.$table);
            DB::table ($table)
                -> where([
                        ['id_'.rtrim($input['entity'], 's'),  $input['id']],
                        ['id_'.rtrim($input['add_entity'], 's'),  $input['add_id']]
                    ])
                -> delete();

            foreach (config('dbi.responses.linking.unlinked') AS $key => $val) {
                $message[$key] = 'cn '.$input['add_entity'].' '.$input['add_id'].' '.$val.' cn '.$input['entity'].' '.$input['id'];
            }
            die(json_encode(['success' => $message]));
        }
        else {
            die (abort(404, $table.' not found!'));
        }
    }
}
