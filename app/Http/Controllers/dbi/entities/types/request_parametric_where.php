<?php

namespace App\Http\Controllers\dbi\entities\types;

use DB;


class request_parametric_where {

    public function instructions () {
        $db = 'cn_data.';

        $where = [
            'id'                    => 't.id',            
            'id_creator'            => 't.id_creator',
            'id_editor'             => 't.id_editor',
            
            'state_public'          => 't.publication_state',

            'state_linked'          => [
                'where' => ['raw' => 'IF(ctt.id_type IS NOT NULL, 1, 0)'],
                'joins' => [
                    [config('dbi.tablenames.coins_to_types').' AS ctt', 'ctt.id_type', '=', 't.id']
                ]
            ],

            'id_coin'               => [
                'where' => 'ctt.id_coin',
                'joins' => [
                    [config('dbi.tablenames.coins_to_types').' AS ctt', 'ctt.id_type', '=', 't.id']
                ]
            ],            
            
            'state_inherited'       => [
                'where' => ['raw' => 'IF(cti.id_type IS NOT NULL, 1, 0)'],
                'joins' => [
                    [config('dbi.tablenames.coins_inherit').' AS cti', 'cti.id', '=', 'c.id']
                ]
            ],
            
            // General
            'state_source'          => ['raw' => 'IF(t.source_link IS NOT NULL, 1, 0)'],
            'source'                => ['t.source_link', 'LIKE', '%', '%'],
            
            'state_comment_private' => ['raw' => 'IF(t.comment_private > \'\', 1, 0)'],
            'comment_private'       => ['t.comment_private', 'LIKE', '%', '%'],
            
            'state_comment_public'  => ['raw' => 'IF(t.comment_public > \'\' || t.comment_public_en > \'\', 1, 0)'],
            'comment_public'        => [
                ['raw' => 'CONCAT_WS("|", t.comment_public, t.comment_public_en)'], 'LIKE', '%', '%'
            ],

            'description_private'   => ['t.description_private', 'LIKE', '%', '%'],
            'name_private'          => 't.name_private',
            
            'id_owner'              => [
                'where' => 'c.id_owner_original',
                'joins' => [
                    [config('dbi.tablenames.coins_to_types').' AS ctt',    'ctt.id_type',  '=', 't.id'],
                    [config('dbi.tablenames.coins').'          AS c',      'c.id',         '=', 'ctt.id_coin'],
                ]
            ],

            'id_reference'          => [
                'where' => 'tref.zotero_id',
                'joins' => [
                    [config('dbi.tablenames.types_to_zotero').' AS tref', 'tref.id_type', '=', 't.id']
                ]
            ],
            
            'id_person'             => [
                'where' => 'ttp.id_person',
                'joins' => [
                    [config('dbi.tablenames.types_to_persons').' AS ttp', 'ttp.id_type', '=', 't.id']
                ]
            ],

            'id_findspot'           => [
                'where' => 'c.id_findspot',
                'joins' => [
                    [config('dbi.tablenames.coins_to_types').' AS ctt',    'ctt.id_type',  '=', 't.id'],
                    [config('dbi.tablenames.coins').'          AS c',      'c.id',         '=', 'ctt.id_coin'],
                ]
            ],
            'id_hoard'              => [
                'where' => 'c.id_hoard',
                'joins' => [
                    [config('dbi.tablenames.coins_to_types').' AS ctt',    'ctt.id_type',  '=', 't.id'],
                    [config('dbi.tablenames.coins').'          AS c',      'c.id',         '=', 'ctt.id_coin'],
                ]
            ],

            // Production
            'id_mint'               => 't.id_mint',
            'id_region'             => [
                'where' => 'sr.id_region',
                'joins' => [
                    [config('dbi.tablenames.mints').'                  AS mm', 'mm.id',                '=', 't.id_mint'],
                    [config('dbi.tablenames.regions_to_subregions').'  AS sr', 'sr.nomisma_id_region', '=', 'mm.imported_nomisma_subregion']
                ]
            ],

            'id_authority'          => 't.id_authority',
            'id_issuer'             => 't.id_issuer',
            'id_authority_person'   => 't.id_authority_person',
            'id_authority_group'    => 't.id_authority_group',
            
            'id_material'           => 't.id_material',    
            'id_denomination'       => 't.id_denomination',    
            'id_standard'           => 't.id_standard',

            'id_period'             => 't.id_period',
            'date_start'            => ['t.date_start', '>='],
            'date_end'              => ['t.date_end',   '<='],

            'id_design'             => [
                ['raw' => 'CONCAT_WS("|", "c", t.id_design_o, t.id_design_r, "c")'], 'LIKE', '%|', '|%'
            ],
            'id_legend'             => [
                ['raw' => 'CONCAT_WS("|", "c", t.id_legend_o, t.id_legend_r, "c")'], 'LIKE', '%|', '|%'
            ],
            'id_monogram'           => [ 
                'where' => 'ttm.id_monogram',
                'joins' => [
                    [config('dbi.tablenames.types_to_monograms').' AS ttm', 'ttm.id_type', '=', 't.id']
                ]
            ],
            'id_symbol'           => [ 
                'where' => 'tts.id_symbol',
                'joins' => [
                    [config('dbi.tablenames.types_to_symbols').' AS tts', 'tts.id_type', '=', 't.id']
                ]
            ],
            'id_die'           => [
                'where' => ['raw' => 'CONCAT_WS("|", "c", c.id_die_o, c.id_die_r, "c")'], 'LIKE', '%|', '|%',
                'joins' => [
                    [config('dbi.tablenames.coins_to_types').' AS ctt',    'ctt.id_type',  '=', 't.id'],
                    [config('dbi.tablenames.coins').'          AS c',      'c.id',         '=', 'ctt.id_coin'],
                ]
            ],

            // technical
            'imported'              => [
                'where' => 'ts.import',
                'joins' => [
                    [config('dbi.tablenames.types_submitted').' AS ts', 'ts.id', '=', 't.id']
                ]
            ],
            'id_group'              => [
                'where' => 'tg.id_objectgroup',
                'joins' => [
                    [config('dbi.tablenames.types_to_objectgroups').' AS tg', 'tg.id_type', '=', 't.id']
                ]
            ],
            'has_images'          => ['raw' => 'IF(t.id_imageset IS NOT NULL, 1, 0)'],
        ];

        // Obverse / Reverse
        foreach (['o', 'r'] AS $side) {
            $sides[$side] = [
                $side.'_id_design'           => 't.id_design_'.$side,
                $side.'_id_legend'           => 't.id_legend_'.$side,

                $side.'_design'              => [
                    'where' => ['d.design_de', 'LIKE', '%', '%'],
                    'connector' => 'AND',
                    'joins' => [
                        [config('dbi.tablenames.designs').' AS d', 'd.id', '=', 't.id_design_'.$side]
                    ]
                ],

                $side.'_id_monogram'         => [
                    'where' => $side.'ttm.id_monogram',
                    'connector' => 'AND',
                    'joins' => [
                        [config('dbi.tablenames.types_to_monograms').' AS '.$side.'ttm', $side.'ttm.id_type', '=', 't.id']
                    ],   
                    'conditions' => [
                        [$side.'ttm.side', $side === 'o' ? 0 : 1]
                    ] 
                ],

                $side.'_id_symbol'           => [
                    'where' => $side.'tts.id_symbol',
                    'connector' => 'AND',
                    'joins' => [
                        [config('dbi.tablenames.types_to_symbols').' AS '.$side.'tts', $side.'tts.id_type', '=', 't.id']
                    ],
                    'conditions' => [
                        [$side.'tts.side', $side === 'o' ? 0 : 1]
                    ]
                ],
            ];
        }

        return array_merge($where, $sides['o'], $sides['r']);
    }
}