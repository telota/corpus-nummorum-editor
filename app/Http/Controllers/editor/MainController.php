<?php

namespace App\Http\Controllers\editor;

use App\Http\Controllers\Controller;
use Auth;
use DB;
use Request;

class MainController extends Controller {

    public function initiate () {

        $id_user = Auth::user() ->id;
        $default_language = substr(Request::server('HTTP_ACCEPT_LANGUAGE'), 0, 2) === 'de' ? 'de' : 'en';

        // Presets
        $presets = DB::table('cn_app.app_editor_users AS u')
            -> leftJoin('cn_app.app_editor_users_presets AS p AS p', 'p.id', '=', 'u.id')
            -> select([
                    DB::raw('IFNULL(p.language, \''.$default_language.'\') AS language'),
                    DB::raw('IFNULL(p.color_theme, 1) AS color_theme'),
                    DB::raw('IFNULL(p.search_layout, 0) AS search_layout')
                ])
            -> where('u.id', $id_user)
            -> get ();
        $presets = json_decode($presets, true)[0];

        // User
        $user = DB::table('cn_app.app_editor_users AS u')
            -> leftJoin('cn_app.app_editor_users_ranks AS r', 'r.id', '=', 'u.access_level')
            -> select([
                    'u.id AS id',
                    'u.name AS name',
                    'u.firstname AS firstname',
                    'u.lastname AS lastname',
                    'u.email AS email',
                    DB::raw('CAST( u.created_at AS DATETIME) AS created_at'),
                    DB::raw('IF( u.last_login is null, null, CAST( u.last_login AS DATETIME)) AS last_login'),
                    DB::raw('IF( u.email_verified_at is null, 0, 1) AS verified'),
                    DB::raw('IF( u.terms_agreed = 1, 1, 0 ) AS agreed'),
                    'u.access_level AS level',
                    'r.role_en AS role'
                ])
            -> where('u.id', $id_user)
            -> get ();
        $user = json_decode($user, true)[0];

        $user['fullname'] = empty ($user['firstname']) || empty ($user['lastname']) ? $user['name'] : ($user['firstname'].' '.$user['lastname']);

        // Log
        $log = [];

        return view('editor.index', ['data' => [
            'user'      => $user,
            'presets'   => $presets,
            'log'       => $log
        ]]);
    }
}
