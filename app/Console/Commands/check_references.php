<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\standardMail;

class check_references extends Command {
    
    protected $signature = 'check:references {no_mail?}';
    protected $description = 'Check references for inconsistencies';    

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
        $issues = [];

        echo "\n------------------------\n"."    References Check"."\n------------------------\n";

        foreach (['coins', 'types'] as $entity) {
            echo "\n".strtoupper($entity)."\n\n";
            $e = substr($entity, 0, -1);

            echo 'Get '.$e.' references to trashed Zotero items ... ';
            $items = DB::table(config('dbi.tablenames.'.$entity.'_to_zotero').' as e')
            -> leftJoin(config('dbi.tablenames.zotero').' as z', 'z.zotero_id', '=', 'e.zotero_id')
            -> select([
                DB::Raw('CONCAT("cn '.$e.' ", e.id_'.$e.') as cn_id'),
                DB::Raw('CONCAT("https://data.corpus-nummorum.eu/editor#/'.$entity.'/show/", e.id_'.$e.') as cn_link'),
                'z.zotero_id as zotero_id',
                DB::Raw('CONCAT("'.config('dbi.url.zotero').'", z.zotero_id) as zotero_link'),
            ])
            -> where('z.is_trash', 1)
            -> orderBy('e.id_'.$e, 'asc')
            -> get();

            $items = json_decode($items, true);
            echo count($items).' items fround'."\n";

            if (!empty($items)) {
                foreach ($items as $item) {
                    echo "\t".$item['cn_id']."\t".$item['zotero_id']."\n";

                    if (empty($issues[$item['cn_id']])) {
                        $issues[$item['cn_id']] = '<a href="'.$item['cn_link'].'">'.$item['cn_id'].'</a>:&emsp;';
                    }

                    $issues[$item['cn_id']] .= "\n".'<a href="'.$item['zotero_link'].'">'.$item['zotero_id'].'</a>';
                }  
            }
        }

        // Send result as mail
        if (empty($this->argument('no_mail')) && count($issues) > 1) {

            // Get Email-Adresses of Admins
            $admins = json_decode(DB::table(config('dbi.tablenames.users')) -> select('email') -> where('access_level', '>', 30) -> get(), true);
            if (!empty($admins[0])) {
                $admins = array_map(function ($item) { return $item['email']; }, $admins);
    
                $content = count($issues).' CN-Datensätze sind mit Zotero-Titeln verknüpft, die in Zotero als "<b>gelöscht</b>" markiert wurden.'."\n".
                '<br/>Bitte entfernen Sie die betroffenen Referenzen oder passen Sie sie an.'."\n".
                '<br/><br/>'."\n".
                "<ol>\n<li>".implode("</li>\n<li>", $issues)."</li>\n</ol>";

                // Send Mail
                Mail::to($admins/*'jan.koester@bbaw.de'*/) -> send(
                    new standardMail(['content' => $content], [
                        'subject' => 'Editor: Zotero Check '.date('Y/W'),
                        'view' => 'generic_message'
                    ])
                );
            }
        }

        echo "\nExecution Time: ".(date('U') - $time)." sec\n";
        echo "------------------------\n"." SUCCESSFULLY FINISHED "."\n------------------------\n\n";
    }

    // -------------------------------------------------------------------------------------
    // Helper Functions
}
