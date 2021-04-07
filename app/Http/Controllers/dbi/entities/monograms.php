<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class monograms implements dbiInterface  { 

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {        
        $query = DB::table(config('dbi.tablenames.monograms')) -> select([
            'id         AS id',
            'monogram   AS name',
            DB::raw('REPLACE(lettercomb, " ", "") AS combination'),
            DB::raw('IF(image > \'\', CONCAT(\'Monograms/\', image), null) AS image')
        ]);
        
        // Set condition if ID is given
        if (!empty($id)) { $query -> where('id', $id); }

        $dbi = $query -> get();
        $items = json_decode($dbi, TRUE);

        for ($i = 0; $i <= count($items); $i++) {
            if (!empty($items[$i]['combination'])) {
                $letters = preg_split('~~u', trim($items[$i]['combination']), -1, PREG_SPLIT_NO_EMPTY);
                sort($letters);
                $items[$i]['combination'] = implode('', $letters);
            }
        }

        return $items;
    }

    public function input ($user, $input) {
        $validation = $this -> validateInput($input);

        if(empty($validation['error'][0])) {
            $input = $validation['input'];

            $ID = DB::transaction(function () use ($input) {
                $values = [
                    'monogram'      => $input['name'],
                    'lettercomb'    => $input['combination'],
                    'image'         => $input['image']
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    return DB::table(config('dbi.tablenames.monograms')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.monograms')) -> where('id', $input['id']) -> update($values);
                    return $input['id'];
                }
            });

            return $ID;
        }
        // Validation Error
        else { return ['error' => $validation['error']]; }
    }

    public function delete ($user, $input) {
        DB::transaction(function () use ($input) {
            DB::table(config('dbi.tablenames.monograms')) -> where('id', $input['id']) -> delete();
        });

        return true;
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {
        if (empty($input['name'])) {
            $error[] = config('dbi.responses.validation.general.name');
        }
        /*if (empty($input['image'])) {
            $error[] = config('dbi.responses.validation.general.image');
        }*/

        // Return validated input
        if (empty($error)) {
            if(!empty($input['image'])) {
                $img_explode    = explode('/', $input['image']);
                $input['image'] = end($img_explode);
            }

            return ['input' => $input];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
