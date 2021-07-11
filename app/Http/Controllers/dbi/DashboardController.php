<?php

namespace App\Http\Controllers\dbi;

use App\Http\Controllers\Controller;
use Response;
use Request;
use DB;
use Auth;
use App\Models\User;


class DashboardController extends Controller {

    public function data () {

        $date_30    = date('Y-m-d H:i:s', strtotime('-30 days') );
        $id_user    = Auth::user()->id;

        $publication_state = [
            'all' => '!= 3',
            'pub' => '= 1'
        ];

        // Write User Statistics query
        foreach (['types', 'coins'] AS $entity) {
            foreach ($publication_state AS $key => $val) {
                $select_user[] = '\''.$entity.'_'.$key.'\',  (
                    SELECT COUNT(id)
                    FROM cn_data.data_'.$entity.'
                    WHERE publication_state '.$val.' && id_creator = '.$id_user.'
                )';
            }
            $select_user[] = '\''.$entity.'_trend\',  (
                SELECT COUNT(id)
                FROM cn_data.data_'.$entity.'
                WHERE publication_state != 3 && id_creator = '.$id_user.' && created_at > \''.$date_30.'\'
            )';
        }

        // Get statistics
        $statistics = DB::table ('cn_app.app_daily_statistics')
            -> select([
                DB::raw('(SELECT JSON_OBJECT(\'user\', JSON_OBJECT( '.implode(",\n", $select_user).' ))) AS user'),
                'data AS data',
                'created_at AS date'
            ])
            -> limit(1)
            -> orderBy('created_at', 'desc')
            -> get();

        // Decode, sort and merge statistics Array
        $statistics = json_decode($statistics, true)[0];
        $statistics1 = json_decode($statistics['user'], true);
        $statistics2 = json_decode($statistics['data'], true);
        ksort($statistics2['regions']);

        $dbi['statistics'] = array_merge($statistics1, $statistics2);
        $dbi['statistics_date'] = $statistics['date'];


        // Get latest coins, types edited by User
        foreach (['coins', 'types'] AS $resource) {
            $query = DB::table ('cn_data.data_'.$resource.' AS b')
                -> leftJoin ('cn_data.data_mints AS m', 'm.id', '=', 'b.id_mint')
                -> select ([
                    'b.id AS id',
                    'm.nomisma_id as mint',
                    'b.created_at AS created_at',
                    'b.updated_at AS updated_at'
                ])
                -> where([['b.id_editor', $id_user]])
                -> where([['b.publication_state', 0]])
                -> orWhere(function ($subquery) use ($id_user) {
                    $subquery -> where([['b.id_creator', $id_user], ['b.id_editor', null]]);
                })
                -> orderBy(DB::RAW('b.updated_at IS NULL'), 'DESC')
                -> orderBy('b.updated_at', 'DESC')
                -> orderBy('b.created_at', 'DESC')
                -> offset(0)
                -> limit(10)
                -> get();

            $dbi['activities']['latest_'.$resource] = json_decode($query, true);
        }

        return Response::json ($dbi);
    }


    public function updateSettings () {

        $user   = Auth::user();
        $input  = Request::post();
        $language = empty($input['language']) ? $user->language : $input['language'];
        unset($input['language']);

        User::where('id', $user->id)->update([
            'language' => $language,
            'settings' => $input
        ]);

        return Response::json(['success' => 'Settings updated.']);
    }
}
