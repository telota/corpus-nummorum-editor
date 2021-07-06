<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class check_nomisma_integrity extends Command {

    protected $signature = 'check:nomisma';
    protected $description = 'Check if Nomisma IDs are valid and available';
    public function __construct() { parent::__construct(); }

    public function handle() {
        $time = date('U');

        echo "\n--------------------- Check Nomisma Integrity ----------------------";

        $entities = [
            ['table' => 'persons', 'select' => 'nomisma_id_person'],
            ['table' => 'mints', 'select' => 'nomisma_id']
        ];
        $errors = [];

        foreach ($entities as $e) {
            echo "\n\n".strtoupper($e['table'])." (".$e['select'].")\n\n";
            echo "DB Query ... ";

            $records = DB::Table(config('dbi.tablenames.'.$e['table']))
            ->select($e['select'].' as NID')
            ->whereNotNull($e['select'])
            ->distinct()
            ->get();
            $records = json_decode($records, true);
            $records = array_map(function ($record) { return $record['NID']; }, $records);

            echo count($records)." records found.\nIterating ...\n";

            foreach ($records as $i => $nomisma) {
                echo "\t".($i + 1)."\t".$nomisma." ... ";

                $curl = shell_exec('curl -s '.config('dbi.url.nomisma').$nomisma.' -I');
                $split = preg_split('/\s+/', trim($curl));
                $status = empty($split[1]) ? 999 : $split[1];

                if ($status < 400) echo 'ok';
                else {
                    echo 'failed';
                    $errors[] = $nomisma;
                }
                echo "\n";
            }
        }

        if (empty($errors)) echo "\neverything ok";
        else echo "\nFailed Curls: ".count($errors)."\n\t".implode("\n\t", $errors);

        echo "\n\nScript finished\nExecution time: ".(date('U') - $time)." sec\n\n";
    }
}
