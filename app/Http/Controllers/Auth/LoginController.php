<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    //protected $redirectTo = RouteServiceProvider::HOME; // set in app\Providers\RouteServiceProvider.php

    protected function redirectTo () {
        $url = RouteServiceProvider::HOME;
        $post = Request::post();
        $anchor = (empty($post['redirect-to']) ? '' : ('#'.$post['redirect-to']));

        return $url.$anchor;
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Bot Honeypot as additional protection
        $post = Request::post();
        if(!empty($post['connection'])) { die(abort(403)); }

        $this->middleware('guest')->except('logout');
    }
}
