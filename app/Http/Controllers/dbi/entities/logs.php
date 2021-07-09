<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;


class logs implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {

        $roots = [
            'cn_editor' => '/opt/projects/corpus-nummorum/src_editor',
            'cn_website' => '/opt/projects/corpus-nummorum/src_public-website'
        ];

        $items = [];

        foreach ($roots as $name => $root) {
            $year = date('Y');
            $log = null;
            $log = @file_get_contents($root.'/storage/logs/'.($name === 'cn_website' ? ('laravel-'.date('Y-m-d').'.logl') : 'laravel.log'));
            $items[$name] = [];

            if (!empty($log)) {
                $split = preg_split('/\r\n|\r|\n/', trim($log));
                $split = array_reverse($split);

                $i = 1;
                foreach ($split as $line) {
                    if (preg_match('/\[[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2}/', $line)) {
                        $items[$name][] = $line;
                        ++$i;
                    }
                    if ($i > 1000) break;
                }
                //$items[$name] = $processed;
            }
        }

        return $items;
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
    }
}
