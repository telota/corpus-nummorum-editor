<?php

namespace App\Http\Controllers\storage;

use App\Http\Controllers\storage\ImageHandler;
use Storage;
use DB;
use Auth;
use DateTime;
//use App\Models\searchFiles;

class StorageActions {

    public function directoryCheck ($directory = null) {
        if (empty($directory)) return true;
        return Storage::exists($directory);
    }

    public function createDirectory ($name, $parent = null) {
        $name = trim($name, '/');

        if ($this->directoryCheck($parent)) {
            $existing = false;
            $name = $this->sanitizeName($name);
            $path = (empty($parent) ? '' : (trim($parent, '/'))).'/'.$name['name'];

            if (Storage::missing($path)) Storage::makeDirectory($path);
            else $existing = true;

            return [
                'success' => true,
                'existing' => $existing,
                'path' => '/'.$path,
                'name' => $name['name']
            ];
        }
        else return ['success' => false];
    }

    public function deleteItem ($path) {

        $split = explode('/', $path);
        $last = array_pop($split);

        if (strpos($last, '.')) {
            if (Storage::exists($path)) Storage::delete($path);
        }
        else Storage::deleteDirectory($path);

        return $path;
    }

    public function getFileDetails ($root, $path) {
        $file = explode('/', $path);
        $file = array_pop($file);
        $dbi = [];

        if ($root === 'Monograms') {
            $dbi = DB::table(config('dbi.tablenames.monograms'))
                ->select([
                    DB::Raw('"materials" AS entity'),
                    'id AS id',
                    DB::Raw('CONCAT("cn monogram ", id) AS name'),
                    DB::Raw('null AS author'),
                    DB::Raw('null AS copyright')
                ])
                ->where('image', $file)
                ->get();
            $dbi = json_decode($dbi, true);
        }
        else if ($root === 'Symbols') {
            $dbi = DB::table(config('dbi.tablenames.symbols'))
                ->select([
                    DB::Raw('"symbols" AS entity'),
                    'id AS id',
                    DB::Raw('CONCAT("cn symbol ", id) AS name'),
                    DB::Raw('null AS author'),
                    DB::Raw('null AS copyright')
                ])
                ->where('image', $file)
                ->get();
            $dbi = json_decode($dbi, true);
        }
        else {
            $dbi = DB::table(config('dbi.tablenames.images'))
                ->select([
                    DB::Raw('"coins" AS entity'),
                    'CoinID AS id',
                    DB::Raw('CONCAT("cn coin ", CoinID) AS name'),
                    'ObjectType AS kind',
                    DB::Raw('IF(ObverseImageFilename = "'.$path.'", ObversePhotographer, ReversePhotographer) AS author'),
                    DB::Raw('null AS copyright'),
                    DB::Raw('created_at AS created'),
                ])
                ->where(function ($subquery) use ($path) { $subquery->orWhere('ObverseImageFilename', $path)->orWhere('ReverseImageFilename', $path); })
                ->get();
            $dbi = json_decode($dbi, true);
        }

        return [
            'path' => $path,
            'file' => $file,
            'type' => Storage::mimeType($path),
            'size' => Storage::size($path),
            'relations' => $dbi
        ];
    }

    public function createThumbnails ($src, $transformation = null) {
        //$handler = new ImageHandler();
        //return $handler->createThumbnails($src, $transformation);
        return ['success' => true];
    }

    // --------------------------------------------------------------------

    public function sanitizeName ($name) {
        $name       = trim($name);
        $name       = mb_convert_encoding($name, 'UTF-8');                              // convert to UTF-8
        $name       = mb_strtolower($name, 'UTF-8');                                    // set to lower case
        $name_split = explode('.', $name);                                              // split name by dots to get extension

        if (!empty($name_split[1])) {
            $extension  = '.'.trim(array_pop($name_split));                             // extract extension
            $name       = implode('.', $name_split);                                    // implode original name
        }
        $name       = preg_replace("/[[:blank:]]+/", '_', trim($name));                 // replace (multiple) blank Spaces
        $name       = str_replace('ä', 'ae', $name);                                    // replace "ä"
        $name       = str_replace('ö', 'oe', $name);                                    // replace "ö"
        $name       = str_replace('ü', 'ue', $name);                                    // replace "ü"
        $name       = preg_replace('/[^a-z0-9_]+/', '-', $name);                        // replace any remaining problematic char
        $name       = preg_replace('/\-+/', '-', $name);                                // replace multiple dashes
        if (empty($name)) $name = substr(date('U'), -8);                                // Replace name with shortened u if name is empty

        return [
            'name' => $name,
            'extension' => $extension ?? null,
            'file' => $name.($extension ?? '')
        ];
    }
}
