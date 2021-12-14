<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class publishadmin implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        if (empty($id)) $this->validateInput('ID is empty!');

        $split = explode('-', $id);
        $id = $split[0];
        $published = isset($split[1]) ? intval($split[1]) : null;
        if (!in_array($published, [null, 0, 1, 2])) $this->validateInput('Invalid published value');

        $dbi = DB::table(config('dbi.tablenames.coins').' AS c')
            ->leftJoin(config('dbi.tablenames.coins_to_types').' AS ctt', 'ctt.id_coin', '=', 'c.id')
            ->leftJoin(config('dbi.tablenames.coins_inherit').' AS ci', 'ci.id', '=', 'c.id')
            ->select([
                'c.id',
                'c.date_string AS date',
                DB::Raw('IF(c.id_design_o IS NULL || c.id_design_r IS NULL, 0, 1) AS design'),
                'c.publication_state AS public',
                DB::Raw('IF(ci.id IS NULL, 1, 0) AS inherited')
            ])
            ->where(function ($subquery) use ($id) {
                $subquery->orWhere('ctt.id_type', $id)->orWhere('ci.id_type', $id);
            })
            ->whereIn('c.publication_state', $published === null ? [0, 1, 2] : [$published])
            ->get();

        $dbi = json_decode($dbi, true);

        $this->validateInput(array_values($dbi));
    }

    public function input ($user, $input) {
        if (!isset($input['state'])) $this->validateInput('No State given!');
        elseif (!in_array($input['state'], [0, 1])) $this->validateInput('Invalid state given!');

        if (!empty($input['ids'])) {
            $state = $input['state'];
            $ids = $input['ids'];

            DB::table(config('dbi.tablenames.coins'))->whereIntegerInRaw('id', $ids)->update(['publication_state' => $state]);
            return implode(',', $input['ids']);
        }
    }

    public function delete ($user, $input) {
        die (abort(404, 'Not supported!'));
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {
        die (json_encode(is_array($input) ? $input : ['error' => $input]));
    }
}
