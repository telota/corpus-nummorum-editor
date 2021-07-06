<?php

namespace App\Http\Controllers\storage;

use Storage;
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
            $name = $this->sanitizeName($name);
            $path = (empty($parent) ? '' : (trim($parent, '/'))).'/'.$name['name'];

            Storage::makeDirectory($path);

            return ['success' => true, 'path' => '/'.$path];
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


/*

    public function createDirectoryIndex ($directory = null) {

        if (empty($directory)) {
            $directories = Storage::directories('storage');
        }
        else {
            $directory = trim($directory, '/');
            $directory = explode('/', $directory);
            $directories = [$directory = 'storage/'.$directory[0]];
        }

        foreach ($directories as $dir) {
            $dir = substr($dir, 8);

            $content = array_map(function ($path) {
                return substr($path, 8);
            }, Storage::allDirectories('storage/'.$dir));

            array_unshift($content, $dir);

            Storage::put('indices/directories/'.$dir.'.json', json_encode($content, JSON_UNESCAPED_UNICODE));
        }
    }

    // create MetaFile for User Upload
    public function createMetaForUpload ($path, $file, $meta = null) {
        $data = [
            'title'             => empty($meta['title']) ? null : $meta['title'],
            'originalFileName'  => empty($meta['originalFileName']) ? $file->getClientOriginalName() : $meta['originalFileName'],
            'size'              => empty($meta['size']) ? $file->getSize() : $meta['size'],
            'type'              => empty($meta['type']) ? $file->getMimeType() : $meta['type'],
            'modified'          => empty($meta['modified']) ? null : $meta['modified'],
            'public'            => empty($meta['public']) ? false : $meta['public'],
            'uploadEmail'       => empty($meta['email']) ? null : $meta['email'],
            'uploadDate'        => date('Y-m-d H:i:s')
        ];

        if (empty($data['uploadEmail'])) {
            $auth = Auth::user();
            if (!empty($auth)) $data['uploadEmail'] = $auth->email;
        }

        $data['creatorEmail'] = $data['uploadEmail'];
        $data['creatorDate'] = $data['uploadDate'];

        return $this->createMeta($path, $data);
    }

    // create MetaFile (use input data if given)
    public function createMeta ($path, $meta = null) {

        $metaPath = $path.'.META';

        if (empty($meta['modified'])) {
            $lastmodified = Storage::LastModified('storage/'.$path);
            $lastmodified = DateTime::createFromFormat("U", $lastmodified);
            $meta['modified'] = $lastmodified->format('Y-m-d H:i:s');
        }

        $data = [
            'id'                => $metaPath,
            'relatedFile'       => [
                'id'                => $path,
                'originalFileName'  => empty($meta['originalFileName']) ? null : $meta['originalFileName'],
                'size'              => empty($meta['size']) ? Storage::size('storage/'.$path) : $meta['size'],
                'type'              => empty($meta['type']) ? Storage::mimeType('storage/'.$path) : $meta['type'],
                'lastModified'      => empty($meta['modified']) ? null : $meta['modified'],
                'uploadedBy'        => empty($meta['uploadEmail']) ? null : $meta['uploadEmail'],
                'uploadedAt'        => empty($meta['uploadDate']) ? null : $meta['uploadDate']
            ],
            'createdBy'         => empty($meta['creatorEmail']) ? env('MAIL_FROM_ADDRESS') : $meta['creatorEmail'],
            'createdAt'         => empty($meta['creatorDate']) ? date('Y-m-d H:i:s') : $meta['creatorDate'],
            'public'            => empty($meta['public']) ? false : true,
            'data' => []
        ];

        $success = Storage::put('storage/'.$metaPath, json_encode($data, JSON_UNESCAPED_UNICODE));
        if (!empty($success)) $success = $this->indexMeta($path, $data);

        return empty($success) ? false : $metaPath;
    }

    // append to existing MetaFile
    public function appendMeta ($path, $meta) {

        $metaPath = $path.'.META';
        $storageMetaPath = 'storage/'.$metaPath;

        if (Storage::missing('storage/'.$path)) return false;
        if (Storage::missing($storageMetaPath)) $data = $this->createMeta($path);

        $data = Storage::get($storageMetaPath);
        $data = json_decode($data, true);

        $auth = Auth::user();
        $meta['modifiedBy'] = empty($auth) ? null : $auth->email;
        $meta['modifiedAt'] = date('Y-m-d H:i:s');

        array_unshift($data['data'], $meta);

        $success = Storage::put($storageMetaPath, json_encode($data, JSON_UNESCAPED_UNICODE));
        if (!empty($success)) $success = $this->indexMeta($path, $data);

        return empty($success) ? false : $metaPath;
    }

    // Write to FileSearch
    public function indexMeta ($path, $meta) {
        if (!empty($meta['relatedFile']['originalFileName'])) $data[] = 'originalFileName::'.$meta['relatedFile']['originalFileName'];
        if (!empty($meta['relatedFile']['uploadedBy'])) $data[] = 'uploadedBy::'.$meta['relatedFile']['uploadedBy'];

        if(!empty($meta['data'][0])) {
            foreach ($meta['data'][0] as $key => $val) {
                if (
                    !empty($val) &&
                    $key !== 'modifiedBy' &&
                    $key !== 'modifiedAt'
                ) $data[] = trim($key).'::'.str_replace('::', '', str_replace('&&', '', trim($val)));
            }
        }

        $data = implode('&&', $data);

        searchFiles::updateOrCreate([
            'filepath' => $path,
        ],[
            'filepath' => $path,
            'metadata' => $data,
            'is_public' => empty($meta['public']) ? 0 : 1
        ]);

        return true;
    }

    public function publishFile ($path, $value = null) {

        $metaPath = $path.'.META';

        if (Storage::missing('storage/'.$metaPath)) $metaPath = $this->createMeta($path);

        $meta = json_decode(Storage::get('storage/'.$metaPath), true);
        $public = isset($value) ? (empty($value) ? false : true) : (empty($meta['public']) ? true : false);
        $meta['public'] = $public;
        //die('state: '.$public);

        $success = Storage::put('storage/'.$metaPath, json_encode($meta, JSON_UNESCAPED_UNICODE));
        if (!empty($success)) searchFiles::where('filepath', $path)->update(['is_public' => empty($public) ? 0 : 1]);

        return empty($success) ? false : $metaPath;
    }
    */
