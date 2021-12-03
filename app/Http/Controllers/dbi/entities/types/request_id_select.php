<?php

namespace App\Http\Controllers\dbi\entities\types;

use DB;


class request_id_select {

    public function instructions ($user) {

        $select = [
            'id'                =>  't.id',
            'kind'              =>  ['raw' => '"Type"'],
            'name'              =>  ['raw' => 'CONCAT_WS(" ", "cn", "type", t.id)'],
            'self'              =>  ['raw' => 'CONCAT("'.env('APP_API').'/types/", t.id)'],
            'created_at'        =>  't.created_at',
            'updated_at'        =>  't.updated_at',
            'downloaded_at'     =>  ['raw' => 'NOW()'],

            'source'            =>  ['raw' => 'JSON_OBJECT(
                "created_at", IF(t.source_link > "", t.created_at, null),
                "imported",   IF(t.source_link > "", true, false),
                "link",       t.source_link
            )'],

            'comment'           =>  ['raw' => 'JSON_OBJECT(
                "de", IFNULL(t.comment_public, t.comment_public_en),
                "en", IFNULL(t.comment_public_en, t.comment_public)
            )'],

            'pecularities'      =>  ['raw' => 'JSON_OBJECT(
                "de", t.pecularities_de,
                "en", t.pecularities_en
            )'],

            'coins'             =>  ['raw' => '(SELECT JSON_ARRAYAGG( JSON_OBJECT(
                    "self",       CONCAT("'.env('APP_API').'/coins/", ct.id_coin),
                    "id",         ct.id_type
                ))
                FROM '.config('dbi.tablenames.coins_to_types').' AS ct
                WHERE ct.id_type = t.id
            )'],

            // Features
            'mint'              =>  ['raw' => 'JSON_OBJECT(
                "id",           mint.id,
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
            )'], // IFNULL(( SELECT n.mint_name FROM cn_data.nomisma_mints_text AS n WHERE n.nomisma_id_mint = mint.nomisma_id && n.language = "en"), mint.mint)

            /*'issuer'            =>  ['raw' => 'JSON_OBJECT(
                "id",           t.id_issuer,
                "name",         ip.person,
                "alias",        ip.alias,
                "role",         ip.position,
                "link",         IF(ip.nomisma_id_person > "", CONCAT("'.config('dbi.url.nomisma').'", ip.nomisma_id_person), null)
            )'],*/

            'authority'         =>  ['raw' => 'JSON_OBJECT(
                "kind",         JSON_OBJECT(
                    "id",       t.id_authority,
                    "link",     IF( a.nomisma_id > "", CONCAT("'.config('dbi.url.nomisma').'", a.nomisma_id), null),
                    "text",     JSON_OBJECT(
                        "de",   a.authority_de,
                        "en",   a.authority_en
                )),
                "person",       IF( ap.id > "", JSON_OBJECT(
                    "id",       ap.id,
                    "name",     ap.person,
                    "alias",    ap.alias,
                    "role",     ap.position,
                    "link",     IF(ap.nomisma_id_person > "", CONCAT("'.config('dbi.url.nomisma').'", ap.nomisma_id_person), null)
                ), null),
                "group",        IF(at.id > "", JSON_OBJECT(
                    "id",       at.id,
                    "name",     at.tribe_de,
                    "link",     IF(at.nomisma_id > "", CONCAT("'.config('dbi.url.nomisma').'", at.nomisma_id), null)
                ), null)
            )'],

