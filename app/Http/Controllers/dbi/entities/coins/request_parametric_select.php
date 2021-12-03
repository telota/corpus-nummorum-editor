<?php

namespace App\Http\Controllers\dbi\entities\coins;

use DB;


class request_parametric_select {

    public function instructions ($user) {

        if ($user['id'] === 0) {
            $select = ['self' =>  ['raw' => 'CONCAT("'.env('APP_API').'/coins/", c.id)']];
        }
        else {
            $select = [
                'id'                =>  'c.id',
                'kind'              =>  ['raw' => '"Coin"'],
                'name'              =>  ['raw' => 'CONCAT_WS(" ", "cn", "coin", c.id)'],
                'self'              =>  ['raw' => 'CONCAT("'.env('APP_API').'/coins/", c.id)'],

                'is_forgery'        =>  'c.is_forgery',

                'types'             =>  ['raw' => '(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                        "self",         CONCAT("'.env('APP_API').'/types/", ct.id_type),
                        "id",           ct.id_type,
                        "inherited",    IF(cti.id IS NOT NULL, 1, 0))
                    )
                    FROM '.config('dbi.tablenames.coins_to_types').'        AS ct
                    LEFT JOIN '.config('dbi.tablenames.coins_inherit').'    AS cti on cti.id = ct.id_coin
                    WHERE ct.id_coin = c.id
                )'],

                'owner'             =>  ['raw' => 'JSON_OBJECT(
                    "city",         o.city,
                    "name",         '.($user['level'] > 9 ? 'o.owner' : 'IF(o.is_name_public = 1, o.owner, null)').'
                )'],

                'mint'              =>  ['raw' => 'JSON_OBJECT(
                    "link",         IF(mint.nomisma_id > "", CONCAT("'.config('dbi.url.nomisma').'", mint.nomisma_id), null),
                    "text",         JSON_OBJECT(
                        "de",       mint.mint,
                        "en",       mint.mint
                    ),
                    "region",       (SELECT JSON_OBJECT(
                        "id",       r.id,
                        "link",     CONCAT("'.config('dbi.url.nomisma').'", sr.nomisma_id_region),
                        "text",     JSON_OBJECT(
                            "de",   r.de,
                            "en",   r.en
                        ))
                        FROM '.config('dbi.tablenames.regions_to_subregions').'     AS sr
                        LEFT JOIN '.config('dbi.tablenames.regions').'              AS r ON r.id = sr.id_region
                        WHERE sr.nomisma_id_region = mint.imported_nomisma_subregion
                    )
                )'],
                // IFNULL(( SELECT n.mint_name FROM cn_data.nomisma_mints_text AS n WHERE n.nomisma_id_mint = mint.nomisma_id && n.language = "de"), mint.mint)

                'diameter'          =>  ['raw' => 'JSON_OBJECT(
                    "value_max",    IF(c.diameter_max > "", c.diameter_max, null),
                    "unit",         "mm"
                )'],
                'weight'            =>  ['raw' => 'JSON_OBJECT(
                    "value",        IF(c.weight> "", c.weight, null),
                    "unit",         "g"
                )'],
                'material'          =>  ['raw' => 'JSON_OBJECT(
                    "abbreviation", UPPER(IF(mat.nomisma_id > "", mat.nomisma_id, SUBSTRING(mat.nomisma_id, 0, 2))),
                    "text",         JSON_OBJECT(
                        "en",       mat.material_en,
                        "de",       mat.material_de
                ))'],
                'denomination'      =>  ['raw' => 'JSON_OBJECT(
                    "text",         JSON_OBJECT(
                        "en",       de.denomination_en,
                        "de",       de.denomination_de
                ))'],

                'date'              =>  ['raw' => 'JSON_OBJECT(
                    "text",         JSON_OBJECT(
                        "en",       null,
                        "de",       c.date_string
                    ),
                    "period",       JSON_OBJECT(
                        "id",       e.id,
                        "link",     IF(e.nomisma_id > "", CONCAT("'.config('dbi.url.nomisma').'", e.nomisma_id), null),
                        "text",         JSON_OBJECT(
                            "en",   e.period_en,
                            "de",   e.period_de
                )))']
            ];

