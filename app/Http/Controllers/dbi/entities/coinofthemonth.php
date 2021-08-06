<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;
use Response;


class coinofthemonth implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        $query = DB::table(config('dbi.tablenames.com').' AS c')
            -> leftJoin (config('dbi.tablenames.com_strings').' AS s', 'c.id', '=', 's.id_com')
            -> select ([
                'c.id           AS id',
                'c.year         AS release_year',
                'c.month        AS release_month',
                'c.presented_by AS presented_by',
                'c.file_image   AS image',
                'c.id_coin      AS id_coin',
                'c.id_type      AS id_type',
                's.language     AS language',
                's.title        AS header',
                's.teaser       AS teaser',
                's.description  AS text'
            ]);

        // Set condition if ID is given
        if (!empty($id)) $query->where(function ($subquery) use ($id) {
            $subquery->orWhere(DB::raw('CONCAT(c.year, "-", LPAD(c.month, 2, 0))'), $id);
            $subquery->orWhere('c.id', $id);
        });

        $dbi = $query
            -> orderBy('c.year', 'desc')
            -> orderBy('c.month', 'desc')
            -> get();

        $items_raw = json_decode($dbi, TRUE);

        foreach ($items_raw as $item) {
            $id = $item['id'];

            $items[$id]['id']                           = $id;
            $items[$id]['release']                      = $item['release_year'].'-'.sprintf('%02s', $item['release_month']).'-01';
            $items[$id]['presented_by']                 = $item['presented_by'];
            $items[$id]['is_type']                      = !empty($item['id_coin']) ? false : true;
            $items[$id]['id_cn']                        = !empty($item['id_coin']) ? $item['id_coin'] : $item['id_type'];
            $items[$id]['header_'.$item['language']]    = $item['header'];
            $items[$id]['teaser_'.$item['language']]    = $item['teaser'];
            $items[$id]['text_'.$item['language']]      = $item['text'];

            $items[$id]['image']                        = !empty (explode ('/', $item['image']) [1]) ? $item['image'] : ('CoinOfMonth/'.$item['image']);
        }

        return isset($items) ? array_values($items) : [];
    }

    public function input ($user, $input) {
        $validation = $this -> validateInput($input);

        if(empty($validation['error'][0])) {
            $input = $validation['input'];

            $ID = DB::transaction(function () use ($input) {
                $values = [
                    'presented_by'  => $input['presented_by'],
                    'file_image'    => $input['image'],
                    'id_coin'       => !$input['is_type'] ? $input['id_cn'] : null,
                    'id_type'       => $input['is_type'] ? $input['id_cn'] : null,
                    'year'          => $input['release_year'],
                    'month'         => $input['release_month'],
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    $ID = DB::table(config('dbi.tablenames.com')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.com')) -> where('id', $input['id']) -> update($values);
                    $ID = $input['id'];
                }

                // Write text strings in helper table
                foreach ($input['strings'] as $string) {
                    DB::table(config('dbi.tablenames.com_strings')) -> updateOrInsert([
                        'id_com'        => $ID,
                        'language'      => $string ['language'],
                    ], [
                        'title'         => $string ['header'],
                        'teaser'        => $string ['teaser'],
                        'description'   => $string ['text']
                    ]);
                }

                return $ID;
            });

            return $ID;
        }
        // Validation Error
        else { return ['error' => $validation['error']]; }
    }

    public function delete ($user, $input) {
        DB::transaction(function () use ($input) {
            DB::table(config('dbi.tablenames.com_strings'))
                -> where('id_com', $input['id'])
                -> delete();

            DB::table(config('dbi.tablenames.com'))
                -> where('id', $input['id'])
                -> delete();
        });

        return true;
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {
        // Check Text
        if ((empty($input['header_en']) || empty($input['teaser_en']) || empty($input['text_en']))
            &&
            (empty($input ['header_de']) || empty($input['teaser_de']) || empty($input['text_de']))
        ) {
            $error[] = config('dbi.responses.validation.com.text');
        }
        // JK: check image
        if (empty($input['image'])) {
            $error[] = config('dbi.responses.validation.general.image');
        }
        // JK: Check if selected coin/type exists
        if (empty($input['id_cn'])) {
            $error[] = config('dbi.responses.validation.com.no_id');
        }
        else if (!DB::table(config('dbi.tablenames.'.($input['is_type'] ? 'types' : 'coins'))) -> where('id', $input['id_cn']) -> exists() ) {
            $error[] = config('dbi.responses.validation.com.unknown_'.($input['is_type'] ? 'type' : 'coin'));
        }

        // Return validated input
        if (empty($error)) {
            // JK: Define Variables for DB actions
            $validation['id']              = empty($input['id'])             ? NULL : $input['id'];
            $validation['presented_by']    = empty($input['presented_by'])   ? NULL : trim($input['presented_by']);
            $validation['image']           = empty($input['image'])          ? NULL : $input['image'];
            $validation['is_type']         = $input['is_type'] === true      ? 1 : 0;
            $validation['id_cn']           = empty($input['id_cn'])          ? NULL : $input['id_cn'];
            $img_explode                   = explode('/', $input['image']);
            $validation['image']           = end($img_explode);

            // JK: handle Release Date
            if (empty($input['release'])) {
                $validation['release_year'] = date('n') < 12 ? date('Y') : (date('Y') + 1);
                $validation['release_month'] = date('n') < 12 ? (date('n') + 1) : 1;
            }
            else {
                $date_explode = explode('-', $input['release']);
                $validation['release_year'] = $date_explode[0];
                $validation['release_month'] = $date_explode[1];
            }

            // Create language arrays
            foreach (['de', 'en'] as $language) {
                if (!empty($input['header_'.$language]) && !empty($input['teaser_'.$language]) && !empty($input['text_'.$language])) {
                    $validation['strings'][] = [
                        'language'  => $language,
                        'header'    => empty($input['header_'.$language])  ? NULL : trim($input['header_'.$language]),
                        'teaser'    => empty($input['teaser_'.$language])  ? NULL : trim($input['teaser_'.$language]),
                        'text'      => empty($input['text_'.$language])    ? NULL : trim($input['text_'.$language])
                    ];
                }
            }

            return ['input' => $validation];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
