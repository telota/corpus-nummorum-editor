<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class cron_statistics extends Command {
    protected $signature = 'cron:statistics';
    protected $description = 'Get current statistics';
    public function __construct() { parent::__construct(); }
    // ------------------------------------------------------------------------------

    public function handle() {
        $time = date('U');

        $db_app = 'cn_app.';
        $db_data = 'cn_data.';

        $count = 'SELECT COUNT(id) FROM';
        $date_30 = date('Y-m-d H:i:s', strtotime('-30 days'));
        $regions = [
            'thrace'            => 1,
            'moesia_inferior'   => 2,
            'mysia'             => 3,
            'troas'             => 4
        ];
        $publication_state = [
            'all' => '!= 3',
            'pub' => '= 1'
        ];

        /*echo(
            "\n\n".
            "----------------------------------------------------------\n".
            "--------------------- CN STATISTICS ----------------------\n".
            "----------------------------------------------------------\n\n"
        );*/

        // Create Select All Array
        foreach (['types', 'coins'] AS $entity) {
            // all objects
            foreach ($publication_state AS $key => $val) {
                $select_all[] = '\''.$entity.'_'.$key.'\',  (SELECT COUNT(id) FROM '.$db_data.'data_'.$entity.' WHERE publication_state '.$val.')';
            }
            $select_all[] = '\''.$entity.'_trend\',  (SELECT COUNT(id) FROM '.$db_data.'data_'.$entity.' WHERE publication_state != 3 && created_at > \''.$date_30.'\')';
        }

        // Iterate over regions
        foreach ($regions AS $region => $id) {
            $select_region[$region]= '\''.$region.'\', JSON_OBJECT(';

            foreach (['types', 'coins'] AS $entity) {
                foreach ($publication_state AS $key => $val) {
                    $select[$region][] = '\''.$entity.'_'.$key.'\',  (SELECT COUNT(b.id)
                    FROM '.$db_data.'data_'.$entity.' AS b
                    LEFT JOIN '.$db_data.'data_mints                    AS m    ON m.id = b.id_mint
                    LEFT JOIN '.$db_data.'nomisma_subregions_to_regions AS sr   ON sr.nomisma_id_region = m.imported_nomisma_subregion
                    WHERE b.publication_state '.$val.' && sr.id_region = '.$id.')';
                }
            }

            $select[$region][] = '\'mints\',  (SELECT COUNT(m.id)
            FROM '.$db_data.'data_mints AS m
            LEFT JOIN '.$db_data.'nomisma_subregions_to_regions AS sr   ON sr.nomisma_id_region = m.imported_nomisma_subregion
            WHERE sr.id_region = '.$id.')';

            $select_region[$region] .= implode(",\n", $select[$region]).')';
        }

        // Execute DB Action
        DB::table($db_app.'app_daily_statistics') -> insert(['data' => DB::raw(
            '(SELECT JSON_OBJECT(
                \'all\', JSON_OBJECT(
                    \'team\',           ('.$count.' '.$db_app.'app_editor_users WHERE access_level > 9),
                    \'bibliography\',   ('.$count.' '.$db_data.'zotero_import),
                    \'mints\',          ('.$count.' '.$db_data.'data_mints),
                    '.implode(",\n", $select_all).'
                ),
                \'regions\', JSON_OBJECT('.implode(",\n", $select_region).'),

                \'designs\', (SELECT COUNT(ad.id) FROM '.$db_data.'data_designs AS ad),
                \'legends\', (SELECT COUNT(al.id) FROM '.$db_data.'data_legends AS al),
                \'dies\', (SELECT COUNT(ac.id) FROM '.$db_data.'data_dies AS ac),
                \'monograms\', (SELECT COUNT(am.id) FROM '.$db_data.'data_monograms AS am),
                \'persons\', (SELECT COUNT(ap.id) FROM '.$db_data.'data_persons AS ap)
                )
            )'
        )]);


        echo("SUCCESS!\n\nExecution time: ".(date('U') - $time)." sec\n");

        // Regular End of Script -------------------------------------------------------------------------------------
        die(
            /*"\n\n".
            "----------------------------------------------------------\n".
            "------------------ REGULAR END OF SCRIPT -----------------\n".
            "----------------------------------------------------------\n\n"*/
        );
    }
}
