<?php

namespace App\Http\Controllers\dbi\entities\coins;

class input_definitions {

    public function instructions () {
        return [
            'base' => [
                'table'     =>  config('dbi.tablenames.coins'),
                'id'        =>  'id',

                'created'   =>  'created_at',
                'updated'   =>  'updated_at',
                'creator'   =>  'id_creator',
                'editor'    =>  'id_editor',
                'public'    =>  'publication_state',

                'cols'      =>  [
                    // Name in DB table     =>  JSON Key

                    'description_private'   =>  'description',
                    'source_link'           =>  'source',
                    'comment_public'        =>  'comment_public_de',
                    'comment_public_en'     =>  'comment_public_en',
                    'comment_private'       =>  'comment_private',

                    'pecularities_de'       => 'pecularities_de',
                    'pecularities_en'       => 'pecularities_en',

                    'id_mint'               =>  'mint',

                    //'id_issuer'             =>  'issuer',
                    'id_authority'          =>  'authority',
                    'id_authority_person'   =>  'authority_person',
                    'id_authority_group'    =>  'authority_group',

                    'id_material'           =>  'material',
                    'id_denomination'       =>  'denomination',
                    'id_standard'           =>  'standard',

                    'id_legend_o'           =>  'o_legend',
                    'id_design_o'           =>  'o_design',
                    'id_die_o'              =>  'o_die',
                    'countermark_o_en'      =>  'o_countermark_en',
                    'countermark_o_de'      =>  'o_countermark_de',
                    'undertype_o_en'        =>  'o_undertype_en',
                    'undertype_o_de'        =>  'o_undertype_de',

                    'id_legend_r'           =>  'r_legend',
                    'id_design_r'           =>  'r_design',
                    'id_die_r'              =>  'r_die',
                    'countermark_r_en'      =>  'r_countermark_en',
                    'countermark_r_de'      =>  'r_countermark_de',
                    'undertype_r_en'        =>  'r_undertype_en',
                    'undertype_r_de'        =>  'r_undertype_de',

                    'id_period'             =>  'period',
                    'date_start'            =>  'date_start',
                    'date_end'              =>  'date_end',
                    'date_string'           =>  'date_text_de',

                    'id_findspot'           =>  'findspot',
                    'id_hoard'              =>  'hoard',

                    'diameter_min'          =>  'diameter_min',
                    'diameter_max'          =>  'diameter_max',
                    'diameter_ignore'       =>  'diameter_ignore',
                    'weight'                =>  'weight',
                    'weight_ignore'         =>  'weight_ignore',
                    'axis'                  =>  'axis',
                    'has_centerhole'        =>  'centerhole',
                    'is_forgery'            =>  'forgery',

                    'provenience'           =>  'provenience',
                    'id_owner_original'     =>  'owner_original',
                    'owner_is_unsure'       =>  'owner_unsure',
                    'collection_id'         =>  'collection',
                    'id_owner_reproduction' =>  'owner_reproduction',
                    'plastercast_id'        =>  'plastercast'
                ],
            ],

            'monograms' => [
                'table'     =>  config('dbi.tablenames.coins_to_monograms'),
                'id_base'   =>  'id_coin',
                'required'  =>  ['id'],

                'cols'      =>  [
                    'id_monogram'   =>  'id',
                    'side'          =>  'side',
                    'id_position'   =>  'position'
                ]
            ],

            'symbols' => [
                'table'     =>  config('dbi.tablenames.coins_to_symbols'),
                'id_base'   =>  'id_coin',
                'required'  =>  ['id'],

                'cols'      =>  [
                    'id_symbol'     =>  'id',
                    'side'          =>  'side',
                    'id_position'   =>  'position'
                ]
            ],

            'controlmarks' => [
                'table'     =>  config('dbi.tablenames.coins_to_controlmarks'),
                'id_base'   =>  'id_coin',
                'required'  =>  ['id'],

                'cols'      =>  [
                    'id_controlmark'=>  'id',
                    'side'          =>  'side',
                    'number'        =>  'count'
                ]
            ],

            'references' => [
                'table'     =>  config('dbi.tablenames.coins_to_zotero'),
                'id_base'   =>  'id_coin',
                'required'  =>  ['id'],

                'cols'      =>  [
                    'zotero_id'     =>  'id',
                    'page'          =>  'page',
                    'number'        =>  'number',
                    'plate'         =>  'plate',
                    'picture'       =>  'picture',
                    'annotation'    =>  'annotation',
                    'comment_de'    =>  'comment_de',
                    'comment_en'    =>  'comment_en',
                    'this_coin'     =>  'this'
                ]
            ],

            'links' => [
                'table'     =>  config('dbi.tablenames.coins_to_links'),
                'id_base'   =>  'id_coin',
                'required'  =>  ['link'],

                'cols'      =>  [
                    'link'          =>  'link',
                    'semantics' =>  'semantics',
                    'comment_de'=>  'comment_de',
                    'comment_en'=>  'comment_en'
                ]
            ],

            'groups' => [
                'table'     =>  config('dbi.tablenames.coins_to_objectgroups'),
                'id_base'   =>  'id_coin',
                'required'  =>  ['id'],

                'cols'      =>  [
                    'id_objectgroup' =>  'id'
                ]
            ],

            'persons' => [
                'table'     =>  config('dbi.tablenames.coins_to_persons'),
                'id_base'   =>  'id_coin',
                'required'  =>  ['id'],

                'cols'      =>  [
                    'id_person'     =>  'id',
                    'id_function'  =>  'function'
                ]
            ],

            'images' => [
                'table'     =>  config('dbi.tablenames.images'),
                'id_base'   =>  'CoinID',
                'required'  =>  ['kind'],

                'cols'      =>  [
                    'ImageID'               => 'id',
                    'ObverseImageFilename'  => 'o_link',
                    'ReverseImageFilename'  => 'r_link',
                    'ObversePhotographer'   => 'o_photographer',
                    'ReversePhotographer'   => 'r_photographer',
                    'ObjectType'            => 'kind',
                    'Path'                  => 'path'
                ]
            ]
        ];
    }
}
