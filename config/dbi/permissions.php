<?php

$db_app  = 'cn_app.';
$db_data = 'cn_data.';

return [
    // Primary Entities
    'coins'         => ['read' => 2,    'write_own' => 11,  'write_all' => 12],
    'types'         => ['read' => 2,    'write_own' => 11,  'write_all' => 12],

    // Secondary Entities
    'bibliography'  => ['read' => 2,    'write_own' => 12,  'write_all' => 12],
    'lists'         => ['read' => 2,    'write_own' => 99,  'write_all' => 99],
    'entitylists'   => ['read' => 2,    'write_own' => 99,  'write_all' => 99],

    'denominations' => ['read' => 2,    'write_own' => 11,  'write_all' => 12],
    'designs'       => ['read' => 2,    'write_own' => 11,  'write_all' => 12],
    'dies'          => ['read' => 2,    'write_own' => 11,  'write_all' => 12],
    'findspots'     => ['read' => 2,    'write_own' => 11,  'write_all' => 12],
    'hoards'        => ['read' => 2,    'write_own' => 11,  'write_all' => 12],
    'legends'       => ['read' => 2,    'write_own' => 11,  'write_all' => 12],
    'materials'     => ['read' => 2,    'write_own' => 11,  'write_all' => 12],
    'monograms'     => ['read' => 2,    'write_own' => 11,  'write_all' => 12],
    'mints'         => ['read' => 2,    'write_own' => 11,  'write_all' => 12],
    'objectgroups'  => ['read' => 2,    'write_own' => 11,  'write_all' => 12],
    'owners'        => ['read' => 10,   'write_own' => 11,  'write_all' => 12],
    'periods'       => ['read' => 2,    'write_own' => 11,  'write_all' => 12],
    'persons'       => ['read' => 2,    'write_own' => 11,  'write_all' => 12],
    'regions'       => ['read' => 2,    'write_own' => 99,  'write_all' => 99],
    'standards'     => ['read' => 2,    'write_own' => 11,  'write_all' => 12],
    'symbols'       => ['read' => 2,    'write_own' => 11,  'write_all' => 12],
    'tribes'        => ['read' => 2,    'write_own' => 11,  'write_all' => 12,],

    // Others
    'publish'       => ['read' => 99,   'write_own' => 18,  'write_all' => 18],
    'link'          => ['read' => 99,   'write_own' => 11,  'write_all' => 12],
    'brokenlinks'   => ['read' => 2,    'write_own' => 99,  'write_all' => 99],

    'coinofthemonth'=> ['read' => 2,    'write_own' => 21,  'write_all' => 21],
    'news'          => ['read' => 2,    'write_own' => 21,  'write_all' => 21],
    'team'          => ['read' => 2,    'write_own' => 22,  'write_all' => 22],
    'texts'         => ['read' => 2,    'write_own' => 22,  'write_all' => 22],
    'users'         => ['read' => 31,   'write_own' => 31,  'write_all' => 31],
    'users_list'    => ['read' => 10,   'write_own' => 31,  'write_all' => 31],
];