            // Obverse / Reverse
            foreach (['o', 'r'] AS $side) {
                //if($user['level'] > 9) {
                    $select[$side === 'o' ? 'obverse' : 'reverse']  = ['raw' => 'JSON_OBJECT(
                        "design", IF(c.id_design_'.$side.' IS NULL && c.id_die_'.$side.' IS NOT NULL,
                            (SELECT JSON_OBJECT(
                            "id",       dd.id,
                            "text",     JSON_OBJECT(
                                "en",   dd.design_en,
                                "de",   dd.design_de
                            ))
                            FROM '.config('dbi.tablenames.dies').'          AS d
                            LEFT JOIN '.config('dbi.tablenames.designs').'  AS dd ON dd.id = d.id_design
                            WHERE d.id = c.id_die_'.$side.'
                        ),
                            JSON_OBJECT(
                                "id",     d'.$side.'.id,
                                "text",   JSON_OBJECT(
                                    "en", d'.$side.'.design_en,
                                    "de", d'.$side.'.design_de
                        ))),

                        "legend", IF(c.id_legend_'.$side.' IS NULL && c.id_die_'.$side.' IS NOT NULL,
                            (SELECT JSON_OBJECT(
                                "string",   l.legend
                            )
                            FROM '.config('dbi.tablenames.dies').'          AS d
                            LEFT JOIN '.config('dbi.tablenames.legends').'  AS l ON l.id = d.id_legend
                            WHERE d.id = c.id_die_'.$side.'
                        ),
                            (SELECT JSON_OBJECT(
                                "string",   l.legend
                            )
                            FROM '.config('dbi.tablenames.legends').' AS l
                            WHERE l.id = c.id_legend_'.$side.'
                        )),

                        "monograms", (SELECT JSON_ARRAYAGG(JSON_OBJECT(
                                "id",           m.id,
                                "position",     JSON_OBJECT(
                                    "id",       p.id,
                                    "text",     JSON_OBJECT(
                                        "en",   p.position_en,
                                        "de",   p.position_de
                                )),
                                "combination",  m.lettercomb,
                                "link",         IF(m.image > "", CONCAT("'.config('dbi.url.storage').'", "Monograms/", m.image), null)
                            ))
                            FROM '.config('dbi.tablenames.coins_to_monograms').'    AS ctm
                            LEFT JOIN '.config('dbi.tablenames.monograms').'        AS m    ON m.id = ctm.id_monogram
                            LEFT JOIN '.config('dbi.tablenames.positions').'        AS p    ON p.id = ctm.id_position
                            WHERE ctm.id_coin = c.id && ctm.side = '.($side === 'o' ? 0 : 1).'),

                        "symbols", (SELECT JSON_ARRAYAGG(JSON_OBJECT(
                                "id",         s.id,
                                "position",   JSON_OBJECT(
                                    "id",     p.id,
                                    "text",   JSON_OBJECT(
                                        "en", p.position_en,
                                        "de", p.position_de
                                )),
                                "text",       JSON_OBJECT(
                                    "en", IFNULL(s.symbol_en, s.Symbol_de),
                                    "de", s.Symbol_de
                                ),
                                "link",       IF(s.image > "", CONCAT("'.config('dbi.url.storage').'", "Symbols/", s.image), null)
                            ))
                            FROM '.config('dbi.tablenames.coins_to_symbols').'  AS cts
                            LEFT JOIN '.config('dbi.tablenames.symbols').'      AS s    ON s.id = cts.id_symbol
                            LEFT JOIN '.config('dbi.tablenames.positions').'    AS p    ON p.id = cts.id_position
                            WHERE cts.id_coin = c.id && cts.side = '.($side === 'o' ? 0 : 1).'
                        )
                    )'];
                /*}
                else {
                    $select[$side === 'o' ? 'obverse' : 'reverse']  = ['raw' => 'JSON_OBJECT(
                        "design", IF(c.id_design_'.$side.' IS NULL && c.id_die_'.$side.' IS NOT NULL,
                            (SELECT JSON_OBJECT(
                            "id",       dd.id,
                            "text",     JSON_OBJECT(
                                "en",   dd.design_en,
                                "de",   dd.design_de
                            ))
                            FROM '.config('dbi.tablenames.dies').'          AS d
                            LEFT JOIN '.config('dbi.tablenames.designs').'  AS dd ON dd.id = d.id_design
                            WHERE d.id = c.id_die_'.$side.'
                        ),
                            JSON_OBJECT(
                                "id",     d'.$side.'.id,
                                "text",   JSON_OBJECT(
                                    "en", d'.$side.'.design_en,
                                    "de", d'.$side.'.design_de
                        ))),

                        "legend", IF(c.id_legend_'.$side.' IS NULL && c.id_die_'.$side.' IS NOT NULL,
                            (SELECT JSON_OBJECT(
                                "string",   l.legend
                            )
                            FROM '.config('dbi.tablenames.dies').'          AS d
                            LEFT JOIN '.config('dbi.tablenames.legends').'  AS l ON l.id = d.id_legend
                            WHERE d.id = c.id_die_'.$side.'
                        ),
                            (SELECT JSON_OBJECT(
                                "string",   l.legend
                            )
                            FROM '.config('dbi.tablenames.legends').' AS l
                            WHERE l.id = c.id_legend_'.$side.'
                        ))
                    )'];
                }*/
            }

