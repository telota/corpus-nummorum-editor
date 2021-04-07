<?php

namespace App\Http\Controllers\dbi\entities\lists;

use App\Http\Controllers\dbi\entities\listsInterface;
use DB;
use Auth;


class users implements listsInterface  {

    public function select ($user, $input, $language) {
        // Hide non public user for average user
        if(empty(Auth::user()) || Auth::user()->access_level < config('dbi.permissions.users_list.read')) {
            die(abort(403, 'Insufficient Permissions.'));
        }

        $query = DB::table(config('dbi.tablenames.users'))
            -> select([
                // ID
                'id AS id',
                // String
                DB::raw('IF(firstname = "Admintooluser",
                    CONCAT_WS(" ", "(old)", lastname),
                    CONCAT_WS("", lastname, IF(firstname IS NOT NULL, CONCAT(", ", SUBSTRING(firstname, 1, 1), "."), null)
                )) AS string'),
                // Addition
                DB::raw('CONCAT_WS(" ", firstname, lastname, CONCAT("(", name, ")")) AS addition'),
                // Search
                DB::raw('LOWER(CONCAT_WS("|",
                    id,
                    email,
                    name,
                    lastname,
                    firstname
                )) AS search')
            ]);
        
        // Where
        if(!empty($input['search'])) {
            foreach($input['search'] AS $search) {
            $query -> where(function ($subquery) use ($search) {
                    $subquery 
                        -> orWhere('id', $search)
                        -> orWhere('name', 'LIKE', '%'.$search.'%')
                        -> orWhere('lastname', 'LIKE', '%'.$search.'%')
                        -> orWhere('firstname', 'LIKE', '%'.$search.'%');
                });
            }
        }

        // Get only Users who can insert data or who were admintool users once
        $query -> where(function ($subquery) {
            $subquery -> orWhere('access_level', '>', 1) -> orWhere([['firstname', 'Admintooluser'], ['name', 'LIKE', 'old_%']]);
        });

        // ORDER BY
        $query -> orderByRaw(
            (empty($input['id']) ? '' : 'FIELD(id, '.implode(',', array_reverse($input['id'])).') DESC, ').
            'firstname = "Admintooluser", '.
            'lastname IS NULL, '.
            'lastname ASC, '.
            'name IS NULL, '.
            'name ASC'
        );
        //-> limit(50);

        $dbi = $query->get();
        $dbi = json_decode($dbi, TRUE);

        foreach ($dbi as $i => $item) {
            if (strpos($item['search'], '|cn-support@bbaw.de|') !== false) {
                unset($dbi[$i]);
                $item['string'] = 'CN Editor';
                $item['addition'] = 'CN IT Server Agent';
                $item['search'] .= '|CN Editor|Server';
                $dbi = array_merge([$item], $dbi);
                break;
            }
        }

        return $dbi;
    }
}
