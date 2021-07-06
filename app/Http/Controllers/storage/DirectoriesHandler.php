<?php

namespace App\Http\Controllers\storage;

use Storage;
use App\Http\Controllers\storage\StorageActions;

class DirectoriesHandler {

    public function createMintDirectory ($nomisma) {

        if (empty($nomisma)) return false;

        $actions = new StorageActions();
        $name = $actions->sanitizeName(trim($nomisma));
        $path = 'coins-images/'.$name['name'];

        Storage::makeDirectory($path);

        return true;
    }
}
