<?php

namespace App\Http\Controllers\dbi\entities\types;

use DB;

class update_inheritance {

    public function updateInheritance ($id = null, $echo = false) {

        //

        $tableToInheritedKeys = [
            'date_start' => 'date_inherited',
            'date_end' => 'date_inherited',
            'date_string' => 'date_inherited',
            'id_period' => 'period_inherited',
            'id_legend_o' => 'legend_o_inherited',
            'id_legend_r' => 'legend_r_inherited',
            'id_design_o' => 'design_o_inherited',
            'id_design_r' => 'design_r_inherited',
            'id_authority' => 'authority_inherited',
            'id_authority_person' => 'authority_person_inherited',
            'id_authority_group' => 'authority_group_inherited',
            'id_mint' => 'mint_inherited',
            'id_material' => 'material_inherited',
            'id_standard' => 'standard_inherited',
            'id_denomination' => 'denomination_inherited',
        ];


        // get coins to update
        $query = DB::table(config('dbi.tablenames.coins_inherit').' AS i')
        ->leftJoin(config('dbi.tablenames.coins').' AS c', 'c.id', '=', 'i.id')
        ->select('i.id')
        ->where('c.publication_state', '!=', 3);
        if (!empty($id)) $query->where('i.id_type', $id);

        $coinIDs = $query->get();
        $coinIDs = json_decode($coinIDs, true);
        $coinIDs = array_map(function ($record) { return $record['id']; }, $coinIDs);

        if ($echo === true) echo implode(', ', $coinIDs);
    }
}
