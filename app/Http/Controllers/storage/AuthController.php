<?php

namespace App\Http\Controllers\storage;

use App\Http\Controllers\Controller;
use Auth;
use Storage;

class AuthController extends Controller {

    public function handleAuthRequest () {

        $headers = getallheaders();

        // NO URI received
        if (empty($headers['X-Original-Uri'])) return $this->denyRequest(500, 'No X-Original-Uri Header received');
        $target = $headers['X-Original-Uri'];

        // Digilib Link
        if (substr($target, 0, 8) === '/digilib') {
            $path = explode('fn=', $target);
            if (empty($path[1])) return $this->denyRequest(404, 'No File requested');
            $path = explode('&', $path[1]);
            $path = ltrim($path[0], '/');
        }
        // Storage Link
        else {
            $path = explode('storage/', $target);
            $path = end($path);
        }

        // Check if File is public
        $meta = 'storage/'.$path.'.META';
        if (Storage::exists($meta)) {
            $meta = json_decode(Storage::get($meta), true);
            if (!empty($meta['public'])) return true;
        }

        // Get
        $rootDirectory = explode('/', $path);
        $rootDirectory = $rootDirectory[0];

        // Handle Session
        $user = Auth::user();
        if (empty($user)) return $this->denyRequest(403, 'not logged in');

        //return $this->denyRequest(403, 'No Permission');

        /*$path = explode('storage/', $headers['X-Original-Uri']);
        $path = end($path);

        $project = explode('/', $path);
        $project = $project[0];

        //if (!str_contains($user->permissions, $project)) return $this->denyRequest(403, 'No permission for directory "'.$project.'"!');

        $file = explode('.', $path);
        if (!empty($file[1])) {
            $file = $file[0];
            // Get Meta Data Json
            $json = @file_get_contents(env('APP_STORAGE').'/'.$file.'.json');
            if (!$json) return $this->denyRequest(404, 'No Json file found for "'.$file.'.json"!');

            // Check if file is public
            $json = json_decode($json, true);
            $private = empty($json['common']['public']) ? true : false;
            if ($private) return $this->denyRequest(403, 'Not permitted to receive "'.$path.'"!');
        }*/

        return true;
    }

    public function denyRequest ($code, $string) {
        // Response must be provided as 401to pass a message in WWW-Authenticate-Header to client
        // http://nginx.org/en/docs/http/ngx_http_auth_request_module.html

        $string = $code.':'.$string;
        return response($string, 401)->header('WWW-Authenticate', $string);
    }

    public function test ($path = 'root') {
        return 'You were redirected successfully to '.$path;
    }
}
