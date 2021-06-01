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
        $statement = '(Byzantion AND Delphin) OR (Fish AND ((Laravel OR PHP) AND NOT PYTHON))';

        echo $statement."\n";
        $this->createQuery($statement);

        echo "\n\ndone\n\n";
    }

    public function createQuery($string) {

        $query = DB::table('test')->select('*')->where(function ($subqery) use ($string) {
            $this->statementWalker($subqery, $string);
        });





        /*preg_match_all("/\((([^()]*|(?R))*)\)/",$string,$matches);
        // iterate thru matches and continue recursive split
        if (count($matches) > 1) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                if (is_string($matches[1][$i])) {
                    if (strlen($matches[1][$i]) > 0) {
                        echo $layer.":   ".$matches[1][$i]."\n";
                        $this->recursiveSplit($matches[1][$i], $layer + 1);
                    }
                }
            }
        }*/
        return $query;
    }

    public function statementWalker($query, $string, $elements = []) {

        $elements['string'] = $string;
        $elements = $this->parseString($string, $elements);

        print_r($elements);

        return $query;
    }

    public function parseString ($string, $elements) {

        $pattern = "/\((([^()]*|(?R))*)\)/";

        $parsed = preg_replace_callback($pattern, function ($match) use (&$elements) {
            if (empty($elements['nodes'])) $elements['nodes'] = [];
            $i = count($elements['nodes']);
            $newString = trim($match[1]);
            $elements['nodes'][$i]['string'] = $newString;
            echo $newString."\n";
            if (strpos($newString, '(')) $elements['nodes'][$i]['nodes'] = $this->parseString($newString, $elements['nodes'][$i]);
            return '[[[[['.$i.']]]]]';
        }, $string);

        $elements['string'] = [$parsed];

        return $elements;
    }

    public function extractor ($string, $elements = [], $layer = 0) {

        $elements[$layer][] = $string;
        $pattern = "/\((([^()]*|(?R))*)\)/";

        $parsed = preg_replace_callback($pattern, function ($match) use (&$elements, $layer) {
            $i = count($elements[$layer]);
            $elements[$layer][$i] = trim($match[1]);
            return '['.$i.']';
        }, $string);

        $elements[$layer][0] = [$parsed];

        return $elements;
    }
}
