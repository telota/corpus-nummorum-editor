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
            $git = trim(shell_exec('git log -1 --pretty=\'format:%h||%cN||%cd||%d\' --date=format:\'%Y-%m-%d\''));
            if (!empty($git)) {
                $git = explode('||', $git);
                if (!empty($git[0])) array_unshift($git, config('dbi.url.github_repo').'/commit/'.$git[0]);
                if (!empty($git[4])) {
                    preg_match('/HEAD -> (.*?), /', $git[4], $match);
                    $git[4] = empty($match[1]) ? $git[1] : ($git[1].' ('.$match[1].')');
                }
            }

            // System
            $system = [
                'git'           => $git,
                'phpVersion'    => phpversion(),
                'maxUpload'     => (int)(ini_get('upload_max_filesize')),
                'maxPost'       => (int)(ini_get('post_max_size')),
                'memoryLimit'   => (int)(ini_get('memory_limit')),
                'maxExecTime'   => (int)(ini_get('max_execution_time'))
            ];

            // Log
            $log = [];

            return view('editor.app', [
                'version' => '?v='.(empty(env('DEV')) ? (empty($git[1]) ? 00 : $git[1]) : date('U')),
                'data' => [
                    'appName'   => 'CN Editor',
                    'baseURL'   => rtrim(env('APP_URL'), '/'),
                    'sparql'    => config('dbi.url.sparql'),
                    'language'  => $auth->language ?? substr(Request::server('HTTP_ACCEPT_LANGUAGE'), 0, 2),
                    'user'      => $user,
                    'settings'  => empty($auth->settings) ? [] : json_decode($auth->settings, true),
                    'log'       => $log,
                    'system'    => $system
                ]
            ]);
        }
    }
}
