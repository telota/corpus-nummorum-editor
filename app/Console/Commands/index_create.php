<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\dbi\handler\index_handler;
use DB;

class index_create extends Command {

    protected $signature = 'index:create {entity?} {id?}';
    protected $description = 'Create Index for given Entity and ID (optional)';
    public function __construct() { parent::__construct(); }

    public function handle() {
        $time = date('U');

        echo "\n--------------------- Create Index ----------------------\n\n";

        $entities = ['coins', 'types'];
        $given_entity = $this->argument('entity');
        if ($given_entity === 'types') unset($entities[0]);
        if ($given_entity === 'coins') unset($entities[1]);

        foreach ($entities as $entity) {
            // Cleaning
            echo "DELETING deleted ".$entity." or marked as deleted... ";

            DB::table(config('dbi.tablenames.index_'.$entity).' as i')
            ->leftJoin(config('dbi.tablenames.'.$entity).' as base', 'base.id', '=', 'i.id')
            ->where(function ($subquery) {
                $subquery->orWhereNull('base.id')->orWhere('base.publication_state', 3);
            })
            ->delete();

            echo "DONE\n\n";

            // Updating
            echo 'UPDATING "'.$entity.'"';
            $id = $this->argument('id');
            echo (empty($id) ? ', all datasets.' : (' ID '.$id))."\n";

            $handler = new index_handler();
            $handler->updateIndex($entity, empty($id) ? null : $id, true);
            echo "DONE\n\n";
        }

        echo("Script finished\nExecution time: ".(date('U') - $time)." sec\n\n");
    }
}
