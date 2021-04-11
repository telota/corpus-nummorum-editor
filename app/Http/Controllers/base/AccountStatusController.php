<?php

namespace App\Http\Controllers\base;

use App\Http\Controllers\Controller;
use Auth;

class AccountStatusController extends Controller {

    public function index () {

        $user = Auth::user();

        // Not authenticated: show login form
        if (empty($user)) {
            return view('auth.login');
        }

        // Authenticated: show account data
        else {
            return view('base.account_status', ['user' => [
                'id' => $user->id,
                'name' => $user->name,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'level' => $user->access_level,
                'role' => $user->role,
                'created' => substr($user->created_at, 0, 10)
            ]]);
        }
    }
}
