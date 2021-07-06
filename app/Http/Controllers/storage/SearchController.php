<?php

namespace App\Http\Controllers\storage;

use App\Http\Controllers\Controller;
use Response;
use Request;
use DB;
use App\Models\searchFiles;


class SearchController extends Controller {

    public function select () {
        $request = Request::Post();

        if (empty($request['query'])) return Response::json([], 404);

        $string = trim($request['query']);
        if (isset($request['published'])) $published = $request['published'] == 1 ? 1 : 0;
        $isRegex = empty($request['regex']) ? false : true;
        $isCs = empty($request['case-sensitive']) ? false : true;

        $query = searchFiles::select([
            'filepath AS path',
            'metadata AS data',
            'is_public AS public',
            'created_at AS created',
            'updated_at AS modified'
        ])
        ->where(function ($queryByOr) use ($string, $isRegex, $isCs) {

            // Split by OR
            foreach ($this->splitString($string, false) as $byOr) {
                $queryByOr->orWhere(function ($queryByAnd) use (&$byOr, $isRegex, $isCs) {
                    $byOr = trim($byOr);

                    // Split by AND
                    foreach ($this->splitString($byOr, true) as $byAnd) {
                        $byAnd = trim($byAnd);

                        $queryByAnd->where(function ($subQuery) use ($byAnd, $isRegex, $isCs) {
                            $this->createWhere($subQuery, $byAnd, $isRegex, $isCs);
                        });
                    }
                });
            }
        });
        if (!empty($request['directory'])) $query->where('rootdirectory', $request['directory']);
        if (isset($published)) $query->where('is_public', $published);
        $matches = $query->get();

        $matches = json_decode($matches, true);

        return Response::json([
            'contents' => $matches
        ]);
    }

    public function splitString ($string, $isAnd = false) {
        if ($isAnd) {
            // Escape Space after NOT to avoid blank split
            $string = str_replace('NOT ', 'NOT___', $string);
            // Replace AND with blanks to make split easier
            $string = str_replace('AND', ' ', $string);
            // Add trailing blanks to enhance quoting-match
            $string = ' '.$string.' ';
            // Replace blanks in quoted strings to avoid being split
            $string = preg_replace_callback('/"(.*?)" /', function ($match) {
                return str_replace(' ', '___', trim($match[1])).' ';
            }, $string);
            // Trim and Replace multiple Spaces to avoid empty matches after split
            $string = trim(preg_replace('/\s+/', ' ', $string));
            $split = explode(' ', $string);
            // Replace Escape-Chars in quoted Strings
            foreach ($split as $key => $val) {
                if (empty($val)) unset($split[$key]);
                else $split[$key] = trim(str_replace('___', ' ', $val));
            }

            return $split;
        }

        else return explode(' OR ', $string);
    }

    public function createWhere ($query, $string, $isRegex = false, $isCs = false) {
        $connectedBy = '';

        // Look for NOT in String
        if (substr($string, 0, 4) === 'NOT ') {
            $string = trim(substr($string, 3));
            $connectedBy = 'NOT ';
        }

        // Look for stated columnnames (column::string)
        $split = explode('::', $string);

        if (empty($split[1])) {
            if (empty($isRegex)) $string = '%'.$string.'%';
        }
        else {
            $isRegex = true;
            $string = '"'.$split[0].'[^"]*'.$split[1];
        }

        // Add R if REGEX is required
        $connectedBy .= ($isRegex === true) ? 'RLIKE' : 'LIKE';

        $query->where(DB::Raw('metadata COLLATE '.($isCs === true ? 'utf8mb4_bin' : 'utf8mb4_unicode_ci')), $connectedBy, $string);
    }
}
