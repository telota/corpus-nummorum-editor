<?php

$db_app  = 'cn_app.';
$db_data = 'cn_data.';

return [
    // Primary Entities
    'coins'                 => $db_data.'data_coins',
    'coins_to_controlmarks' => $db_data.'data_coins_to_controlmarks',
    'coins_inherit'         => $db_data.'data_coins_to_types_inherit',
    'coins_submitted'       => $db_data.'data_coins_submitted',
    'coins_to_links'        => $db_data.'data_coins_to_weblinks',
    'coins_to_monograms'    => $db_data.'data_coins_to_monograms',
    'coins_to_objectgroups' => $db_data.'data_coins_to_objectgroups',
    'coins_to_persons'      => $db_data.'data_coins_to_persons',
    'coins_to_symbols'      => $db_data.'data_coins_to_symbols',
    'coins_to_types'        => $db_data.'data_coins_to_types',
    'coins_to_zotero'       => $db_data.'data_coins_to_zotero',

    'types'                 => $db_data.'data_types',
    'types_submitted'       => $db_data.'data_types_submitted',
    'types_to_findspots'    => $db_data.'data_types_to_findspots',
    'types_to_hoards'       => $db_data.'data_types_to_hoards',
    'types_to_links'        => $db_data.'data_types_to_weblinks',
    'types_to_monograms'    => $db_data.'data_types_to_monograms',
    'types_to_objectgroups' => $db_data.'data_types_to_objectgroups',
    'types_to_persons'      => $db_data.'data_types_to_persons',
    'types_to_symbols'      => $db_data.'data_types_to_symbols',
    'types_to_zotero'       => $db_data.'data_types_to_zotero',
    'types_variations'      => $db_data.'data_type_variations',

    'images'                => $db_data.'thrakien_images',
    'positions'             => $db_data.'data_positions',
    'zotero'                => $db_data.'zotero_import',

    // Secondary Entities
    'authorities'           => $db_data.'data_authorities',
    'bibliography'          => $db_data.'zotero_import',
    'controlmarks'          => $db_data.'data_controlmarks',
    'copyrights'            => $db_data.'data_copyrights',
    'countries'             => $db_data.'data_countries_iso3166',
    'denominations'         => $db_data.'data_denominations',
    'designs'               => $db_data.'data_designs',
    'dies'                  => $db_data.'data_dies',
    'findspots'             => $db_data.'data_findspots',
    'hoards'                => $db_data.'data_hoards',
    'legends'               => $db_data.'data_legends',
    'legends_directions'    => $db_data.'data_legends_directions',
    'materials'             => $db_data.'data_materials',
    'monograms'             => $db_data.'data_monograms',
    'mints'                 => $db_data.'data_mints',
    'mints_nomisma'         => $db_data.'nomisma_mints',
    'mints_nomisma_text'    => $db_data.'nomisma_mints_text',
    'objectgroups'          => $db_data.'data_objectgroups',
    'owners'                => $db_data.'data_owners',
    'periods'               => $db_data.'data_periods',
    'persons'               => $db_data.'data_persons',
    'persons_functions'     => $db_data.'data_persons_functions',
    'photographers'         => $db_data.'data_photographers',
    'positions'             => $db_data.'data_positions',
    'regions'               => $db_data.'data_regions',
    'regions_to_subregions' => $db_data.'nomisma_subregions_to_regions',
    'standards'             => $db_data.'data_standards',
    'symbols'               => $db_data.'data_symbols',
    'tribes'                => $db_data.'data_tribes',
    'lgpn_names'            => $db_data.'lgpn_names',

    // Others
    'brokenlinks'           => $db_app.'app_statistics_coins_error',
    'com'                   => $db_app.'app_coinsofmonth',
    'com_strings'           => $db_app.'app_coinsofmonth_text',
    'news'                  => $db_app.'app_pr_news',
    'news_strings'          => $db_app.'app_pr_news_strings',
    'team'                  => $db_app.'app_website_team',
    'texts'                 => $db_app.'app_website_texts',
    'users'                 => $db_app.'app_editor_users',
    'typology'              => $db_data.'data_typology',
    'typology_text'         => $db_data.'data_typology_text',
    'typology_mints'        => $db_data.'data_typology_to_mints',
    'typology_persons'      => $db_data.'data_typology_to_persons',
    'typology_nomisma'      => $db_data.'data_typology_to_nomisma',

    // Indexes
    'index_coins'           => $db_data.'index_coins',
    'index_types'           => $db_data.'index_types'
];
