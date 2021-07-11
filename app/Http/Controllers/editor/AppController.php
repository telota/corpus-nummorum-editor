<?php

namespace App\Http\Controllers\editor;

use App\Http\Controllers\Controller;
use Auth;
use DB;
use Request;

class AppController extends Controller {

    public function initiate () {
        $auth = Auth::user();

        // Not authenticated: show login form
        if (empty($auth)) {
            return view('auth.login', ['showRedirect' => true]);
        }

        // Level is insufficent: show status
        else if ($auth->level < 2) {
            return redirect('/account');
        }

        // Level is sufficient: get Data and return SPA
        else {
            // User
            $user = [
                'id'        => $auth->id,
                'email'     => $auth->email,
                'name'      => $auth->name,
                'role'      => 'TEST',
                'level'     => $auth->level,
                'lastname'  => $auth->lastname,
                'firstname' => $auth->firstname,
                'fullname'  => empty($auth->firstname) || empty($auth->lastname) ? $auth->name : ($auth->firstname.' '.$auth->lastname),
                'verified'  => empty($auth->email_verified_at) ? 0 : 1,
                'agreed'    => empty($auth->terms_agreed) ? 0 : 1
            ];

            // Git Status
            $git = trim(shell_exec('git log -1 --pretty=\'format:%h||%cN||%cd\' --date=format:\'%Y-%m-%d\''));
            if (!empty($git)) {
                $git = explode('||', $git);
                if (!empty($git[0])) array_unshift($git, config('dbi.url.github_repo').'/commit/'.$git[0]);
            }

            // System
            $system = [
                'git' => $git,
                'maxUpload' => (int)(ini_get('upload_max_filesize')),
                'maxPost' => (int)(ini_get('post_max_size')),
                'memoryLimit' => (int)(ini_get('memory_limit'))
            ];

            // Log
            $log = [];

            return view('editor.app', ['data' => [
                'appName'   => 'CN Editor',
                'baseURL'   => rtrim(env('APP_URL'), '/'),
                'sparql'    => config('dbi.url.sparql'),
                'language'  => $auth->language ?? (substr(Request::server('HTTP_ACCEPT_LANGUAGE'), 0, 2) === 'de' ? 'de' : 'en'),
                'user'      => $user,
                'settings'  => $auth->settings ?? [],
                'log'       => $log,
                'system'    => $system
            ]]);
        }
    }

    public function initiate2 () {

        $user = Auth::user();

        // Not authenticated: show login form
        if (empty($user)) {
            return view('auth.login', ['showRedirect' => true]);
        }

        // Level is insufficent: show status
        else if ($user->level < 2) {
            return redirect('/account');
        }

        // Level is sufficient: get Data and return editor app
        else {
            $id_user = $user->id;
            $default_language = substr(Request::server('HTTP_ACCEPT_LANGUAGE'), 0, 2) === 'de' ? 'de' : 'en';

            // Presets
            /*$presets = DB::table('cn_app.app_editor_users AS u')
                -> leftJoin('cn_app.app_editor_users_presets AS p AS p', 'p.id', '=', 'u.id')
                -> select([
                        DB::raw('IFNULL(p.language, \''.$default_language.'\') AS language'),
                        DB::raw('IFNULL(p.color_theme, 1) AS color_theme'),
                        DB::raw('IFNULL(p.search_layout, 0) AS search_layout')
                    ])
                -> where('u.id', $id_user)
                -> get ();
            $presets = json_decode($presets, true)[0];*/

            // User
            $user = DB::table('cn_app.app_editor_users AS u')
                -> leftJoin('cn_app.app_editor_users_ranks AS r', 'r.id', '=', 'u.level')
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
                        'u.level AS level',
                        'r.role_en AS role'
                    ])
                -> where('u.id', $id_user)
                -> get ();
            $user = json_decode($user, true)[0];

            $user['fullname'] = empty ($user['firstname']) || empty ($user['lastname']) ? $user['name'] : ($user['firstname'].' '.$user['lastname']);

            // Git Status
            $git = trim(shell_exec('git log -1 --pretty=\'format:%h||%cN||%cd\' --date=format:\'%Y-%m-%d\''));
            if (!empty($git)) {
                $git = explode('||', $git);
                if (!empty($git[0])) array_unshift($git, 'https://github.com/telota/corpus-nummorum-editor/commit/'.$git[0]);
            }

            $system = [
                'git' => $git,
                'maxUpload' => (int)(ini_get('upload_max_filesize')),
                'maxPost' => (int)(ini_get('post_max_size')),
                'memoryLimit' => (int)(ini_get('memory_limit'))
            ];

            // Log
            $log = [];

            return view('editor.app', ['data' => [
                'user'      => $user,
                'presets'   => $presets,
                'baseURL'   => rtrim(env('APP_URL'), '/'),
                'sparql'    => config('dbi.url.sparql'),
                'system'    => $system
            ]]);


        }
    }
}
