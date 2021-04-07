<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;
use Illuminate\Support\Facades\Artisan;


class bibliography implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        $select = [
            'zotero_id AS id',
            DB::raw('CONCAT("'.config('dbi.url.zotero').'", zotero_id) as link'),
            'author',
            'title',
            DB::raw('IFNULL(shorttitle, title) AS title_short'),
            'year_published AS year',
            'series',
            'volume',
            'place',
            'publisher',
            'edition'
        ];

        if ($user['level'] > 9) { 
            $select[] = DB::raw('IF(is_trash = 1, "deleted", "public") as status');
            $select[] = DB::raw('CAST(created_at AS DATE) as created_at');
            $select[] = DB::raw('CAST(updated_at AS DATE) as updated_at');
            $select[] = DB::raw('CAST(fetched_at AS DATE) as fetched_at');
        }
        
        $query = DB::table(config('dbi.tablenames.bibliography')) -> select($select);

        // Set condition if ID is given
        if (!empty($id)) { $query -> where('zotero_id', $id); }
        // No Trash if level below 10
        if ($user['level'] < 10) { $query -> where('is_trash', 0); }

        $query -> orderByRaw(
            'is_trash = 1, '.
            'author IS NULL, '.
            'author ASC, '.
            'year_published ASC'
        );

        $dbi = $query -> get();
        $items = json_decode($dbi, TRUE);

        return $items;
    }

    public function input ($user, $input) {
        Artisan::call('zotero:import');
        die();
    }

    public function delete ($user, $input) {
        return ['error' => [config('dbi.responses.general.no_manual_input')]];
    }

    public function connect ($user, $input) {
        return ['error' => [config('dbi.responses.general.no_manual_input')]];
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {}
}
