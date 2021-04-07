<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;


class news implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {        
        $query = DB::table(config('dbi.tablenames.news').' AS n')
            -> leftJoin (config('dbi.tablenames.news_strings').' AS s', 'n.id', '=', 's.id_news')
            -> select ([
                'n.id           AS id',
                'n.release_at   AS release',
                'n.creator      AS creator',
                'n.image        AS image',
                //'n.links        AS links',
                's.language     AS language',
                's.header       AS header',
                's.teaser       AS teaser',
                's.text         AS text',
                's.date_string  AS date',
                's.links        AS links'
            ]);

        // Set condition if ID is given
        if (!empty($id)) { $query -> where('n.id', $id); }

        $dbi = $query 
            -> orderBy('n.release_at', 'desc')
            -> get();

        $items_raw = json_decode($dbi, TRUE);

        foreach ($items_raw as $item) {
            if(!empty($item['links'])) {
                $link_exploded = explode('[|]', $item['links']);
                $links_value =  $link_exploded[0];
                $links_text = end($link_exploded);
            }
            else {
                $links_value = null;
                $links_text = null;
            }

            $items[$item['id']]['id']                           = $item['id'];
            $items[$item['id']]['release']                      = substr($item['release'], 0, 10);
            $items[$item['id']]['creator']                      = $item['creator'];
            $items[$item['id']]['image']                        = $item['image'];
            $items[$item['id']]['links']                        = $item['links'];
            $items[$item['id']]['header_'.$item['language']]    = $item['header'];
            $items[$item['id']]['teaser_'.$item['language']]    = $item['teaser'];
            $items[$item['id']]['text_'.$item['language']]      = $item['text'];
            $items[$item['id']]['date_'.$item['language']]      = $item['date'];
            $items[$item['id']]['links_value']                  = $links_value;
            $items[$item['id']]['links_text_'.$item['language']]     = $links_text;
        }


        return isset($items) ? array_values($items) : [];
    }

    public function input ($user, $input) {        
        $validation = $this -> validateInput($input);

        if(empty($validation['error'][0])) {
            $input = $validation['input'];

            $ID = DB::transaction(function () use ($input) {
                $values = [
                    'creator'       => $input['creator'], 
                    'image'         => $input['image'], 
                    //'links'         => $input['links'], 
                    'release_at'    => $input['release_at']
                ];

                // No ID given -> Insert new record
                if (empty($input['id'])) {
                    $ID = DB::table(config('dbi.tablenames.news')) -> insertGetID($values);
                }
                // ID given -> update existing record
                else {
                    DB::table(config('dbi.tablenames.news')) -> where('id', $input['id']) -> update($values);
                    $ID = $input['id'];
                }
                
                // Write text strings in helper table
                foreach ($input['strings'] as $string) {
                    DB::table(config('dbi.tablenames.news_strings')) -> updateOrInsert([
                        'id_news'   => $ID,
                        'language'  => $string['language']
                    ], [
                        'id_news'   => $ID,  
                        'header'    => $string['header'], 
                        'teaser'    => $string['teaser'], 
                        'text'      => $string['text'],
                        'date_string'      => $string['date'],
                        'links'     => $string['links']
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
            DB::table(config('dbi.tablenames.news_strings'))
                -> where('id_news', $input['id'])
                -> delete();

            DB::table(config('dbi.tablenames.news'))
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
        if (  
            ( empty ($input ['header_en']) OR empty ($input ['teaser_en']) OR empty ($input ['text_en']) ) 
            AND
            ( empty ($input ['header_de']) OR empty ($input ['teaser_de']) OR empty ($input ['text_de']) )
        ) {
            $error[] = config('dbi.responses.validation.com.text');
        }
        
        // Return validated input
        if (empty($error)) {
            // JK: Define Variables for DB actions
            $validation ['id']              = empty ($input ['id'])         ? NULL : $input ['id'];
            $validation ['release_at']      = empty ($input ['release'])    ? date ('Y-m-d') : $input ['release'];
            $validation ['creator']         = empty ($input ['creator'])    ? NULL : trim ($input ['creator']);
            $validation ['image']           = empty ($input ['image'])      ? NULL : $input ['image'];
            
            //if ( !empty ($input ['header_en']) && !empty ($input ['teaser_en']) && !empty ($input ['text_en']) ) {
                $validation ['strings'] []  = array (
                    'language'              => 'en',
                    'header'                => empty ($input ['header_en'])  ? NULL : trim ($input ['header_en']),
                    'teaser'                => empty ($input ['teaser_en'])  ? NULL : trim ($input ['teaser_en']),
                    'text'                  => empty ($input ['text_en'])    ? NULL : trim ($input ['text_en']),
                    'date'                  => empty ($input ['date_en'])    ? NULL : trim ($input ['date_en']),
                    'links'                 => empty ($input ['links_value'])   ? NULL : ($input ['links_value'] . (!empty($input['links_text_en']) ? ('[|]' . $input['links_text_en']) : '')),
                );
            //}

            //if ( !empty ($input ['header_de']) && !empty ($input ['teaser_de']) && !empty ($input ['text_de']) ) {
                $validation ['strings'] []  = array (
                    'language'              => 'de',
                    'header'                => empty ($input ['header_de'])  ? NULL : trim ($input ['header_de']),
                    'teaser'                => empty ($input ['teaser_de'])  ? NULL : trim ($input ['teaser_de']),
                    'text'                  => empty ($input ['text_de'])    ? NULL : trim ($input ['text_de']),
                    'date'                  => empty ($input ['date_de'])    ? NULL : trim ($input ['date_de']),
                    'links'                 => empty ($input ['links_value'])   ? NULL : ($input ['links_value'] . (!empty($input['links_text_de']) ? ('[|]' . $input['links_text_de']) : '')),
                );
            //}           
            
            return ['input' => $validation];
        }
        // Return Error
        else { return ['error' => $error]; }
    }
}
