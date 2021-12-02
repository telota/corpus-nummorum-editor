<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class rpc_coins_crawl extends Command {

    protected $signature = 'rpc:crawlcoins';
    protected $description = 'Crawl RPC coins';
    public function __construct() { parent::__construct(); }
    // ------------------------------------------------------------------------------

    static $urls = [
        'moesia-inferior_7-2' => 'https://rpc.ashmus.ox.ac.uk/coin/browse?volume_id=14&province_id=33',
        'moesia-inferior_8' => 'https://rpc.ashmus.ox.ac.uk/coin/browse?volume_id=15&province_id=33',
        'thrace_7-2' => 'https://rpc.ashmus.ox.ac.uk/coin/browse?volume_id=14&province_id=6',
        'thrace_8' => 'https://rpc.ashmus.ox.ac.uk/coin/browse?volume_id=15&province_id=6'
    ];

    static $path        = '/opt/projects/corpus-nummorum/output/';
    static $file        = '/opt/projects/corpus-nummorum/output/rpc_coins_';
    static $get_list    = false;
    static $get_data    = true;
    static $csv         = '/opt/projects/corpus-nummorum/output/rpc_coins.csv';

    public function handle() {
        date_default_timezone_set('Europe/Berlin');
        $time = date('U');
        $date = date('Y-m-d H:i:s');

        echo(
            "\n\n".
            "----------------------------------------------------------\n".
            "---------------------- CN RPC IMPORT ---------------------\n".
            "----------------------------------------------------------\n\n".
            $date."\n"
        );

        if (self::$get_list === true) self::getList();
        if (self::$get_data === true) self::getData();

        // Regular End of Script -------------------------------------------------------------------------------------
        echo("Execution time: ".(date('U') - $time)." sec\n");
        die(
            "\n\n".
            "----------------------------------------------------------\n".
            "------------------ REGULAR END OF SCRIPT -----------------\n".
            "----------------------------------------------------------\n\n"
        );
    }

    static function getList () {
        foreach (self::$urls as $name => $url) {

            // First call to get number of records
            echo "\nFetching ".$name.' ... '."\n\n";
            $html = file_get_contents($url);
            preg_match('/<div class="panel-heading"><h4>Search results:(.*?)entries found.<\/h4><\/div>/s', $html, $match);
            if (empty($match[0]) || empty($match[1])) die ('ERROR: Number of search results not found ...'."\n");
            $count = intval(str_replace(',', '', trim($match[1])));
            if ($count < 1) die ('ERROR: Count is 0 ...'."\n");
            echo 'Number of records: '.$count."\n";

            // Calculate Pages
            $pages = ceil($count / 20);
            echo 'Number of Pages: '.$pages."\n";

            // Get records
            echo "\nStarting Loop ...";
            $separator = 50;
            $items = [];

            for ($i = 1; $i <= $pages; $i++) {
                echo "\nPage ".$i;
                $content = file_get_contents($url.'&page='.$i);
                preg_match('/<table>(.*?)<\/table>/s', $content, $html);
                preg_match_all('/<tr>(.*?)<\/tr>/s', $html[1], $trs);

                foreach ($trs[1] as $tr) $items[] = self::parseRow($tr);
                if ($i === $separator) {
                    echo "\n100 Pages crawled -> save in file";
                    file_put_contents(self::$file.$name.'_'.$separator.'.json', json_encode($items, JSON_UNESCAPED_UNICODE));
                    $separator += 50;
                }
            }
            echo "\nLoop finished: ".count($items).' Links extracted.'."\n";

            echo 'Writing json file ... ';
            file_put_contents(self::$file.$name.'.json', json_encode($items, JSON_UNESCAPED_UNICODE));
            echo 'SUCCEESS'."\n";
        }
    }

    static function parseRow ($tr) {
        // ID
        preg_match('/<a href="(.*?)">/', $tr, $id);
        $id = explode('/', $id[1]);
        $id = intval(end($id));

        // Images
        $images = [];
        preg_match_all('/<img src="(.*?)" width/', $tr, $imgs);
        foreach ($imgs[1] as $i => $img) {
            if (substr($img, 0, 1) === '/') $img = 'https://rpc.ashmus.ox.ac.uk'.$img;
            $images[$i === 0 ? 'obverse' : 'reverse'] = empty($img) ? null : $img;
        }

        //echo "\nID: $id";
        return [
            'id' => $id,
            'img_obv' => $images['obverse'],
            'img_rev' => $images['reverse'],
            'source_link' => 'https://rpc.ashmus.ox.ac.uk/coin/'.$id
        ];
    }

    static function getData () {
        $cn_types = self::getCnTypes();

        $file = fopen(self::$csv, 'w');
        fputcsv($file, [
            'ID',
            'RPC-Link',
            'RPC Type',
            'CN Type',
            'CN Group',
            'Mint',
            'Ruler',
            'Material',
            'Obv. Design',
            'Rev. Design',
            'Obv. Legend',
            'Rev. Legend',
            'Description',
            'Citation',
            'Link',
            'Diameter',
            'Weight',
            'Axis',
            'Inventory No.',
            'Obv. Image',
            'Rev. Image'
        ]);

        foreach (self::$urls as $name => $url) {
            echo "\n".$name."\n";

            $i = 0;
            $items = file_get_contents(self::$path.'rpc_coins_'.$name.'.json');
            $items = json_decode($items, true);

            foreach ($items as $item) {
                $crawled = self::crawlItem($item, $cn_types);
                fputcsv($file, array_values($crawled));
                ++$i;

                if ($i % 100 === 0) {
                    echo "SLEEP\n"; sleep(10);
                }
                else if ($i % 5 === 0) {
                    echo "SLEEP\n"; sleep(1);
                }
            }
        }

        echo "\nLoop finished: $i/".count($items)." items crawled.\n";
    }

    static function getContent ($path) {
        $data = null;
        $i = 0;

        while(empty($data) && $i < 10) {
            if ($i > 0) {
                echo 'Error!';
                sleep(30);
            }
            $data = @file_get_contents($path);
            $i++;
        }

        return $data;
    }

    static function crawlItem ($raw, $cn_types) {
        echo $raw['id']."\n";

        $item = [
            'id' => $raw['id'],
            'source_link' => $raw['source_link'],
            'rpc_type' => null,
            'type_id' => null,
            'id_objectgroup' => null,
            'id_mint' => null,
            'ruler' => null,
            'id_material' => null,
            'o_design' => null,
            'r_design' => null,
            'o_legend' => null,
            'r_legend' => null,
            'description_en' => null,
            'citations' => null,
            'link' => null,
            'diameter' => null,
            'weight' => null,
            'axis' => null,
            'collection_id' => null,
            'O_img' => empty($raw['img_obv']) ? null : $raw['img_obv'],
            'r_img' => empty($raw['img_rev']) ? null : $raw['img_rev']
        ];

        //$html = file_get_contents($raw['source_link']);
        $html = self::getContent($raw['source_link']);
        preg_match('/<div class="panel panel-default">(.*?)<div class="box-footer">/s', $html, $match);
        $html = $match[1];

        // RPC Type
        preg_match('/<h4> <a href="(.*?)"> <em>/', $html, $rpc_type);
        $rpc_type = $rpc_type[1];
        if (substr($rpc_type, 0, 1) === '/') $rpc_type = 'https://rpc.ashmus.ox.ac.uk'.$rpc_type;
        $item['rpc_type'] = $rpc_type;

        // CN Type & Group
        if (!empty($cn_types[$rpc_type])) {
            $item['type_id'] = $cn_types[$rpc_type]['id'];
            $item['id_objectgroup'] = $cn_types[$rpc_type]['group'];
            $item['id_mint'] = $cn_types[$rpc_type]['mint'];
            $item['id_material'] = $cn_types[$rpc_type]['material'];
        }

        // RPC Data
        foreach([
            'citations' => 'Reference',
            'axis' => 'Axis',
            'weight' => 'Weight',
            'diameter' => 'Diameter',
            'o_design' => 'Obverse design',
            'r_design' => 'Reverse design',
            'o_legend' => 'Obverse inscription',
            'r_legend' => 'Reverse inscription',
            'collection_id' => 'Inventory no.'
        ] as $key => $rpcKey) {
            preg_match('/<td>'.$rpcKey.'<\/td>\s+<td>(.*?)<\/td>/', $html, $match);
            if (!empty($match[1])) $item[$key] = trim($match[1]);
        }

        // Reign
        preg_match('/<a href="\/search\/browse\?reign_id=[0-9]+">(.*?)<\/a>/', $html, $match);
        if (!empty($match[1])) {
            if ($match[1] === 'Gordian III') $item['ruler'] = 464;
            $item['description_en'] .= 'Reign: '.$match[1].'; ';
        }

        // Owner
        preg_match('/Museum<\/td>\s+<td><a href="(.*?)<\/a>/', $html, $match);
        if (!empty($match[1])) {
            $match[1] = explode('">', $match[1]);
            $match[1] = end($match[1]);
            if (!empty($match[1])) $item['description_en'] .= 'Oirignal: '.$match[1].(empty($item['collection_id']) ? '' : (', '.$item['collection_id']));
        }

        // URI
        preg_match('/of the coin<\/td>\s+<td><a href="(.*?)">/', $html, $match);
        if (!empty($match[1])) {
            $item['description_en'] .= ' ('.$match[1].')';
            $item['link'] = $match[1];
        }

        return $item;
    }

    static function getCnTypes () {
        $raw = DB::table(config('dbi.tablenames.types').' AS t')
        ->select([
            't.id AS id',
            't.source_link AS src',
            't.id_mint As mint',
            't.id_material As material',
            DB::Raw('(
                SELECT ttg.id_objectgroup
                FROM '.config('dbi.tablenames.types_to_objectgroups').' as ttg
                WHERE ttg.id_type = t.id
                LIMIT 1
            ) AS objgroup')
        ])
        ->where('t.source_link', 'LIKE', 'https://rpc.ashmus.ox.ac.uk/%')
        ->get();
        $raw = json_decode($raw, true);

        $items = [];

        foreach ($raw as $item) {
            $items[$item['src']] = [
                'id' => $item['id'],
                'group' => $item['objgroup'],
                'mint' => $item['mint'],
                'material' => $item['material']
            ];
        }

        return $items;
    }
}
