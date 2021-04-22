<?php

namespace App\Http\Controllers\dbi\import\resources;

use App\Http\Controllers\dbi\import\ImportInterface;
use Auth;
use DB;


class numismaticsorg implements ImportInterface {

    public function coins ($url) {

        // Check Auth
        if (Auth::user()->access_level < 10) { die(abort(403)); }

        $explode = explode('?', $url);
        $url = $explode[0];

        $raw = @file_get_contents ($url.'.jsonld?profile=linkedart') OR die ('Error: Could not parse '.$url.'. Please check the link and retry.');
        $raw = json_decode($raw, true);

        $items['url'] = $url;

        if (empty($raw['id'])) {
            die ('Error: Could not parse '.$url.'. Please check the link and retry.');
        }
        else {

            // JK: Duplicate Check ----------------------------------------------------------------------------------------------------------------------------
            if (DB::table ('cn_data.data_coins') -> where([['source_link', $raw['id']]], 1) -> exists()) {
                $dbi = DB::table('cn_data.data_coins')
                        -> select('id AS id')
                        -> where('source_link', '=', $raw['id'])
                        -> get();
                $dbi = json_decode($dbi, TRUE);

                $items['duplicates'] = [];

                foreach ($dbi as $duplicate) {
                    $items['duplicates'][] = $duplicate['id'];
                }
            }


            // JK: Processing ----------------------------------------------------------------------------------------------------------------------------------
            else {
                $items['duplicates'] = null;
                $items['data']['source']  = $raw['id'];
                $items['info'] = [];


                // Comment
                if (!empty($raw['_label'])) {
                    $items['data']['comment_private'] = $raw['_label'];
                }

                // Provenience
                //if (!empty ($raw ['current_owner'] ) )   { $items['data'] ['Provenience'] = $raw ['current_owner'] ['_label'];}

                // CollectionNr.
                if (!empty($raw ['identified_by'][1]))   {
                    $items['data']['collection_id'] = $raw['identified_by'][1]['content'];
                }

                // Owner
                if (!empty($raw['current_owner'])) {
                    $owner = $raw['current_owner']['_label'];

                    $dbi = DB::table ('cn_data.data_owners')
                        -> select(
                            'id AS id',
                            'owner AS info'
                        )
                        -> where('owner', 'like', '%'.$owner.'%')
                        -> get();
                    $dbi = json_decode ($dbi, TRUE);

                    if (isset($dbi[0]['id'])) {
                        $items['data']['owner_original'] = $dbi[0]['id'];
                        $items['info']['owner_original'] = $dbi[0]['info'];
                    }
                }

                // Dimension
                if (!empty($raw['dimension']))
                {
                    foreach ($raw['dimension'] as $item)
                    {
                        // Diameter
                        if ($item['classified_as'][0]['_label'] === 'diameter') {
                            $items['data']['diameter_max'] = floatval($item['value']);
                        }

                        // Weight
                        else if ($item['classified_as'][0]['_label'] === 'weight') {
                            $items['data']['weight'] = floatval($item['value']);
                        }

                        // Axis
                        else if ($item['classified_as'][0]['_label'] === 'die axis') {
                            $items['data']['axis'] = intval($item['value']);
                        }
                    }
                }

                // Produced by
                if (!empty($raw['produced_by'])) {
                    // Technique
                    //if (!empty ($raw ['produced_by'] ['technique']))      {$items['data'] ['DiameterMax'] = $item ['value'];}

                    // Time
                    if (!empty ($raw ['produced_by'] ['timespan'])) {
                        $Date_from                  = $raw ['produced_by'] ['timespan'] ['begin_of_the_begin'];
                        $Date_to                    = $raw ['produced_by'] ['timespan'] ['end_of_the_end'];
                        $items['data']['date_start']= $Date_from     = intval(substr ($Date_from, 0, 1) == '-' ? substr($Date_from, 0, 5) : substr ($Date_from, 1, 4));
                        $items['data']['date_end']  = $Date_to       = intval(substr ($Date_to, 0, 1) == '-' ? substr($Date_to, 0, 5) : substr ($Date_to, 1, 4));

                        if ($Date_from < 0 && $Date_to  < 0) {
                            $items['data']['date_text_de'] = $Date_from == $Date_to ? (($Date_from*-1).' v. Chr.') : (($Date_from*-1).'–'.($Date_to*-1).' v. Chr.');
                        }
                        elseif ($Date_from < 0 && $Date_to  > 0) {
                            $items['data']['date_text_de'] = ($Date_from*-1).' v. Chr. – '.$Date_to.' n. Chr.';
                        }
                        else {
                            $items['data']['date_text_de'] = $Date_from == $Date_to ? ($Date_from.' n. Chr.') : ($Date_from.'–'.$Date_to.' n. Chr.');
                        }
                    }

                    // Carried out by
                    if (!empty ($raw ['produced_by'] ['carried_out_by'] [0])) {
                        $dbi = null;
                        $explode = explode ('/',$raw ['produced_by'] ['carried_out_by'] [0] ['id']);

                        $dbi = DB::table ('cn_data.data_persons')
                            -> select('id AS id', 'person AS info')
                            -> where('nomisma_id_person', '=', end ($explode))
                            -> get();
                        $dbi = json_decode ($dbi, TRUE);

                        if (isset ($dbi [0] ['id'])) {
                            $items['data']['authority_person'] = $dbi [0] ['id'];
                            $items['info']['authority_person'] = $dbi [0] ['info'];
                            $items['data']['authority'] = 1;
                        }

                    }

                    // Produced at
                    if (!empty ($raw ['produced_by'] ['took_place_at'] [0]))
                    {
                        $dbi = null;
                        $mint = explode (' ', $raw ['produced_by'] ['took_place_at'] [0] ['_label']);

                        $dbi = DB::table ('cn_data.data_mints')
                            -> select (
                                'id AS id',
                                'mint AS info'
                            )
                            -> where ('nomisma_id', 'like', '%'.strtolower ($mint [0]).'%')
                            -> get();
                        $dbi = json_decode ($dbi, TRUE);

                        if (isset ($dbi [0] ['id'])) {
                            $items['data']['mint'] = $dbi [0] ['id'];
                            $items['info']['mint'] = $dbi [0] ['info'];
                        }

                    }

                    // Material
                    if (!empty ($raw ['made_of'] [0])) {
                        $dbi = null;
                        $material = explode (' ', $raw ['made_of'] [0] ['_label']);

                        $dbi = DB::table ('cn_data.data_materials')
                            -> select ( 'id AS id', 'material_de AS info' )
                            -> where ('material_en', 'like', '%'.strtolower ($material [0]).'%')
                            -> get();
                        $dbi = json_decode ($dbi, TRUE);

                        if (isset ($dbi [0] ['id'])) {
                            $items['data']['material'] = $dbi [0] ['id'];
                            $items['info']['material'] = $dbi [0] ['info'];
                        }

                    }

                    // Denomination
                    if (!empty ($raw ['classified_as'] [2])) {
                        $dbi = null;
                        $denomination = strtolower ( $raw ['classified_as'] [2] ['_label']);

                        $dbi = DB::table ('cn_data.data_denominations')
                            -> select ( 'id AS id', 'denomination_de AS info')
                            -> where ('denomination_en', 'like', '%'.$denomination.'%')
                            -> get();
                        $dbi = json_decode ($dbi, TRUE);

                        if (isset ($dbi [0] ['id'])) {
                            $items['data']['denomination'] = $dbi [0] ['id'];
                            $items['info']['denomination'] = $dbi [0] ['info'];
                        }

                    }
                }
            }

            return $items;
        }
    }