            // Images
            $select['images'] = ['raw' => '(SELECT JSON_ARRAYAGG( JSON_OBJECT(
                "id",             img.ImageID,
                "kind",           IFNULL(img.ObjectType, "unknown"),
                "obverse",        JSON_OBJECT(
                    "link",       IF( img.ObverseImageFilename > "", CONCAT( IF( img.Path > "", IF( SUBSTRING( img.Path, -1, 1 ) != "/", CONCAT( img.Path, "/" ), img.Path ),""), img.ObverseImageFilename ), null),
                    "kind",       IF( img.ObverseImageFilename > "", img.ObjectType, null),
                    "bg_color",   IF( img.BackgroundColor > "" && img.ObverseImageFilename > "", img.BackgroundColor, null)
                ),
                "reverse",        JSON_OBJECT(
                    "link",       IF( img.ReverseImageFilename > "", CONCAT( IF( img.Path > "", IF( SUBSTRING( img.Path, -1, 1 ) != "/", CONCAT( img.Path, "/"), img.Path ),""), img.ReverseImageFilename ), null),
                    "kind",       IF( img.ReverseImageFilename > "", img.ObjectType, null),
                    "bg_color",   IF( img.BackgroundColor > "" && img.ReverseImageFilename > "", img.BackgroundColor, null)
                )))
                FROM '.config('dbi.tablenames.images').' AS img
                WHERE img.CoinID = c.id'.($user['level'] > 9 ? '' : '&& img.is_private = 0') .'
                GROUP BY img.ImageID
                ORDER BY img.ObjectType DESC
                LIMIT 1
            )']; //"'.$storage.'",

            // Hide publication state for API
            if($user['level'] > 9) {
                $select['public'] = 'c.publication_state';
                $select['name_private'] = 'c.description_private';
            }
        }

        return $select;
    }
}


/*"legend",
    IF( lo.id IS NOT NULL, JSON_OBJECT(
        "string", lo.legend),
    IF( c.id_die_o IS NOT NULL,
        (SELECT JSON_OBJECT(
            "string", dl.Legend
        ) FROM '.$db.'data_dies        AS d
        LEFT JOIN '.$db.'data_legends  AS dl   ON dl.id = d.id_legend
        WHERE d.id = c.id_die_o ),
    JSON_OBJECT(
        "string", null)
    ))*/
