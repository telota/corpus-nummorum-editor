<?php

namespace App\Http\Controllers\dbi\entities\types;

use DB;
use App\Http\Controllers\dbi\entities\types\input_definitions;
use App\Http\Controllers\dbi\handler\index_handler;


class input {

    public function handle ($user, $input) {
        $this->updatePublicationState($input);

        // Get Base Table config
        $config = new input_definitions;
        $config = $config -> instructions();
        $base   = $config['base'];
        $ID     = empty($input['id']) ? null : $input['id'];
        /*
        $base = config('dbi.types_input.base');*/

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
            $this -> helpers($config, 'monograms',  $ID, array_merge($input['o_monograms'], $input['r_monograms']));
            $this -> helpers($config, 'symbols',    $ID, array_merge($input['o_symbols'], $input['r_symbols']));
            $this -> helpers($config, 'references', $ID, array_merge($input['citations'], $input['literature']));
            $this -> helpers($config, 'links',      $ID, $input['links']);
            $this -> helpers($config, 'groups',     $ID, $input['groups']);
            $this -> helpers($config, 'persons',    $ID, $input['persons']);

            $this -> helpers($config, 'variations', $ID, $input['variations']);
            $this -> helpers($config, 'findspots',  $ID, $input['findspots']);
            $this -> helpers($config, 'hoards',     $ID, $input['hoards']);
        }

        // Update Index
        if (!empty($input['id'])) {
            $handler = new index_handler();
            $handler->updateIndex('types', $ID);
        }

        return $ID;
    }


    function helpers ($config, $entity, $ID, $input) {
        if (!empty($entity) && !empty ($ID)) {
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

                    foreach ($src['cols'] as $db => $post) {
                        $row[$db] = $item[$post];
                    }

                    /*// JK: References need special Treatment
                    if ($entity == 'references') {
                        $row ['ZoteroID'] = $this -> zotero.$row ['ZoteroID'];
                    }*/

                    $new [] = $row;
                }
            }

            // DB Transaction
            DB::transaction (
                function() use ($src, $ID, $new)
                {
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

    function updatePublicationState ($input) {// Update Publication State
        if (isset($input['updatePublicationState'])) {
            $value = $input['updatePublicationState'];
            if (in_array($value, [0, 2]) && $input['id']) {
                DB::table(config('dbi.tablenames.types'))->where('id', $input['id'])->update(['publication_state' => $value]);
                die (json_encode(['success' => true]));
            }
        }
    }
}
