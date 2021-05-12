<?php

namespace App\Http\Controllers\dbi\entities\types;

class input_definitions {

    public function instructions () {
        return [
            'base' => [
                'table'     =>  config('dbi.tablenames.types'),
                'id'        =>  'id',

                'created'   =>  'created_at',
                'updated'   =>  'updated_at',
                'creator'   =>  'id_creator',
                'editor'    =>  'id_editor',
                'public'    =>  'publication_state',

                'cols'      =>  [
                    'name_private'          =>  'name',
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

                    'id_legend_r'           =>  'r_legend',
                    'id_design_r'           =>  'r_design',

                    'id_period'             =>  'period',
                    'date_start'            =>  'date_start',
                    'date_end'              =>  'date_end',
                    'date_string'           =>  'date_text_de',

                    'id_imageset'           =>  'image'
                ],
            ],

            'monograms' => [
                'table'     =>  config('dbi.tablenames.types_to_monograms'),
                'id_base'   =>  'id_type',
                'required'  =>  ['id'],

                'cols'      =>  [
                    'id_monogram'   =>  'id',
                    'side'          =>  'side',
                    'id_position'   =>  'position'
                ]
            ],

            'symbols' => [
                'table'     =>  config('dbi.tablenames.types_to_symbols'),
                'id_base'   =>  'id_type',
                'required'  =>  ['id'],

                'cols'      =>  [
                    'id_symbol'     =>  'id',
                    'side'          =>  'side',
                    'id_position'   =>  'position'
                ]
            ],

            'references' => [
                'table'     =>  config('dbi.tablenames.types_to_zotero'),
                'id_base'   =>  'id_type',
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
                    'this_type'     =>  'this'
                ]
            ],

            'links' => [
                'table'     =>  config('dbi.tablenames.types_to_links'),
                'id_base'   =>  'id_type',
                'required'  =>  ['link'],

                'cols'      =>  [
                    'link'      =>  'link',
                    'semantics' =>  'semantics',
                    'comment_de'=>  'comment_de',
                    'comment_en'=>  'comment_en'
                ]
            ],

            'groups' => [
                'table'     =>  config('dbi.tablenames.types_to_objectgroups'),
                'id_base'   =>  'id_type',
                'required'  =>  ['id'],

                'cols'      =>  [
                    'id_objectgroup'      =>  'id'
                ]
            ],

            'persons' => [
                'table'     =>  config('dbi.tablenames.types_to_persons'),
                'id_base'   =>  'id_type',
                'required'  =>  ['id'],

                'cols'      =>  [
                    'id_person'      =>  'id',
                    'id_function'    =>  'function'
                ]
            ],

            'findspots' => [

                'table'     =>  config('dbi.tablenames.types_to_findspots'),
                'id_base'   =>  'id_type',
                'required'  =>  ['id'],

                'cols'      =>  [
                    'id_findspot'      =>  'id',
                ]
            ],

            'hoards' => [

                'table'     =>  config('dbi.tablenames.types_to_hoards'),
                'id_base'   =>  'id_type',
                'required'  =>  ['id'],

                'cols'      =>  [
                    'id_hoard'      =>  'id',
                ]
            ],

            'variations' => [

                'table'     =>  config('dbi.tablenames.types_variations'),
                'id_base'   =>  'id_type',
                'required'  =>  ['de', 'en'],

                'cols'      =>  [
                    'description_de'    =>  'de',
                    'description_en'    =>  'en',
                    'comment'           =>  'comment'
                ]
            ]
        ];
    }
}
