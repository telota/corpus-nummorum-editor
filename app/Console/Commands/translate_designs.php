<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class translate_designs extends Command {

    protected $signature = 'translate:designs';
    protected $description = 'Use Google to translate Design Strings';

    public function __construct () { parent::__construct(); }

    public function handle() {
        $langSource = 'en';
        $langTarget = 'bg';
        //$file       = '/opt/projects/corpus-nummorum/output/designs_translated.csv';

        $designs = $this->getDesigns();

        if ($designs[0]){
            echo "\nStart Iterating Designs ...\n\n";

            //$file = fopen($file, 'w');
            /*fputcsv($file, [
                'ID',
                'DE',
                'EN',
                'BG'
            ]);*/

            foreach ($designs as $i => $d) {
                echo $d['id'];
                $translation = $this->fetchGoogleAPI($d['en'], $langSource, $langTarget);
                DB::table(config('dbi.tablenames.designs'))->where('id', $d['id'])->update(['design_bg' => $translation]);

                /*fputcsv($file, [
                    $d['id'],
                    $d['de'],
                    $d['en'],
                    $translation
                ]);*/
            }
        }
    }

    public function getDesigns () {
        echo 'Get Designs from DBI ... ';

        $dbi = DB::table(config('dbi.tablenames.designs'))
            ->select([
                'id             AS id',
                'design_en      AS en'
            ])
            ->whereNotNull('design_en')
            ->whereNull('design_bg')
            ->orderBy('id', 'ASC')
            ->get();
        $dbi =  json_decode($dbi, TRUE);

        echo count($dbi)." Records\n";

        return $dbi;
    }

    public function fetchGoogleAPI ($string, $langSource, $langTarget) {
        $url = [
            'https://translate.googleapis.com/translate_a/single?client=gtx',
            'sl='.$langSource,
            'tl='.$langTarget,
            'dt=t',
            'q='.urlencode($string)
        ];

        echo ' ... fetching ... ';
        $response = @file_get_contents(implode('&', $url));
        if (empty($response)) die('Google: No response');
        $response = json_decode($response, true);

        // Remap response to extract the strings
        if (!empty($response[0])) {
            $response = array_map(function ($fragment) {
                return empty($fragment[0]) ? '' : $fragment[0];
            }, $response[0]);
            $response = implode('', $response);
            echo "OK\n";
        }
        else {
            $response = null;
            echo "FAIL\n";
        }

        return $response;
    }
}
