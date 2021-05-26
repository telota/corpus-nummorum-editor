<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\dbi\handler\index_handler;
use DB;

class test_search extends Command {

    protected $signature = 'test:search';
    protected $description = 'Test Search';
    public function __construct() { parent::__construct(); }

    public function handle() {
        echo "\n--------------------- Search Test ----------------------\n\n";

        $handler = new index_handler();

        //$statement = '((a AND (aa or bb)) AND (c OR d AND NOT e)) OR (f AND g)';
        $statement = 'NOT Bayern OR design::"knopf trag"';

        echo $statement."\n";
        $handler->handleSearch($statement);

        echo "\n\ndone\n\n";
    }
}
