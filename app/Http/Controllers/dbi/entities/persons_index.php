<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use App\Http\Controllers\dbi\handler\generic_select;
use Request;
use DB;


class persons_index implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        $src = [
            'http://clas-lgpn2.classics.ox.ac.uk/cgi-bin/lgpn_search.cgi?name=',
            '&style=json'
        ];

        $handler = new generic_select;

        // DB Pre Query if no ID is given
        if (empty($id)) {
            $input = Request::post();

            if (!empty($input['string'])) {
                if (empty($input['combination'] && empty($input['regex']))) {
                    if (empty($input['exactStart'])) $input['string'] = '%'.$input['string'];
                    if (empty($input['exactEnd'])) $input['string'] .= '%';
                }
                if (!empty($input['combination'])) {
                    $input['letters'] = preg_split('~~u', $input['string'], -1, PREG_SPLIT_NO_EMPTY);
                    $input['letters'] = array_unique($input['letters']);
                    sort($input['letters']);
                    unset($input['string']);
                }
            }

            if (empty($input['sort_by'])) $input['sort_by'] = 'id.ASC';

            $allowed_keys = [
                'where' => [
                    'id'            => 'id',
                    'string'        => ['name_sanitized', empty($input['regex']) ? 'LIKE' : 'RLIKE', '', ''],
                    'letters'       => ['letters', 'LIKE', '%', '%'],
                    'number'        => ['occurances', '>=', '', ''],
                    'from'          => ['date_from', '>=', '', ''],
                    'to'            => ['date_to', '<=', '', '']
                ],
                'order_by' => [
                    'id'            => 'id',
                    'number'        => 'occurances',
                    'name'          => 'name_sanitized'
                ]
            ];

            $prequery = $handler->preQuery(config('dbi.tablenames.lgpn_names'), $allowed_keys, $input);

            $where = $prequery['ids'];
        }

        // Set condition if ID is given
        else $where = [$id];

        if(!empty($where[0])) {
            $dbi = $handler->mainQuery(config('dbi.tablenames.lgpn_names'), null, $where);

            // Remapping
            $dbi = array_map(function ($i) use ($src) {
                if (empty($i['date_from']) && empty($i['date_to'])) $date = '--';
                else {
                    $date = [];
                    foreach (['date_from', 'date_to'] as $p) {
                        $year = $i[$p];
                        if (empty($year)) $date[$p] = '--';
                        else if ($year < 0) $date[$p] = ($year * (-1)).($p === 'date_from' && $i['date_to'] < 0 ? '' : ' BC');
                        else $date[$p] = $year.($p === 'date_from' && $i['date_to'] > 0 ? '' : ' AD');
                    }
                    $date = implode('–', $date);
                }

                return [
                    'id' => $i['id'],
                    'namePlain' => $i['name_sanitized'],
                    'nameOrthographic' => $i['name_original'],
                    'firstLetter' => $i['first_letter'],
                    'letters' => $i['letters'],
                    'dateFrom' => $i['date_from'],
                    'dateTo' => $i['date_to'],
                    'dateString' => $date,
                    'number' => $i['occurances'],
                    'link' => implode('', [$src[0], urlencode($i['name_original']), $src[1]])
                ];
            }, $dbi);
        }
        else $dbi = [];

        return empty($id) ? [
            'pagination'    => $prequery['pagination'],
            'where'         => $prequery['where'],
            'contents'      => $dbi
        ] : $dbi;

        /*$limitDefault = 50; //1001;
        $offsetDefault = 0;
        $src = [
            'http://clas-lgpn2.classics.ox.ac.uk/cgi-bin/lgpn_search.cgi?name=',
            '&style=json'
        ];

        $input = Request::post();
        $params = [];
        $pagination = [
            'offset' => empty($input['offset']) ? intval($input['offset']) : $offsetDefault,
            'limit' => empty($input['limit']) ? intval($input['offset']) : $offsetDefault
        ];

        // Query
        $raw = DB::table(config('dbi.tablenames.lgpn_names'))->get();
        $raw = json_decode($raw, true);

        $i = 0;
        $data = [];

        foreach ($raw as $raw) {
            $match = true;

            // Conditions
            if (!empty($params)) {

            }

            // Restructuring
            if ($match) {
                if (empty($i['date_from']) && empty($i['date_to'])) $date = '--';
                else {
                    $date = [];
                    foreach (['date_from', 'date_to'] as $p) {
                        $year = $i[$p];
                        if (empty($year)) $date[$p] = '--';
                        else if ($year < 0) $date[$p] = ($year * (-1)).($p === 'date_from' && $i['date_to'] < 0 ? '' : ' BC');
                        else $date[$p] = $year.($p === 'date_from' && $i['date_to'] > 0 ? '' : ' AD');
                    }
                    $date = implode('–', $date);
                }

                $data[] = [
                    'id' => $i['id'],
                    'namePlain' => $i['name_sanitized'],
                    'nameOrthographic' => $i['name_original'],
                    'firstLetter' => $i['first_letter'],
                    'letters' => $i['letters'],
                    'dateFrom' => $i['date_from'],
                    'dateTo' => $i['date_to'],
                    'dateString' => $date,
                    'number' => $i['occurances'],
                    'link' => implode('', [$src[0], urlencode($i['name_original']), $src[1]])
                ];
                ++$i;
            }
        }

        return [
            'pagination' => [
                'count' => $i,
            ],
            'contents' => $data
        ];

        // Postprocessing
        $dbi = array_map(function ($i) use ($src) {
            if (empty($i['date_from']) && empty($i['date_to'])) $date = '--';
            else {
                $date = [];
                foreach (['date_from', 'date_to'] as $p) {
                    $year = $i[$p];
                    if (empty($year)) $date[$p] = '--';
                    else if ($year < 0) $date[$p] = ($year * (-1)).($p === 'date_from' && $i['date_to'] < 0 ? '' : ' BC');
                    else $date[$p] = $year.($p === 'date_from' && $i['date_to'] > 0 ? '' : ' AD');
                }
                $date = implode('–', $date);
            }

            return [
                'id' => $i['id'],
                'namePlain' => $i['name_sanitized'],
                'nameOrthographic' => $i['name_original'],
                'firstLetter' => $i['first_letter'],
                'letters' => $i['letters'],
                'dateFrom' => $i['date_from'],
                'dateTo' => $i['date_to'],
                'dateString' => $date,
                'number' => $i['occurances'],
                'link' => implode('', [$src[0], urlencode($i['name_original']), $src[1]])
            ];
        }, $dbi);*/

        //return $dbi;
    }

    public function input ($user, $input) {
        die (abort(404, 'Not supported!'));
    }

    public function delete ($user, $input) {
        die (abort(404, 'Not supported!'));
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {

    }
}
