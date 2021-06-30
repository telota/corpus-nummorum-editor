<?php

namespace App\Http\Controllers\dbi\entities\types;

use DB;


class request_parametric_select {

    public function instructions ($user) {

        if ($user['id'] === 0) {
            $select = ['self' =>  ['raw' => 'CONCAT("'.env('APP_API').'/types/", t.id)']];
        }
        else {
            $select = [
                'id'                =>  't.id',
                'kind'              =>  ['raw' => '"Type"'],
                'name'              =>  ['raw' => 'CONCAT_WS(" ", "cn", "type", t.id)'],
                'self'              =>  ['raw' => 'CONCAT("'.env('APP_API').'/types/", t.id)'],

                'coins'             =>  ['raw' => '(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                        "self",         CONCAT("'.env('APP_API').'/coins/", ct.id_coin),
                        "id",           ct.id_coin
                    ))
                    FROM '.config('dbi.tablenames.coins_to_types').' AS ct
                    WHERE ct.id_type = t.id
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

                'diameter'          =>  ['raw' => '(SELECT JSON_OBJECT(
                        "value_max",  CAST(AVG(c.diameter_max) AS DECIMAL(12,2)),
                        "count",      COUNT(c.id),
                        "unit",       "mm"
                    )
                    FROM '.config('dbi.tablenames.coins_to_types').'    AS ctt
                    LEFT JOIN '.config('dbi.tablenames.coins').'        AS c ON c.id = ctt.id_coin
                    WHERE ctt.id_type = t.id &&
                    c.diameter_max IS NOT NULL &&
                    c.diameter_max > 0 &&
                    c.diameter_ignore = 0
                )'],

                'weight'            =>  ['raw' => '(SELECT JSON_OBJECT(
                        "value",      CAST(AVG(c.weight) AS DECIMAL(12,2)),
                        "count",      COUNT( c.id ),
                        "unit",       "g"
                    )
                    FROM '.config('dbi.tablenames.coins_to_types').'    AS ctt
                    LEFT JOIN '.config('dbi.tablenames.coins').'        AS c ON c.id = ctt.id_coin
                    WHERE ctt.id_type = t.id &&
                    c.weight IS NOT NULL &&
                    c.weight > 0 &&
                    c.weight_ignore = 0
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
                        "de",       t.date_string
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
                        "design", JSON_OBJECT(
                            "id",     d'.$side.'.id,
                            "text",   JSON_OBJECT(
                                "en",   d'.$side.'.design_en,
                                "de",   d'.$side.'.design_de
                        )),

                        "legend", (SELECT JSON_OBJECT(
                                "string",   l.legend
                            )
                            FROM '.config('dbi.tablenames.legends').' AS l
                            WHERE l.id = t.id_legend_'.$side.'
                        ),

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
                            FROM '.config('dbi.tablenames.types_to_monograms').'    AS ttm
                            LEFT JOIN '.config('dbi.tablenames.monograms').'        AS m    ON m.id = ttm.id_monogram
                            LEFT JOIN '.config('dbi.tablenames.positions').'        AS p    ON p.id = ttm.id_position
                            WHERE ttm.id_type = t.id &&
                            ttm.side = '.($side === 'o' ? 0 : 1).'),

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
                            FROM '.config('dbi.tablenames.types_to_symbols').'  AS tts
                            LEFT JOIN '.config('dbi.tablenames.symbols').'      AS s    ON s.id = tts.id_symbol
                            LEFT JOIN '.config('dbi.tablenames.positions').'    AS p    ON p.id = tts.id_position
                            WHERE tts.id_type = t.id &&
                            tts.side = '.($side === 'o' ? 0 : 1).'
                        )
                    )'];
                /*}
                else {
                    $select[$side === 'o' ? 'obverse' : 'reverse']  = ['raw' => 'JSON_OBJECT(
                        "design", JSON_OBJECT(
                            "id",     d'.$side.'.id,
                            "text",   JSON_OBJECT(
                                "en",   d'.$side.'.design_en,
                                "de",   d'.$side.'.design_de
                        )),

                        "legend", (SELECT JSON_OBJECT(
                                "string",   l.legend
                            )
                            FROM '.config('dbi.tablenames.legends').' AS l
                            WHERE l.id = t.id_legend_'.$side.'
                        )
                    )'];
                }*/
            }

            // Images
            $select['images'] = ['raw' => 'IF(t.id_imageset > "", (SELECT JSON_ARRAYAGG(JSON_OBJECT(
                "id",         img.ImageID,
                "obverse",    JSON_OBJECT(
                    "link",     IF( img.ObverseImageFilename > "", CONCAT( IF( img.Path > "", IF( SUBSTRING( img.Path, -1, 1 ) != "/", CONCAT( img.Path, "/" ), img.Path ),""), img.ObverseImageFilename ), null),
                    "kind",     IF( img.ObverseImageFilename > "", img.ObjectType, null),
                    "bg_color", IF( img.BackgroundColor > "" && img.ObverseImageFilename > "", img.BackgroundColor, null )),
                "reverse",    JSON_OBJECT(
                    "link",     IF( img.ReverseImageFilename > "", CONCAT( IF( img.Path > "", IF( SUBSTRING( img.Path, -1, 1 ) != "/", CONCAT( img.Path, "/"), img.Path ),""), img.ReverseImageFilename ), null),
                    "kind",     IF( img.ReverseImageFilename > "", img.ObjectType, null),
                    "bg_color", IF( img.BackgroundColor > "" && img.ReverseImageFilename > "", img.BackgroundColor, null )
                )))
                FROM '.config('dbi.tablenames.images').' AS img
                WHERE img.ImageID = t.id_imageset),
                null
            )']; //"'.$storage.'",

            // Hide publication state for API
            if($user['level'] > 9) {
                $select['public'] = 't.publication_state';
                $select['name_private'] = ['raw' => 'CONCAT_WS(" | ", t.name_private, t.description_private)'];
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
