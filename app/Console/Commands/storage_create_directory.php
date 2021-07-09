<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\storage\DirectoriesHandler;
use DB;

class storage_create_directory extends Command {

    protected $signature = 'storage:create_directory {entity} {id?}';
    protected $description = 'Create directory for entity and optional ID';
    public function __construct() { parent::__construct(); }

    public function handle() {
        $time = date('U');
        $handler = new DirectoriesHandler();

        $entity = $this->argument('entity');
        $id = $this->argument('id') ?? null;

        echo "\n--------------------- Create Directory for ".$entity."".(empty($id) ? 's' : (' (ID: '.$id.')'))." ----------------------\n\n";

        if (in_array($entity, ['mint', 'mints'])) $handler->createMintDirectory($id);
        else if (in_array($entity, ['user', 'users'])) $handler->createUserDirectory($id);
        else die ($entity." is not supported!\n\n");

        echo "Script finished\nExecution time: ".(date('U') - $time)." sec\n\n";
    }
}