            'findspots'          =>  ['raw' => '(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                    "id",     f.id,
                    "name",   f.name,
                    "link",   IF(f.link > "", CONCAT("'.config('dbi.url.geonames').'", f.link), null),
                    "country",f.country
                )) FROM (
                    SELECT
                        ttf.id_type     AS id_type,
                        f.id            AS id,
                        f.geonames_id   AS link,
                        f.findspot      AS name,
                        f.country       AS country
                    FROM '.config('dbi.tablenames.types_to_findspots').' AS ttf
                    LEFT JOIN '.config('dbi.tablenames.findspots').'     AS f    ON f.id = ttf.id_findspot
                    UNION DISTINCT
                    SELECT
                        ctt.id_type     AS id_type,
                        f.id            AS id,
                        f.geonames_id   AS link,
                        f.findspot      AS name,
                        f.country       AS country
                    FROM '.config('dbi.tablenames.coins_to_types').'    AS ctt
                    LEFT JOIN '.config('dbi.tablenames.coins').'        AS c    ON c.id = ctt.id_coin
                    LEFT JOIN '.config('dbi.tablenames.findspots').'    AS f    ON f.id = c.id_findspot
                ) AS f
                WHERE f.id_type = t.id &&
                f.id > ""
            )'],

            'hoards'             =>  ['raw' => '(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                    "id",       h.id,
                    "name",     h.name,
                    "link",     h.link
                )) FROM (
                    SELECT
                        tth.id_type AS id_type,
                        h.id        AS id,
                        h.link      AS link,
                        h.hoard     AS name
                    FROM '.config('dbi.tablenames.types_to_hoards').' AS tth
                    LEFT JOIN '.config('dbi.tablenames.hoards').'     AS h    ON h.id = tth.id_hoard
                    UNION DISTINCT
                    SELECT
                        ctt.id_type AS id_type,
                        h.id        AS id,
                        h.link      AS link,
                        h.hoard     AS name
                    FROM '.config('dbi.tablenames.coins_to_types').'    AS ctt
                    LEFT JOIN '.config('dbi.tablenames.coins').'        AS c    ON c.id = ctt.id_coin
                    LEFT JOIN '.config('dbi.tablenames.hoards').'       AS h    ON h.id = c.id_hoard
                ) AS h
                WHERE h.id_type = t.id &&
                h.id > ""
            )'],

            'diameter'          =>  ['raw' => '(SELECT JSON_OBJECT(
                    "value_max",  CAST(AVG(c.diameter_max) AS DECIMAL(12,2)),
                    "value_min",  CAST(AVG(c.diameter_min) AS DECIMAL(12,2)),
                    "count",      COUNT(c.id),
                    "unit",       "mm"
                )
                FROM '.config('dbi.tablenames.coins_to_types').'    AS ctt
                LEFT JOIN '.config('dbi.tablenames.coins').'        AS c    ON c.id = ctt.id_coin
                WHERE ctt.id_type = t.id &&
                c.diameter_max IS NOT NULL &&
                c.diameter_max > 0 &&
                c.diameter_ignore = 0
            )'],

            'weight'            =>  ['raw' => '(SELECT JSON_OBJECT(
                    "value",      CAST(AVG(c.weight) AS DECIMAL(12,2)),
                    "count",      COUNT(c.id),
                    "unit",       "g"
                )
                FROM '.config('dbi.tablenames.coins_to_types').'    AS ctt
                LEFT JOIN '.config('dbi.tablenames.coins').'        AS c    ON c.id = ctt.id_coin
                WHERE ctt.id_type = t.id &&
                c.weight IS NOT NULL &&
                c.weight > 0 &&
                c.weight_ignore = 0
            )'],

            'axes'              =>  [/*'raw' => '(SELECT JSON_ARRAYAGG(
                    analyzed.result
                )
                FROM(
                    SELECT JSON_OBJECT(
                        "value", c.axis,
                        "counted", COUNT(c.id)
                    ) AS result
                    FROM cn_data.data_coins_to_types AS ctt
                    LEFT JOIN cn_data.data_coins AS c ON c.id  = ctt.id_coin
                    WHERE id_type = t.id && c.axis IS NOT NULL
                    GROUP BY c.axis
                ) AS analyzed)
            '*/], // needs separate selution - look in next block

            'material'          =>  ['raw' => 'JSON_OBJECT(
                "id",         mat.id,
                "link",       IF(mat.nomisma_id > "", CONCAT("'.config('dbi.url.nomisma').'", mat.nomisma_id), null),
                "text",       JSON_OBJECT(
                    "en", mat.material_en,
                    "de", mat.material_de
            ))'],

            'denomination'      =>  ['raw' => 'JSON_OBJECT(
                "id",         de.id,
                "link",       IF(de.nomisma_id > "", CONCAT("'.config('dbi.url.nomisma').'", de.nomisma_id), null),
                "text",       JSON_OBJECT(
                    "en", de.denomination_en,
                    "de", de.denomination_de
            ))'],

            'standard'          =>  ['raw' => 'JSON_OBJECT(
                "id",         s.id,
                "link",       IF(s.nomisma_id > "", CONCAT("'.config('dbi.url.nomisma').'", s.nomisma_id), null),
                "text",       JSON_OBJECT(
                    "en", s.standard_en,
                    "de", s.Standard_de
            ))'],

            'date'              =>  ['raw' => 'JSON_OBJECT(
                "text",         JSON_OBJECT(
                    "en", null,
                    "de", t.date_string
                ),
                "period",       JSON_OBJECT(
                    "id",           e.id,
                    "link",         IF(e.nomisma_id > "", CONCAT("'.config('dbi.url.nomisma').'", e.nomisma_id), null),
                    "value_min",    e.date_from,
                    "value_max",    e.date_to,
                    "text",     JSON_OBJECT(
                        "en",       e.period_en,
                        "de",       e.period_de
            )))']
        ];

        // Axes
        for ($i = 1; $i <= 13; $i++) {
            $axis[] = '(
                SELECT JSON_OBJECT(
                    "value", '.$i.',
                    "count", COUNT(c.id)
                )
                FROM '.config('dbi.tablenames.coins_to_types').'    AS ctt
                LEFT JOIN '.config('dbi.tablenames.coins').'        AS c ON c.id = ctt.id_coin
                WHERE ctt.id_type = t.id && c.axis = '.$i.'
                GROUP BY c.axis
            )';
        }
        $select['axes'] = ['raw' => 'JSON_ARRAY('.implode(',', $axis).')'];

        // -----------------------------------------------------------------------
        // Obverse / Reverse
        foreach (['o', 'r'] AS $side) {
            $select[$side === 'o' ? 'obverse' : 'reverse'] = ['raw' => 'JSON_OBJECT(
                "design",     JSON_OBJECT(
                    "id",     d'.$side.'.id,
                    "text",   JSON_OBJECT(
                        "en", d'.$side.'.design_en,
                        "de", d'.$side.'.design_de
                )),

                "legend", (SELECT JSON_OBJECT(
                        "id",           l.id,
                        "string",       l.legend,
                        "direction",    IF( l.id_legend_direction IS NOT NULL, JSON_OBJECT(
                            "id",       l.id_legend_direction,
                            "link",     CONCAT( "'.config('dbi.url.storage').'", "legend-directions/", LPAD(l.id_legend_direction, 3, 0), ".svg") ), null)
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
        }


        // ---------------------------------------------------------------------------
        // Persons
        $select['persons'] = ['raw' => '(SELECT JSON_ARRAYAGG(JSON_OBJECT(
            "id",           p.id,
            "name",         p.person,
            "alias",        p.alias,
            "role",         p.position,
            "link",         IF(p.nomisma_id_person > "", CONCAT("'.config('dbi.url.nomisma').'", p.nomisma_id_person), null),
            "function",     JSON_OBJECT(
                "id",       pf.id,
                "text",     JSON_OBJECT(
                    "de",   pf.person_function_de,
                    "en",   pf.person_function_en,
                    "el",   pf.person_function_el
            ))))
            FROM '.config('dbi.tablenames.types_to_persons').'          AS ttp
            LEFT JOIN '.config('dbi.tablenames.persons').'              AS p    ON p.id = ttp.id_person
            LEFT JOIN '.config('dbi.tablenames.persons_functions').'    AS pf   ON pf.id = ttp.id_function
            WHERE ttp.id_type = t.id
        )'];

        // Variations
        $select['variations'] = ['raw' => '( SELECT JSON_ARRAYAGG( JSON_OBJECT(
                "text",   JSON_OBJECT(
                    "de", v.description_de,
                    "en", v.description_en ),
                "comment", v.comment
            )) FROM '.config('dbi.tablenames.types_variations').' AS v
            WHERE v.id_type = t.id
        )'];


        // ---------------------------------------------------------------------------------------------------------------------
        // References
        foreach (['citations','literature'] AS $section) {
            $select[$section] = ['raw' => '(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                "id",         zi.zotero_id,
                "link",       CONCAT("'.config('dbi.url.zotero').'", zi.zotero_id),
                "title",      CONCAT(
                    zi.author, ", ",
                    zi.title,
                    IF( zi.volume > "", CONCAT(" ", zi.volume), ""),
                    IF( zi.place > "" || zi.year_published > "", ",", ""),
                    IF( zi.place > "", CONCAT(" ", zi.place), ""),
                    IF( zi.year_published > "", CONCAT(" ", zi.year_published), "")
                ),
                "quote",      JSON_OBJECT(
                    "page",       ttz.page,
                    "number",     ttz.number,
                    "plate",      ttz.plate,
                    "picture",    ttz.picture,
                    "annotation", ttz.annotation,
                    "comment",    JSON_OBJECT(
                        "de",     ttz.comment_de,
                        "en",     ttz.comment_en
                    ),
                    "text", JSON_OBJECT(
                        "en", CONCAT(
                            IF( ttz.page > "", CONCAT("p. ", ttz.page), ""),
                            IF( ttz.number > "", CONCAT( IF( ttz.page > "", ", ", ""), "nr. ", ttz.number), ""),
                            IF( ttz.plate > "", CONCAT( IF( ttz.page > "" || ttz.number > "", ", ", ""), "pl. ", ttz.plate), ""),
                            IF( ttz.picture > "", CONCAT(", pic. ", ttz.picture), ""),
                            IF( ttz.annotation > "", CONCAT(", annot. ", ttz.annotation), "")
                        ),
                        "de", CONCAT(
                            IF( ttz.page > "", CONCAT("S. ", ttz.page), ""),
                            IF( ttz.number > "", CONCAT( IF( ttz.page > "", ", ", ""), "Nr. ", ttz.number), ""),
                            IF( ttz.plate > "", CONCAT( IF( ttz.page > "" || ttz.number > "", ", ", ""), "Taf. ", ttz.plate), ""),
                            IF( ttz.picture > "", CONCAT(", Abb. ", ttz.picture), ""),
                            IF( ttz.annotation > "", CONCAT(", FN ", ttz.annotation), ""))))
                ))
                FROM '.config('dbi.tablenames.types_to_zotero').'   AS ttz
                LEFT JOIN '.config('dbi.tablenames.zotero').'       AS zi  ON zi.zotero_id = ttz.zotero_id
                WHERE
                ttz.id_type = t.id &&
                ttz.this_type = '.($section === 'citations' ? 1 : 0).' &&
                ttz.zotero_id > ""
            )'];
        }

        // Web References
        $select['web_references'] = ['raw' => '(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                "id",         wl.id,
                "link",       wl.link,
                "semantics",  IFNULL(wl.semantics, "skos:exactMatch"),
                "comment",    JSON_OBJECT(
                    "de",     wl.comment_de,
                    "en",     wl.comment_en
                )
            ))
            FROM '.config('dbi.tablenames.types_to_links').' AS wl
            WHERE wl.id_type = t.id
        )'];


        // ---------------------------------------------------------------------------------------------------------------------
        // Images
        $select['images'] = ['raw' => 'IF(t.id_imageset > "", (SELECT JSON_ARRAYAGG(JSON_OBJECT(
            "id",         img.ImageID,
            "kind",       IFNULL(img.ObjectType, "unknown"),
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
            WHERE img.ImageID = t.id_imageset'.($user['level'] > 9 ? '' : '&& img.is_private = 0') .'),
            null
        )'];


        // ---------------------------------------------------------------------------------------------------------------------
        // DBI (internal Data which will not be provided by API)
        if($user['level'] > 9) {
            $select['dbi'] = ['raw' => 'JSON_OBJECT(
                "public",           IFNULL(t.publication_state, 0),
                "comment",          t.comment_private,
                "description",      t.description_private,
                "name",             t.name_private,

                "creator",          t.id_creator,
                "creator_name",     IFNULL((SELECT
                    u.name
                    FROM '.config('dbi.tablenames.users').' AS u
                    WHERE u.id = t.id_creator), t.id_creator
                ),
                "editor",           t.id_editor,
                "editor_name",      IFNULL((SELECT
                    u.name
                    FROM '.config('dbi.tablenames.users').' AS u
                    WHERE u.id = t.id_editor), t.id_editor
                ),

                "date_start",       t.date_start,
                "date_end",         t.date_end,

                "groups", (SELECT JSON_ARRAYAGG(JSON_OBJECT(
                        "id",         og.id,
                        "name",       og.objectgroup,
                        "text",       JSON_OBJECT(
                            "de", og.description_de,
                            "en", og.description_en
                    )))
                    FROM '.config('dbi.tablenames.types_to_objectgroups').'  AS tto
                    LEFT JOIN '.config('dbi.tablenames.objectgroups').'      AS og ON og.id = tto.id_objectgroup
                    WHERE tto.id_type = t.id
                ),

                "submitted",      ( SELECT JSON_OBJECT(
                    "info", JSON_OBJECT(
                        "import",   ts.import,
                        "date",     ts.created_at),

                    "description", JSON_OBJECT(
                        "de",       ts.description_de,
                        "en",       ts.description_en),

                    "obverse", JSON_OBJECT(
                        "legend", JSON_OBJECT(
                            "string",       ts.legend_o_string,
                            "description",  ts.legend_o_description),
                        "design",   ts.design_o),

                    "reverse", JSON_OBJECT(
                        "legend", JSON_OBJECT(
                            "string",       ts.legend_r_string,
                            "description",  ts.legend_r_description),
                        "design",   ts.design_r),

                    "references", JSON_OBJECT(
                        "citations",    ts.citations,
                        "literature",   ts.literature)
                ) FROM '.config('dbi.tablenames.types_submitted').' AS ts
                WHERE ts.id = t.id),

                "findspots", (SELECT JSON_ARRAYAGG(JSON_OBJECT(
                        "id",     f.id,
                        "name",   f.name,
                        "link",   IF(f.link > "", CONCAT("'.config('dbi.url.geonames').'", f.link), null),
                        "country",f.country
                    )) FROM (
                        SELECT
                            ttf.id_type     AS id_type,
                            f.id            AS id,
                            f.geonames_id   AS link,
                            f.findspot      AS name,
                            f.country       AS country
                        FROM '.config('dbi.tablenames.types_to_findspots').' AS ttf
                        LEFT JOIN '.config('dbi.tablenames.findspots').'     AS f    ON f.id = ttf.id_findspot
                    ) AS f
                    WHERE f.id_type = t.id &&
                    f.id > ""
                ),

                "hoards", (SELECT JSON_ARRAYAGG(JSON_OBJECT(
                        "id",       h.id,
                        "name",     h.name,
                        "link",     h.link
                    )) FROM (
                        SELECT
                            tth.id_type AS id_type,
                            h.id        AS id,
                            h.link      AS link,
                            h.hoard     AS name
                        FROM '.config('dbi.tablenames.types_to_hoards').' AS tth
                        LEFT JOIN '.config('dbi.tablenames.hoards').'     AS h    ON h.id = tth.id_hoard
                    ) AS h
                    WHERE h.id_type = t.id &&
                    h.id > ""
                )
            )'];
        }
        else {
            $select['published'] = 't.publication_state';
        }

        return $select;
    }
}

/*"legend",         JSON_OBJECT(
    "id",         lo.id,
    "string",     lo.legend,
    "direction",  JSON_OBJECT(
        "id",     lod.id,
        "link",   IF( lod.image > "", CONCAT( "'.config('dbi.url.storage').'", "Legenddirections/", lod.image ), null ))),*/
