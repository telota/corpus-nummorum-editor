<?php

namespace App\Http\Controllers\dbi;

use Response;

class DataConverter {

    public function output ($entity, $id, $extension, $data) {
        $extension = strtolower($extension);

        if ($extension !== 'jsonld') {
            $message = array_map(function ($key) use ($extension) {
                return '"'.$extension.'" '.$key;
            }, config('dbi.responses.api.not_supported'));
            return Response::json(['error' => $message], 404);
        }

        return Response::json($data, 200);
    }
}
