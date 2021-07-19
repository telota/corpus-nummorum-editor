<?php

namespace App\Http\Controllers\dbi\entities\coins;

use DB;


class request_id_select {

    public function instructions ($user) {

        $select = [
            'id'                =>  'c.id',
            'kind'              =>  ['raw' => '"Coin"'],
            'name'              =>  ['raw' => 'CONCAT_WS(" ", "cn", "coin", c.id)'],
            'self'              =>  ['raw' => 'CONCAT("'.env('APP_API').'/coins/", c.id)'],
            'created_at'        =>  'c.created_at',
            'updated_at'        =>  'c.updated_at',
            'downloaded_at'     =>  ['raw' => 'NOW()'],

            'source'            =>  ['raw' => 'JSON_OBJECT(
                "created_at", IF(c.source_link > "", c.created_at, null),
                "imported",   IF(c.source_link > "", true, false),
                "link",       c.source_link
            )'],

            'comment'           =>  ['raw' => 'JSON_OBJECT(
                "de", IFNULL(c.comment_public, c.comment_public_en),
                "en", IFNULL(c.comment_public_en, c.comment_public)
            )'],

            'type_comment'      =>  ['raw' => '(SELECT JSON_OBJECT(
                    "de", GROUP_CONCAT(IFNULL(t.comment_public, t.comment_public_en) SEPARATOR "; "),
                    "en", GROUP_CONCAT(IFNULL(t.comment_public_en, t.comment_public) SEPARATOR "; ")
                )
                FROM '.config('dbi.tablenames.coins_to_types').'    AS ct
                LEFT JOIN '.config('dbi.tablenames.types').'        AS t on t.id = ct.id_type
                WHERE ct.id_coin = c.id
            )'],

            'pecularities'      =>  ['raw' => 'JSON_OBJECT(
                "de", c.pecularities_de,
                "en", c.pecularities_en
            )'],

            'types'             =>  ['raw' => '(SELECT JSON_ARRAYAGG( JSON_OBJECT(
                    "self",       CONCAT("'.config('dbi.url.api').'types/", ct.id_type),
                    "id",         ct.id_type,
                    "inherited",  IF(cti.id IS NOT NULL, 1, 0)
                ))
                FROM '.config('dbi.tablenames.coins_to_types').'        AS ct
                LEFT JOIN '.config('dbi.tablenames.coins_inherit').'    AS cti on cti.id = ct.id_coin
                WHERE ct.id_coin = c.id
            )'],

            'is_forgery'        =>  'c.is_forgery',

            'owner'             =>  ['raw' => 'JSON_OBJECT(
                "provenience",        c.provenience,
                "original",           JSON_OBJECT(
                    "is_unsure",      c.owner_is_unsure,
                    "id",             oo.id,
                    "country",        oo.country,
                    "city",           oo.city,
                    "link",           '.($user['level'] > 9 ? 'oo.link'            : 'IF(oo.is_name_public = 1, oo.link, null)').',
                    "name",           '.($user['level'] > 9 ? 'oo.owner'           : 'IF(oo.is_name_public = 1, oo.owner, null)').',
                    "collection_nr",  '.($user['level'] > 9 ? 'c.collection_id'    : 'IF(oo.is_name_public = 1, c.collection_id, null)').'
                ),
                "reproduction",       JSON_OBJECT(
                    "id",             or.id,
                    "country",        or.country,
                    "city",           or.city,
                    "link",           '.($user['level'] > 9 ? 'or.link'            : 'IF(or.is_name_public = 1, or.link, null)').',
                    "name",           '.($user['level'] > 9 ? 'or.owner'           : 'IF(or.is_name_public = 1, or.owner, null)').',
                    "collection_nr",  '.($user['level'] > 9 ? 'c.plastercast_id'   : 'IF(or.is_name_public = 1, c.plastercast_id, null)').'
                )
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
                "id",           c.id_issuer,
                "name",         ip.person,
                "alias",        ip.alias,
                "role",         ip.position,
                "link",         IF(ip.nomisma_id_person > "", CONCAT("'.config('dbi.url.nomisma').'", ip.nomisma_id_person), null)
            )'],*/

            'authority'         =>  ['raw' => 'JSON_OBJECT(
                "kind",         JSON_OBJECT(
                    "id",       c.id_authority,
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

            'findspot'          =>  ['raw' => 'JSON_OBJECT(
                "id",           f.id,
                "name",         f.findspot,
                "link",         IF(f.geonames_id > "", CONCAT("'.config('dbi.url.geonames').'", f.geonames_id), null),
                "country",      f.country
            )'],

            'hoard'             =>  ['raw' => 'JSON_OBJECT(
                "id",           h.id,
                "name",         h.hoard,
                "link",         IF(h.link > "", h.link, null)
            )'],

            'diameter'          =>  ['raw' => 'JSON_OBJECT(
                "value_min",    IF(c.diameter_min > "", c.diameter_min, null),
                "value_max",    IF(c.diameter_max > "", c.diameter_max, null),
                "unit",         "mm",
                "ignore",       IF( c.diameter_ignore = 1, 1, 0)
            )'],

            'weight'            =>  ['raw' => 'JSON_OBJECT(
                "value",        IF(c.weight > "", c.weight, null),
                "unit",         "g",
                "ignore",       IF(c.weight_ignore = 1, 1, 0)
            )'],

            'axis'              =>  ['raw' => 'IF(c.axis > 12, null, c.axis)'],
            'centerhole'        =>  ['raw' => 'JSON_OBJECT(
                "value",        IFNULL(c.has_centerhole, 0),
                "string",       CASE
                        WHEN c.has_centerhole = 1 THEN "obverse"
                        WHEN c.has_centerhole = 2 THEN "reverse"
                        WHEN c.has_centerhole = 3 THEN "obverse / reverse"
                        ELSE "none"
                    END
            )'],

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
                "link",       IF( s.nomisma_id > "", CONCAT("'.config('dbi.url.nomisma').'", s.nomisma_id), null),
                "text",       JSON_OBJECT(
                    "en", s.standard_en,
                    "de", s.Standard_de
            ))'],

            'date'              =>  ['raw' => 'JSON_OBJECT(
                "text",         JSON_OBJECT(
                    "en", null,
                    "de", c.date_string
                ),
                "period",       JSON_OBJECT(
                    "id",       e.id,
                    "link",     IF(e.nomisma_id > "", CONCAT("'.config('dbi.url.nomisma').'", e.nomisma_id), null),
                    "value_min",    e.date_from,
                    "value_max",    e.date_to,
                    "text",         JSON_OBJECT(
                        "en",   e.period_en,
                        "de",   e.period_de
            )))']
        ];


        // -----------------------------------------------------------------------
        // Obverse / Reverse
        foreach (['o', 'r'] AS $side) {
            $select[$side === 'o' ? 'obverse' : 'reverse'] = ['raw' => 'JSON_OBJECT(
                "design",     IF(c.id_design_'.$side.' IS NULL && c.id_die_'.$side.' IS NOT NULL,
                    (SELECT JSON_OBJECT(
                        "id",     dd.id,
                        "text",   JSON_OBJECT(
                            "en", dd.design_en,
                            "de", dd.design_de
                    ))
                    FROM '.config('dbi.tablenames.dies').'          AS d
                    LEFT JOIN '.config('dbi.tablenames.designs').'  AS dd   ON dd.id = d.id_design
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
                            "id",           l.id,
                            "string",       l.legend,
                            "direction",    IF( l.id_legend_direction IS NOT NULL, JSON_OBJECT(
                                "id",       l.id_legend_direction,
                                "link",     CONCAT( "'.config('dbi.url.storage').'", "legend-directions/", LPAD(l.id_legend_direction, 3, 0), ".svg") ), null)
                        )
                        FROM '.config('dbi.tablenames.dies').'          AS d
                        LEFT JOIN '.config('dbi.tablenames.legends').'  AS l   ON l.id = d.id_legend
                        WHERE d.id = c.id_die_'.$side.'
                    ),
                        (SELECT JSON_OBJECT(
                            "id",           l.id,
                            "string",       l.legend,
                            "direction",    IF( l.id_legend_direction IS NOT NULL, JSON_OBJECT(
                                "id",       l.id_legend_direction,
                                "link",     CONCAT( "'.config('dbi.url.storage').'", "legend-directions/", LPAD(l.id_legend_direction, 3, 0), ".svg") ), null)
                        )
                        FROM '.config('dbi.tablenames.legends').' AS l
                        WHERE l.id = c.id_legend_'.$side.'
                    )
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
                ),

                "die", (SELECT JSON_OBJECT(
                        "id", d.id,
                        "nr", d.name_die
                    )
                    FROM '.config('dbi.tablenames.dies').' AS d
                    WHERE d.id = c.id_die_'.$side.'),

                "controlmarks", (SELECT JSON_ARRAYAGG(JSON_OBJECT(
                        "id",     cm.id,
                        "name",   cm.controlmark,
                        "link",   IF( cm.image > "", CONCAT( "'.config('dbi.url.storage').'", "Kontrollzeichen/", cm.image ), null ),
                        "count",  IF( ctc.number > "", CAST( ctc.number AS UNSIGNED ), 1)
                    ))
                    FROM '.config('dbi.tablenames.coins_to_controlmarks').' AS ctc
                    LEFT JOIN '.config('dbi.tablenames.controlmarks').'     AS cm   ON cm.id = ctc.id_controlmark
                    WHERE ctc.id_coin = c.id && ctc.side = '.($side === 'o' ? 0 : 1).'
                ),

                "countermark",    JSON_OBJECT(
                    "text",       JSON_OBJECT(
                        "de",   c.countermark_'.$side.'_de,
                        "en",   c.countermark_'.$side.'_en
                )),
                "undertype",      JSON_OBJECT(
                    "text",       JSON_OBJECT(
                        "de",   c.undertype_'.$side.'_de,
                        "en",   c.undertype_'.$side.'_en
                ))
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
            FROM '.config('dbi.tablenames.coins_to_persons').'          AS ctp
            LEFT JOIN '.config('dbi.tablenames.persons').'              AS p    ON p.id = ctp.id_person
            LEFT JOIN '.config('dbi.tablenames.persons_functions').'    AS pf   ON pf.id = ctp.id_function
            WHERE ctp.id_coin = c.id
        )'];


        // ---------------------------------------------------------------------------------------------------------------------
        // References
        foreach ([
            'citations',
            'literature',
            'type_literature'
        ] AS $section) {
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
                    "page",       ctz.page,
                    "number",     ctz.number,
                    "plate",      ctz.plate,
                    "picture",    ctz.picture,
                    "annotation", ctz.annotation,
                    "comment",    JSON_OBJECT(
                        "de",     ctz.comment_de,
                        "en",     ctz.comment_en
                    ),
                    "text", JSON_OBJECT(
                        "en", CONCAT(
                            IF( ctz.page > "", CONCAT("p. ", ctz.page), ""),
                            IF( ctz.number > "", CONCAT( IF( ctz.page > "", ", ", ""), "nr. ", ctz.number), ""),
                            IF( ctz.plate > "", CONCAT( IF( ctz.page > "" || ctz.number > "", ", ", ""), "pl. ", ctz.plate), ""),
                            IF( ctz.picture > "", CONCAT(", pic. ", ctz.picture), ""),
                            IF( ctz.annotation > "", CONCAT(", annot. ", ctz.annotation), "")
                        ),
                        "de", CONCAT(
                            IF( ctz.page > "", CONCAT("S. ", ctz.page), ""),
                            IF( ctz.number > "", CONCAT( IF( ctz.page > "", ", ", ""), "Nr. ", ctz.number), ""),
                            IF( ctz.plate > "", CONCAT( IF( ctz.page > "" || ctz.number > "", ", ", ""), "Taf. ", ctz.plate), ""),
                            IF( ctz.picture > "", CONCAT(", Abb. ", ctz.picture), ""),
                            IF( ctz.annotation > "", CONCAT(", FN ", ctz.annotation), ""))))
                ))'];

            // Citations || Literature
            if ($section != 'type_literature') {
                $select[$section]['raw'] .= '
                    FROM '.config('dbi.tablenames.coins_to_zotero').'   AS ctz
                    LEFT JOIN '.config('dbi.tablenames.zotero').'       AS zi  ON zi.zotero_id = ctz.zotero_id
                    WHERE
                    ctz.id_coin = c.id &&
                    ctz.this_coin = '.($section === 'citations' ? 1 : 0).' &&
                    ctz.zotero_id > ""
                )';
            }

            // Type Literature
            else {
                $select[$section]['raw'] .= '
                    FROM '.config('dbi.tablenames.coins_to_types').'        AS ctt
                    LEFT JOIN '.config('dbi.tablenames.types_to_zotero').'  AS ctz ON ctz.id_type = ctt.id_type
                    LEFT JOIN '.config('dbi.tablenames.zotero').'           AS zi  ON zi.zotero_id = ctz.zotero_id
                    WHERE
                    ctt.id_coin = c.id &&
                    ctz.zotero_id > ""
                )';
            }
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
            FROM '.config('dbi.tablenames.coins_to_links').' AS wl
            WHERE wl.id_coin = c.id
        )'];


        // ---------------------------------------------------------------------------------------------------------------------
        // Images
        $select['images'] = ['raw' => '( SELECT JSON_ARRAYAGG( JSON_OBJECT(
            "id",           img.ImageID,
            "kind",           IFNULL(img.ObjectType, "unknown"),
            "obverse",        JSON_OBJECT(
                "link",       IF( img.ObverseImageFilename > "", CONCAT( IF( img.Path > "", IF( SUBSTRING( img.Path, -1, 1 ) != "/", CONCAT( img.Path, "/" ), img.Path ),""), img.ObverseImageFilename ), null),
                "kind",       IF( img.ObverseImageFilename > "", img.ObjectType, null),
                "bg_color",   IF( img.BackgroundColor > "" && img.ObverseImageFilename > "", img.BackgroundColor, null),
                "photographer", IF( img.ObversePhotographer > "" , img.ObversePhotographer, null)
            ),
            "reverse",      JSON_OBJECT(
                "link",       IF( img.ReverseImageFilename > "", CONCAT( IF( img.Path > "", IF( SUBSTRING( img.Path, -1, 1 ) != "/", CONCAT( img.Path, "/"), img.Path ),""), img.ReverseImageFilename ), null),
                "kind",       IF( img.ReverseImageFilename > "", img.ObjectType, null),
                "bg_color",   IF( img.BackgroundColor > "" && img.ReverseImageFilename > "", img.BackgroundColor, null),
                "photographer", IF( img.ReversePhotographer > "" , img.ReversePhotographer, null)
            )))
            FROM '.config('dbi.tablenames.images').' AS img
            WHERE img.CoinID = c.id &&
            1=1
            ORDER BY img.ObjectType DESC
        )'];


        // ---------------------------------------------------------------------------------------------------------------------
        // DBI (internal Data which will not be provided by API)
        if($user['level'] > 9) {
            $select['dbi'] = ['raw' => 'JSON_OBJECT(
                "public",         IFNULL(c.publication_state, 0),
                "comment",        IF( c.comment_private > "", c.comment_private, null),
                "type_comment", (SELECT GROUP_CONCAT(t.comment_private SEPARATOR "; ")
                    FROM '.config('dbi.tablenames.coins_to_types').'    AS ct
                    LEFT JOIN '.config('dbi.tablenames.types').'        AS t on t.id = ct.id_type
                    WHERE ct.id_coin = c.id
                ),
                "description",    IF( c.description_private > "", c.description_private, null),

                "creator",        c.id_creator,
                "creator_name",   IFNULL((SELECT
                    u.name
                    FROM '.config('dbi.tablenames.users').' AS u
                    WHERE u.id = c.id_creator), c.id_creator
                ),
                "editor",         c.id_editor,
                "editor_name",    IFNULL((SELECT
                    u.name
                    FROM '.config('dbi.tablenames.users').' AS u
                    WHERE u.id = c.id_editor), c.id_editor
                ),

                "date_start",     c.date_start,
                "date_end",       c.date_end,

                "groups", (SELECT JSON_ARRAYAGG(JSON_OBJECT(
                        "id",         og.id,
                        "name",       og.objectgroup,
                        "text",       JSON_OBJECT(
                            "de", og.description_de,
                            "en", og.description_en
                    )))
                    FROM '.config('dbi.tablenames.coins_to_objectgroups').'  AS cto
                    LEFT JOIN '.config('dbi.tablenames.objectgroups').'      AS og ON og.id = cto.id_objectgroup
                    WHERE cto.id_coin = c.id
                ),

                "inherited",      (SELECT JSON_OBJECT(
                    "id_type",            cti.id_type,

                    "mint",               cti.mint_inherited,
                    "issuer",             cti.issuer_inherited,
                    "authority",          cti.authority_inherited,
                    "authority_person",   cti.authority_person_inherited,
                    "authority_group",    cti.authority_group_inherited,
                    "date",               cti.date_inherited,
                    "period",             cti.period_inherited,

                    "material",           cti.material_inherited,
                    "denomination",       cti.denomination_inherited,
                    "standard",           cti.standard_inherited,

                    "o_design",           cti.design_o_inherited,
                    "o_legend",           cti.legend_o_inherited,
                    "o_monograms",        cti.monogram_o_inherited,
                    "o_symbols",          cti.symbol_o_inherited,

                    "r_design",           cti.design_r_inherited,
                    "r_legend",           cti.legend_r_inherited,
                    "r_monograms",        cti.monogram_r_inherited,
                    "r_symbols",          cti.symbol_r_inherited,

                    "persons",            cti.person_inherit,

                    "comment_private",    cti.comment_private_inherited,
                    "comment_public",     cti.comment_public_inherited
                )
                FROM '.config('dbi.tablenames.coins_inherit').' AS cti
                WHERE cti.id = c.id),

                "submitted",      ( SELECT JSON_OBJECT(
                    "info", JSON_OBJECT(
                        "import", cs.import,
                        "date", cs.created_at),

                    "description", JSON_OBJECT(
                        "de", cs.description_de,
                        "en", cs.description_en),

                    "obverse", JSON_OBJECT(
                        "legend", JSON_OBJECT(
                            "string", cs.legend_o_string,
                            "description", cs.legend_o_description),
                        "design", cs.design_o),

                    "reverse", JSON_OBJECT(
                        "legend", JSON_OBJECT(
                            "string", cs.legend_r_string,
                            "description", cs.legend_r_description),
                        "design", cs.design_r),

                    "references", JSON_OBJECT(
                        "citations", cs.citations,
                        "literature", cs.literature)
                ) FROM '.config('dbi.tablenames.coins_submitted').' AS cs
                WHERE cs.id = c.id),

                "images", ( SELECT JSON_ARRAYAGG( JSON_OBJECT(
                    "id",             img.ImageID,
                    "kind",           IFNULL(img.ObjectType, "unknown"),
                    "obverse",        JSON_OBJECT(
                        "link",       IF( img.ObverseImageFilename > "", CONCAT( IF( img.Path > "", IF( SUBSTRING( img.Path, -1, 1 ) != "/", CONCAT( img.Path, "/" ), img.Path ),""), img.ObverseImageFilename ), null),
                        "kind",       IF( img.ObverseImageFilename > "", img.ObjectType, null),
                        "bg_color",   IF( img.BackgroundColor > "" && img.ObverseImageFilename > "", img.BackgroundColor, null),
                        "photographer", IF( img.ObversePhotographer > "" , img.ObversePhotographer, null),
                        "public",     1,
                        "copyright",  1
                    ),
                    "reverse",        JSON_OBJECT(
                        "link",       IF( img.ReverseImageFilename > "", CONCAT( IF( img.Path > "", IF( SUBSTRING( img.Path, -1, 1 ) != "/", CONCAT( img.Path, "/"), img.Path ),""), img.ReverseImageFilename ), null),
                        "kind",       IF( img.ReverseImageFilename > "", img.ObjectType, null),
                        "bg_color",   IF( img.BackgroundColor > "" && img.ReverseImageFilename > "", img.BackgroundColor, null),
                        "photographer", IF( img.ReversePhotographer > "" , img.ReversePhotographer, null),
                        "public",     1,
                        "copyright",  1
                    ))) FROM '.config('dbi.tablenames.images').' AS img
                    WHERE img.CoinID = c.id
                    ORDER BY img.ObjectType DESC
                )
            )'];
        }
        else {
            $select['published'] = 'c.publication_state';
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
