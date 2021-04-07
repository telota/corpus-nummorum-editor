<?php

namespace App\Http\Controllers\dbi\entities\coins;

use DB;
use App\Http\Controllers\dbi\entities\coins\input_definitions;


class input {

    public function handle ($user, $input) {
        // Get Base Table config
        $config = new input_definitions;
        $config = $config -> instructions();
        $base   = $config['base'];
        $ID     = empty($input['id']) ? null : $input['id']; 
        /*
        $base   = config('dbi.coins_input.base');*/ 

        // Empty Object given (new empty Item)
        if ($ID  === 'new') {
            $ID = DB::table($base['table']) -> insertGetID([
                $base['creator'] => $user['id'],
                $base['public'] => 0
            ]);
        }

        // Object is given as input
        else {
            // Iterate over Base Table Config to create Input Array
            foreach ($base['cols'] as $db => $insert) {
                $base['input'][$db] = $input[$insert];
            }
            
            // Write Input
            if (empty($ID)) {                
                $base['input'][$base['creator']] = $user['id'];
                $base['input'][$base['public']] = 0;

                $ID = DB::table($base['table']) -> insertGetID($base['input']);
            }

            else {
                // Set current User as Editor
                $base['input'][$base['editor']] = $user['id'];
                $base['input'][$base['public']] = empty($input['public']) ? 0 : $input['public'];
        
                DB::table($base['table'])        
                    -> where($base['id'], $ID) 
                    -> update($base['input']);
            }

            // Helper Entities
            $this -> helpers($config, 'monograms',      $ID, array_merge($input['o_monograms'], $input['r_monograms']));
            $this -> helpers($config, 'symbols',        $ID, array_merge($input['o_symbols'], $input['r_symbols']));
            $this -> helpers($config, 'controlmarks',   $ID, array_merge($input['o_controlmarks'], $input['r_controlmarks']));
            $this -> helpers($config, 'references',     $ID, array_merge($input['citations'], $input['literature']));
            $this -> helpers($config, 'links',          $ID, $input['links']);
            $this -> helpers($config, 'groups',         $ID, $input['groups']);
            $this -> helpers($config, 'persons',        $ID, $input['persons']);

            $this -> helper_images($config, $ID, $input['images']);

            if(!empty($input['inherited']['id_type'])) { 
                $this -> helper_inheritance($config, $ID, $input['inherited']); 
            }
        }

        return $ID;
    }    


    function helpers ($config, $entity, $ID, $input) {
        if (!empty($entity) && !empty($ID)) {
            $src = $config[$entity];
            $new = [];

            // Process input array
            foreach ($input as $item) {
                // Check whether required fields are empty
                $run = 0;

                foreach ($src['required'] as $required) {
                    if (empty($item[$required])) { ++$run; }
                }

                // If required fields are not empty add element to new array
                if ($run < 1) {
                    $row[$src['id_base']] = $ID;

                    foreach($src['cols'] as $db => $post) {
                        $row[$db] = $item[$post];
                    }

                    // JK: References need special Treatment
                    // if ($entity === 'references') {$row['ZoteroID'] = $this->zotero.$row['ZoteroID'];}

                    $new[] = $row;
                }
            }

            // DB Transaction
            DB::transaction (
                function() use ($src, $ID, $new) {
                    // Delete old Helper Table Links
                    DB::table($src['table']) 
                        -> where($src['id_base'], $ID)
                        -> delete();

                    // Write new Helper Table Links
                    if (!empty($new)) {
                        $new = array_unique($new, SORT_REGULAR); // Remove duplicate rows
                        DB::table($src['table']) -> insert($new);
                    }
                }
            );
        }
    }


    function helper_images ($config, $ID, $images) {
        $data = [];

        foreach($images as $i) {
            $data [] = [
                'id'                => empty($i['id']) ? null : $i['id'],
                'o_link'            => $i['obverse']['link'],
                'r_link'            => $i['reverse']['link'],
                'o_photographer'    => $i['obverse']['photographer'],
                'r_photographer'    => $i['reverse']['photographer'],
                'kind'              => $i['obverse']['kind'] === 'original' ? 'original' : 'plastercast',
                'path'              => ''
            ];
        }

        $this -> helpers($config, 'images', $ID, $data);        
    }


    function helper_inheritance ($config, $id, $i) {

        DB::table('cn_data.data_coins_to_types_inherit') -> updateOrInsert( 
            ['id' => $id], 
            [
                'id_type'                   => $i['id_type'],

                'mint_inherited'            => empty($i['mint']) ? 0 : 1,
                'issuer_inherited'          => empty($i['issuer']) ? 0 : 1,
                'authority_inherited'       => empty($i['authority']) ? 0 : 1,
                'authority_person_inherited'=> empty($i['authority_person']) ? 0 : 1,
                'authority_group_inherited' => empty($i['authority_group']) ? 0 : 1,

                'material_inherited'        => empty($i['material']) ? 0 : 1,
                'denomination_inherited'    => empty($i['denomination']) ? 0 : 1,
                'standard_inherited'        => empty($i['standard']) ? 0 : 1,
                'date_inherited'            => empty($i['date']) ? 0 : 1,
                'period_inherited'          => empty($i['period']) ? 0 : 1,

                'legend_o_inherited'        => empty($i['o_legend']) ? 0 : 1,
                'symbol_o_inherited'        => empty($i['o_symbols']) ? 0 : 1,
                'design_o_inherited'        => empty($i['o_design']) ? 0 : 1,
                'monogram_o_inherited'      => empty($i['o_monograms']) ? 0 : 1,
                
                'legend_r_inherited'        => empty($i['r_legend']) ? 0 : 1,
                'symbol_r_inherited'        => empty($i['r_symbols']) ? 0 : 1,
                'design_r_inherited'        => empty($i['r_design']) ? 0 : 1,
                'monogram_r_inherited'      => empty($i['r_monograms']) ? 0 : 1,

                'person_inherit'            => empty($i['persons']) ? 0 : 1,

                'comment_private_inherited' => 0, //empty($i['comment_private']) ? 0 : 1,
                'comment_public_inherited'  => 0 //empty($i['comment_public']) ? 0 : 1,
            ]
        );

        // Add inheriting type to coins_to_types if not existing
        DB::table('cn_data.data_coins_to_types') -> updateOrInsert( 
            ['id_coin' => $id, 'id_type' => $i['id_type']], 
            ['id_coin' => $id, 'id_type' => $i['id_type']]
        );
    }
}