<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\dbi\handler\index_handler;
use DB;

class index_tables extends Command {

    protected $signature = 'index:tables';
    protected $description = 'Create Table Statments for Index Tables';
    public function __construct() { parent::__construct(); }

    public function handle() {
        $time = date('U');

        echo "\n--------------------- Create Index Table Staments ----------------------\n\n";

        //$entities = ['coins', 'types'];
        //$given_entity = $this->argument('entity');

        $handler = new index_handler();
        $handler->createTableStatement();

        echo "Script finished\nExecution time: ".(date('U') - $time)." sec\n\n";
    }
}
