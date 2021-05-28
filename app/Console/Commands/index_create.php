<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\dbi\handler\index_handler;
use DB;

class index_create extends Command {

    protected $signature = 'index:create {entity} {id?}';
    protected $description = 'Create Index for given Entity and (optional) ID';
    public function __construct() { parent::__construct(); }

    public function handle() {
        $time = date('U');

        echo "\n--------------------- Create Index ----------------------\n\n";

        $entity = $this->argument('entity');
        echo 'Updating "'.$entity.'"';
        $id = $this->argument('id');
        echo (empty($id) ? ', all datasets.' : (' ID '.$id))."\n";

        echo "\nStart Processing ...\n";
        $handler = new index_handler();
        $handler->updateIndex($entity, empty($id) ? null : $id);
        echo "done\n";

        /*// Get Entity and check if valid
        echo "Checking given Entity ... ";
        $entity = $this->argument('entity');
        if (!in_array($entity, ['coins', 'types'])) die ('"'.$entity.'" is not supported (only coins and types)!'."\n\n");
        echo '"'.$entity."\" is ok.\n";

        // Create Array from given ID or get ID of all not deleted datasets in DB
        echo "Checking given ID ... ";
        $id = $this->argument('id');
        echo empty($id) ? "no ID given, will process all datasets.\n" : ($id." was given.\n");

        // Create Handler Instance
        $handler = new index_handler();

        // Loop over IDs Array
        echo "\nStart Processing ...\n";
        $handler->updateOrInsert($entity, empty($id) ? null : $id);
        echo "done\n";*/

        // Delete all empty values
        echo "\nDeleting empty values ... ";
        DB::table(config('dbi.tablenames.index_coins'))->whereNull('data_value')->delete();
        DB::table(config('dbi.tablenames.index_types'))->whereNull('data_value')->delete();
        echo "done\n";

        echo("\nScript finished\nExecution time: ".(date('U') - $time)." sec\n\n");
    }
}