    // -------------------------------------------------------------------------------------------------------------------------------------
    // JK: TYPE ----------------------------------------------------------------------------------------------------------------------------
    // -------------------------------------------------------------------------------------------------------------------------------------
    public function types ($url) {

        // Check Auth
        if (Auth::user()->access_level < 10) { die(abort(403)); }

        $explode = explode('?', $url);
        $url = $explode[0];

        $raw    = @file_get_contents ($url.'.jsonld') OR die ('Error: Could not parse '.$url.'. Please check the link and retry.');
        $raw    = json_decode($raw, true) ['@graph'];

        $items['url'] = $url;

        if (empty($raw[0]['@id'])) {
            die ('Error: Could not parse '.$url.'. Please check the link and retry.');
        }
        else {

            // JK: Duplicate Check ----------------------------------------------------------------------------------------------------------------------------
            if (DB::table ('cn_data.data_types') -> where ([ ['source_link', $raw[0]['@id']] ], 1) -> exists ()) {
                $dbi = DB::table ('cn_data.data_types')
                        -> select ( 'id AS id' )
                        -> where ([['source_link', '=', $raw[0]['@id'] ]])
                        -> get();
                $dbi = json_decode ($dbi, TRUE);

                $items['duplicates'] = [];

                foreach ($dbi as $duplicate) {
                    $items['duplicates'][] = $duplicate['id'];
                }
            }


            // JK: Processing ----------------------------------------------------------------------------------------------------------------------------------
            else
            {
                $items['duplicates'] = null;
                $items['data']['source']  = $raw[0]['@id'];
                $items['info'] = [];


                // Comment ---------------------------------------------------------------------------------------------------------------------
                $index = [1, 2];

                foreach ($index as $i) {
                    $data = [];

                    foreach ($raw [$i] as $key1 => $value1)
                    {
                        if ($key1 != '@id')
                        {
                            $values = [];
                            $explode = explode (':', $key1);

                            foreach ($raw [$i] [$key1] as $key2 => $value2)
                            {
                                foreach ($raw [$i] [$key1] [$key2] as $value3)
                                {
                                    $values [] = $value3;
                                }
                            }

                            $data [] = end ($explode).': '.implode (', ', $values);

                            $description [$i] = implode ('; ', $data);
                        }
                    }
                }
                $items['data']['comment_private'] = 'OBVERSE: '.$description [1].' | REVERSE: '.$description [2];

                // DB: Denomination ---------------------------------------------------------------------------------------------------------------------
                if (isset ($raw [0] ['nmo:hasDenomination'] [0] ['@id']))
                {
                    $dbi = null;
                    $explode = explode ('/',$raw [0] ['nmo:hasDenomination'] [0] ['@id']);

                    $dbi = DB::table ('cn_data.data_denominations')
                        -> select ( 'id AS id', 'denomination_de AS info')
                        -> where ([['nomisma_id', '=', end ($explode) ]])
                        -> get();
                    $dbi = json_decode ($dbi, TRUE);

                    if (isset ($dbi [0] ['id'])) {
                        $items['data'] ['denomination'] = $dbi [0] ['id'];
                        $items['info'] ['denomination'] = $dbi [0] ['info'];
                    }
                }

                // DB: Mint ---------------------------------------------------------------------------------------------------------------------
                if (isset ($raw [0] ['nmo:hasMint'] [0] ['@id']))
                {
                    $dbi = null;
                    $explode = explode ('/',$raw [0] ['nmo:hasMint'] [0] ['@id']);

                    $dbi = DB::table ('cn_data.data_mints')
                        -> select ( 'id AS id', 'mint AS info' )
                        -> where ([['nomisma_id', '=', end ($explode) ]])
                        -> get();
                    $dbi = json_decode ($dbi, TRUE);

                    if (isset ($dbi [0] ['id'])) {
                        $items['data'] ['mint'] = $dbi [0] ['id'];
                        $items['info'] ['mint'] = $dbi [0] ['info'];
                    }
                }

                // Person ---------------------------------------------------------------------------------------------------------------------
                if (isset ($raw [0] ['nmo:hasAuthority'] [0] ['@id']))
                {
                    $dbi = null;
                    $explode = explode ('/',$raw [0] ['nmo:hasAuthority'] [0] ['@id']);

                    $dbi = DB::table ('cn_data.data_persons')
                        -> select ( 'id AS id', 'person AS info' )
                        -> where ([['nomisma_id_person', '=', end ($explode) ]])
                        -> get();
                    $dbi = json_decode ($dbi, TRUE);

                    if (isset ($dbi [0] ['id'])) {
                        $items['data'] ['authority_person'] = $dbi [0] ['id'];
                        $items['info'] ['authority_person'] = $dbi [0] ['info'];
                        $items['data'] ['authority'] = 1;
                    }
                }

                // DB: Material ---------------------------------------------------------------------------------------------------------------------
                if (isset ($raw [0] ['nmo:hasMaterial'] [0] ['@id']))
                {
                    $dbi = null;
                    $explode = explode ('/',$raw [0] ['nmo:hasMaterial'] [0] ['@id']);

                    $dbi = DB::table ('cn_data.data_materials')
                        -> select ( 'id AS id', 'material_de AS info' )
                        -> where ([['nomisma_id', '=', 'http://nomisma.org/id/'.end ($explode) ]])
                        -> get();
                    $dbi = json_decode ($dbi, TRUE);

                    if (isset ($dbi [0] ['id'])) {
                        $items['data'] ['material'] = $dbi [0] ['id'];
                        $items['info'] ['material'] = $dbi [0] ['info'];
                    }
                }

                // Date ---------------------------------------------------------------------------------------------------------------------
                $items['data'] ['date_start']            = $Date_from     = intval ($raw [0] ['nmo:hasStartDate'][0]['@value']);
                $items['data'] ['date_end']              = $Date_to       =  intval ($raw [0] ['nmo:hasEndDate'][0]['@value']);

                if ($Date_from < 0 && $Date_to  < 0)
                {
                    $items['data'] ['date_text_de']        = ($Date_from*-1) .'–'. ($Date_to*-1) . ' v. Chr.';
                }
                elseif ($Date_from < 0 && $Date_to  > 0) {
                    $items['data'] ['date_text_de']        = ($Date_from*-1) .' v. Chr. – '. $Date_to  . ' n. Chr.';
                }
                else
                {
                    $items['data'] ['date_text_de']        = $Date_from .'–'+ $Date_to  . ' n. Chr.';;
                }
            }

            return $items;
        }
    }
}
