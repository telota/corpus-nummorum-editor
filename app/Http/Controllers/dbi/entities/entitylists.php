<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;
use Request;


class entitylists implements dbiInterface  { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {        
        $input = Request::post();

        $i = config('dbi.entitylists.'.$id );
        if (empty($i)) {
            die (abort(404, 'Not supported!'));
        }
        
        // JK: Build Select Array
        foreach ($i['select'] as $iterate) {
            $select[] = $iterate['raw'] == 1 ? DB::raw($iterate['db']) : $iterate ['db'];
        }

        // JK: Get selected ID
        if (!empty($input['id']))
        {
            $dbi = DB::table($i['table'])
                -> select($select)
                -> where($i['id'], $input['id'])
                -> get();
            
            $post_id = json_decode($dbi, TRUE);            
        }

        // JK: Get List
        $query = DB::table ($i['table']) -> select($select);

        // If where is set
        if (isset($i['where'])) {
            $query -> where($i['where']);
        }
        
        // Where Conditions
        if (isset($input['string'])) {
            if (isset ($input['string'][0])) {
                $strings = $i['string'];
                $posts = $input['string'];
                
                if (!empty ($input['id'])) { 
                    $query -> where([[$i['id'], '!=', $input['id']]]); 
                }

                if (!isset($strings[1])) {
                    foreach ($posts as $iterate) {
                        if (!empty($iterate)) { 
                            $query -> where($strings[0], 'LIKE', '%'.$iterate.'%');
                        }
                    }
                }

                else {
                    foreach ($posts as $iterate) {
                        $query -> where (function ($subquery) use ($strings, $iterate) {
                            for ($i = 0; $i < count ($strings); $i++) {
                                //if ($i == 0) {
                                //    $subquery -> where ($strings[$i], 'LIKE', '%'.$iterate.'%'); 
                                //}
                                //else {
                                    $subquery -> orWhere ($strings[$i], 'LIKE', '%'.$iterate.'%'); 
                                //}
                            }      
                        });
                    }
                }   
            }
            else {
                $query -> where($i['id'], '=', $input['string']);
            }
        }
            

        $dbi = $query 
            -> limit($i['limit'] - (!empty($input['id']) ? 1 : 0))
            -> get();

        $items = json_decode($dbi, TRUE);


        return isset($post_id) ? array_merge($post_id, $items) : $items;
    }

    public function input ($user, $input) {
        die (abort(404, 'Not supported!'));
    }

    public function delete ($user, $input) {
        die (abort(404, 'Not supported!'));
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {

        // Return validated input
        if (empty($error)) {
            return ['input' => $input];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
