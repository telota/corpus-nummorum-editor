<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/account-status'; //RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // Bot Honeypot as additional protection
        if (!empty($data['connection'])) { die(abort(403)); }

        return Validator::make($data, [
            //'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:app_editor_users'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'firstname' => ['required','string','max:255'],
            'lastname'  => ['required','string','max:255'],
            'country'   => ['required','string','max:255'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        /*return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);*/

        $user = User::create([
            //'name'      => $data['name'],
            'name'      => substr($data['firstname'], 0, 1).substr($data['lastname'], 0, 44),
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'firstname' => $data['firstname'],
            'lastname'  => $data['lastname'],
            'country'   => $data['country'],
        ]);

        // Get Email-Adresses of Admins
        /*$admins = json_decode(DB::table(config('dbi.tablenames.users')) -> select('email') -> where('access_level', '>', 30) -> get(), true);
        if (!empty($admins[0])) {
            $admins = array_map(function ($item) { return $item['email']; }, $admins);

            // Send Mail
            Mail::to($admins) -> send(
                new standardMail($data, [
                    'subject' => 'Editor: Neue Registrierung',
                    'view' => 'user_new'
                ])
            );
        }*/

        return $user;
    }
}
