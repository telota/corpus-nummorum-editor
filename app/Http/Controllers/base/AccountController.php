<?php

namespace App\Http\Controllers\base;

use App\Http\Controllers\Controller;
use Auth;

class AccountController extends Controller {

    public function status () {

        $user = Auth::user();

        // Not authenticated: show login form
        if (empty($user)) {
            die(abort(403));
        }

        // Authenticated: show account data
        else {
            $level = $user->level;

            if ($level < 2) {
                $role = 'Registered';
            }
            else if ($level < 10) {
                $role = 'Contributor';
            }
            else if ($level < 31) {
                $role = 'CN Team Member';
            }
            else {
                $role = 'CN Team Administrator';
            }

            return view('base.account', [
                'level' => $user->level,
                'user' => [
                    'ID' => $user->id,
                    'username' => $user->name,
                    'email' => $user->email,
                    'password' => '******',
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'role' => $role,
                    'registered' => substr($user->created_at, 0, 10)
                ]
            ]);
        }
    }
}
