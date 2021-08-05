<?php

namespace App\Http\Controllers\storage;

use App\Http\Controllers\Controller;
use App\Http\Controllers\storage\StorageActions;
use App\Http\Controllers\storage\DirectoriesHandler;
use Storage;
use Response;
use Request;
use Auth;


class StorageController extends Controller {

    public function authCheck ($level = 1) {
        if (Auth::user()->level < $level) die (abort(403));
    }

    public function directoryCheck ($directory = null) {
        if (!empty($directory) && Storage::missing($directory)) die (abort(404));
    }

    public function index ($directory = null) {
        $this->authCheck(10);

        $list = [];
        $handler = new DirectoriesHandler();

        // No Directory given, provide Directory Index
        if (empty($directory)) {

            $list = []; //$handler->indexCoinImages();

            foreach (config('dbi.files.root_directories') AS $root => $name) {
                $list[] = $root;
                foreach (Storage::allDirectories($root) as $subdir) {
                    $list[] = $subdir;
                }
            }

            return Response::json(['directories' => $list]);
        }

        // Directory given, provide File Index
        else {

            $directory = trim($directory, '/');
            if (substr($directory, 0, 11) === 'coin-images') $directory = $handler->redirectCoinImages($directory);
            $this->directoryCheck($directory);

            $list = Storage::files($directory);
            return Response::json(['files' => $list]);
        }

    }

    // Get information about specific file
    public function details ($root, $path) {

        // Check Auth
        $this->authCheck(10);

        $path = $root.'/'.$path;
        $this->directoryCheck($path);

        $handler = new StorageActions();
        $data = $handler->getFileDetails($root, $path);

        return Response::json($data);
    }

    // Upload file to storage
    public function upload () {

        // Check Auth
        $this->authCheck(11);

        $request = Request::all();

        // ensure a file was uploaded
        if (empty($request['file']) && empty($request['file0'])) return $this->returnError(404, 'No files given!');

        // Create Actions Handler
        $handler = new StorageActions();

        // Rebuild Keys if single File was given
        if (!isset($request['file0'])) {
            $request['file0'] = $request['file'];
            $request['meta0'] = $request['meta'] ?? null;
            unset($request['file']);
            unset($request['meta']);
        }

        $directory = (empty($request['directory']) ? 'uploads' : rtrim($request['directory'], '/')).'/';

        $response = [];

        $fi = 0;
        while (!empty($request['file'.$fi])) {

            $file           = $request['file'.$fi];
            $name_original  = $file->getClientOriginalName();
            $sanitized      = $handler->sanitizeName($name_original);
            $name           = $sanitized['name'];
            $file_name      = $sanitized['file'];
            $extension      = $sanitized['extension'];

            // Store File (check existence if required)
            if (empty($request['override'])) {
                $existing = Storage::files($directory);
                $ei = 1;
                while (in_array($directory.$file_name, $existing)) {
                    $file_name = $name.'_copy'.$ei.$extension;
                    ++$ei;
                }
            }

            //$response = ['error' => json_decode($request['meta'.$fi], true)];

            $success = Storage::putFileAs($directory, $file, $file_name);

            // Store Meta
            /*if (!empty($success) && !empty($request['meta'.$fi])) {
                $meta_data = json_decode($request['meta'.$fi], true);
                $meta_name = $handler->createMetaForUpload($directory.$file_name, $file, $meta_data);
            }*/

            $response[] = [
                'success'       => empty($success) /*|| empty($meta_name)*/ ? false : true,
                'path'          => $directory.$file_name,
                'directory'     => $directory,
                'name'          => $file_name,
                'originalName'  => $name_original,
                //'meta'          => empty($meta_name) ? null : $meta_name
            ];

            ++$fi;
        }

        return Response::json($response, 201);
    }

    public function action ($action) {
        $this->authCheck(11);

        $POST = Request::post();
        $handler = new StorageActions();

        if ($action === 'create') {
            if (empty($POST['name'])) die(abort(404));
            $response = $handler->createDirectory($POST['name'], $POST['directory'] ?? null);
            return Response::json($response, empty($response['success']) ? 404 : 201);
        }

        else if ($action === 'publish') {
            if (empty($POST['path'])) die(abort(404));
            $response = $handler->publishFile($POST['path'], empty($POST['value']) ? false : true);
            return Response::json(empty($response) ? $response : ['success' => $response]);
        }

        else if ($action === 'delete') {
            if (empty($POST['path'])) die(abort(404));
            $response = $handler->deleteItem($POST['path']);
            return Response::json(empty($response) ? $response : ['success' => $response]);
        }

        else if ($action === 'append-meta') {
            if (empty($POST['path'])) die(abort(404));
            $response = $handler->appendMeta($POST['path'], $POST['data']);
            return Response::json(empty($response) ? $response : [
                'success' => $response,
                'meta' => json_decode(Storage::get('storage/'.$POST['path'].'.META'), true)
            ]);
        }
    }

    // Delete requested file
    public function delete () {

        // Check Auth
        if (Auth::user()->level < 12) { die(abort(403)); }

        $file = Request::post()['file'];

        $delete = substr($file, 8) === 'storage/' ? substr($file, 8) : $file;

        // Ensure file exists
        if (Storage::disk('data')->exists($delete)) {
            if (Storage::disk('data')->delete($delete)) {
                $feedback['success'] = config('cn_admin_feedback.ok_updated').' "storage/'.$delete.'" deleted.';
                $feedback['url']     = 'storage/'.$delete;
            }
            else {
                $feedback['error'] = config('cn_admin_feedback.server_issue').' "storage/'.$delete.'" could not be deleted.';
            }
        }
        else {
            $feedback['error'] = config('cn_admin_feedback.not_found').' "storage/'.$delete.'" could not be found. Someone else might deleted it, already. Please reload and try again.';
        }

        return Response::json($feedback);
    }

    public function returnError ($code, $string) {
        return response($string, $code)->header('message', $string);
    }
}
