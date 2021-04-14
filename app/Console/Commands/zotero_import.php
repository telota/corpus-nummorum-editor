<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class zotero_import extends Command {
    
    protected $signature = 'zotero:import {full_fetch?}';
    protected $description = 'Import Zotero References';    

    public function __construct(){
        parent::__construct();
    }

    // -------------------------------------------------------------------------------------

    static $zotero_api      = 'https://api.zotero.org/groups/163139';
    static $version         = '?v=3';

    // -------------------------------------------------------------------------------------

    public function handle() {
        // date_default_timezone_set('Europe/Berlin');
        // $date = date('Y-m-d H:i:s');
        $time = date('U');
        
        echo "\n------------------------\n"."Import Zotero References"."\n------------------------\n\n";

        $zotero_group = self::$zotero_api.self::$version;
        $zotero_items = self::$zotero_api.'/items'.self::$version.'&includeTrashed=1'; // add trashed to get all items in Library

        // Partial Fetch is requested
        if(empty($this->argument('full_fetch'))) {
            echo 'Partial Fetch requested'."\n\n";

            // Get latest Update from Import Table 
            echo 'Get date of latest Zotero Reference Update ... ';
            $latest_update = DB::table(config('dbi.tablenames.zotero')) -> select(DB::Raw('MAX(updated_at) as u')) -> get();
            $latest_update = substr(json_decode($latest_update, true)[0]['u'], 0, 10);
            echo $latest_update."\n";

            // Set URL to get items sorted by last Update descending
            $zotero_items .= '&sort=dateModified&direction=desc';
            $start = 0;
            $limit = 10;
            $run = true;

            // Start Loop, break if all new items are processed
            while($run === true) {
                $items = self::fetch($zotero_items.'&start='.$start.'&limit='.$limit);

                foreach ($items as $i => $item) {
                    $modified = self::parseDate($item['data']['dateModified']);
                    // Break if item is older than latest update in DB
                    if ($modified < $latest_update) {
                        echo "\n".'All modified Items processed, Loop stopped'."\n";
                        $run = false;
                        break;
                    }
                    // Process Item
                    echo "\t".($start + $i + 1);
                    self::extractData($item);
                }
                $start += $limit;
            }
        }

        // Full Fetch is requested
        else {
            echo 'Full Fetch requested'."\n\n";

            // Get total number of items in group (including Trash!)
            $group = self::fetch($zotero_group);
            $total = empty($group['meta']['numItems']) ? 0 : $group['meta']['numItems'];
            echo $total.' items available'."\n";
    
            // Check if any items are available
            if ($total < 1) {
                echo 'ERROR: No Items available, aborting Script'."\n";
            }
    
            // Get and process items
            else {
                // Set URL to get items sorted by last Update descending
                $zotero_items .= '&sort=dateModified&direction=desc';
                $limit = 100;

                // Start Loop, set total number of items as Maximum
                for ($start = 0; $start < $total; $start += $limit) {
                    $items = self::fetch($zotero_items.'&start='.$start.'&limit='.$limit);

                    // Loop over retrieved items
                    foreach ($items as $i => $item) {
                        echo "\t".($start + $i + 1);
                        self::extractData($item);
                    }
                }
            }
        }

        echo "\nExecution Time: ".(date('U') - $time)." sec\n";
        echo "------------------------\n"." SUCCESSFULLY FINISHED "."\n------------------------\n\n";
    }

    // -------------------------------------------------------------------------------------
    // Helper Functions

    static function fetch ($url) {  
        echo "\n".'Fetching Data from "'.$url.'" ... ';      
        $data = file_get_contents($url);
        $data = json_decode($data, true);

        if (empty($data)) {
            echo 'no Data'."\n";
            return [];
        }
        else {
            echo count($data).' records retrieved'."\n";
            return $data;
        }
    }

    static function parseDate ($raw) {
        // extract YYYY-MM-DD
        preg_match('/[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/', $raw, $date);
        // extract HH:ii:ss
        preg_match('/(2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]/', $raw, $time);
        return empty($date[0]) || empty($time[0]) ? null : ($date[0].' '.$time[0]);
    }

    static function extractData ($item) {
        $data = [
            'zotero_id'         => $item['key'],
            'author'            => empty($item['meta']['creatorSummary'])   ? null : trim($item['meta']['creatorSummary']),
            'year_published'    => empty($item['meta']['parsedDate'])       ? null : trim($item['meta']['parsedDate']),
            'title'             => empty($item['data']['title'])            ? null : trim($item['data']['title']),
            'shorttitle'        => empty($item['data']['shortTitle'])       ? null : trim($item['data']['shortTitle']),
            'series'            => empty($item['data']['series'])           ? null : trim($item['data']['series']),
            'volume'            => empty($item['data']['volume'])           ? null : trim($item['data']['volume']),
            'edition'           => empty($item['data']['edition'])          ? null : trim($item['data']['edition']),
            'place'             => empty($item['data']['place'])            ? null : trim($item['data']['place']),
            'publisher'         => empty($item['data']['publisher'])        ? null : trim($item['data']['publisher']),
            'date'              => empty($item['data']['date'])             ? null : trim($item['data']['date']),
            'is_trash'          => empty($item['data']['deleted'])          ? 0 : 1,
            'created_at'        => self::parseDate($item['data']['dateAdded']),
            'updated_at'        => self::parseDate($item['data']['dateModified'])
        ];

        echo "\t".$data['zotero_id']."\t".$data['updated_at']."\t";

        DB::table(config('dbi.tablenames.zotero')) -> updateOrInsert(['zotero_id' => $data['zotero_id']], $data);

        echo "+\n";
    }
}
