<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\storage\DirectoriesHandler;
use DB;

class storage_create_mint_directories extends Command {

    protected $signature = 'storage:mints';
    protected $description = 'Create directories for mints';
    public function __construct() { parent::__construct(); }

    public function handle() {
        $time = date('U');
        $handler = new DirectoriesHandler();

        echo "\n--------------------- Create Directories for Mints ----------------------\n\n";

        echo "DB Query ... ";

        $mints = DB::Table(config('dbi.tablenames.mints'))
        ->select('nomisma_id')
        ->whereNotNull('nomisma_id')
        ->distinct()
        ->get();
        $mints = json_decode($mints, true);
        $mints = array_map(function ($mint) { return $mint['nomisma_id']; }, $mints);

        echo count($mints)." mints found.\nIterating ...\n";

        foreach ($mints as $mint) {
            echo $mint." ... ";
            echo $handler->createMintDirectory($mint) ? 'ok' : 'failed';
            echo "\n";
        }

        echo "Script finished\nExecution time: ".(date('U') - $time)." sec\n\n";
    }
}
