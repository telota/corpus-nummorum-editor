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

        echo(
            "\n--------------------- Create Index ----------------------\n\n"
        );

        // Get Entity and check if valid
        echo "Checking given Entity ... ";
        $entity = $this->argument('entity');
        if (!in_array($entity, ['coins', 'types'])) die ('"'.$entity.'" is not supported (only coins and types)!'."\n\n");
        echo '"'.$entity."\" is ok.\n";

        // Create Array from given ID or get ID of all not deleted datasets in DB
        echo "Checking given ID ... ";
        if ($given_id = $this->argument('id')) {
            echo $given_id." was given.\n";
            $ids = [$given_id];
        }
        else {
            echo "no ID given, query DB for IDs ... ";
            $ids = DB::table(config('dbi.tablenames.'.$entity))
                ->select('id')
                ->where('publication_state', '!=', 3)
                ->get();
            $ids = array_map(function ($dataset) { return $dataset['id']; }, json_decode($ids, true));
            echo "done\n";
        }

        // Create Handler Instance
        $handler = new index_handler();

        // Loop over IDs Array
        $count = count($ids);
        echo "\nStart Processing, ".$count." dataset(s) to handle ...\n";
        foreach ($ids as $i => $id) {
            echo "ID ".$id."\t";
            if ($handler->updateOrInsert($entity, $id)) echo 'ok';
            else 'error';
            echo ", remaining: ".($count - $i -1)."\n";
        }
        echo "done\n";

        echo("\nScript finished\nExecution time: ".(date('U') - $time)." sec\n\n");
    }
}
