<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Http\Controllers\dbi\handler\legend_handler as handler;

class check_legends extends Command {

    protected $signature = 'check:legends';
    protected $description = 'Check Legends for invalid chars';

    public function __construct () { parent::__construct(); }

    public $replacements = [
        'A' => 'Α',
        'B' => 'Β',
        'C' => 'Ϲ',
        'D' => 'Δ',
        'E' => 'Ε',
        '€' => 'Є',
        'Є' => 'Є',
        'G' => 'Γ',
        'H' => 'Η',
        'I' => 'Ι',
        'K' => 'Κ',
        'M' => 'Μ',
        'N' => 'Ν',
        'O' => 'Ο',
        'P' => 'Ρ',
        'T' => 'Τ',
        'X' => 'Χ',
        'Y' => 'Υ',
        '•' => '·',
        ' ۬' => ' ·',
        '\'۬' => '',
        //ᛈ
    ];


    public function handle() {

        echo "\n--- LEGEND CHECK ---\n";
        echo "Getting Legends ... ";

        $dbi = DB::table(config('dbi.tablenames.legends'))->get();
        $dbi = json_decode($dbi, true);

        $legends = $dbi;

        //$legends = [];
        /*foreach ($dbi as $d) {
            $d['coins'] = 0;
            $d['types'] = 0;
            $d['dies'] = 0;
            $d['total'] = 0;
            $legends[$d['id']] = $d;
        }

        foreach (['coins', 'types', 'dies'] as $e) {
            foreach ($e === 'dies' ? [''] : ['_o', '_r'] as $s) {
                $relations = DB::table(config('dbi.tablenames.legends'))
                ->leftJoin(config('dbi.tablenames.'.$e).' AS rel', 'rel.id_legend'.$s, '=', config('dbi.tablenames.legends').'.id')
                ->select([config('dbi.tablenames.legends').'.id AS id', 'rel.id AS related'])
                ->get();
                $relations = json_decode($relations, true);

                foreach ($relations as $r) {
                    if (!empty($r['related'])) {
                        ++ $legends[$r['id']][$e];
                        ++ $legends[$r['id']]['total'];
                        //echo "\n".$r['id']."\t".$legends[$r['id']][$e];
                    }
                }
            }
        }*/

        echo count($legends)." items\nIterating ... \n";

        /*$csv = fopen('/opt/projects/corpus-nummorum/output/legends_'.date('Y-m-d').'.csv', 'w');
        fputcsv($csv, [
            'ID',
            'LANGUAGE',
            'DIRECTION',
            'LEGEND_OLD',
            'LEGEND_NEW',
            'INDEX',
            'KEYWORDS',
            'MONOGRAMS',
            'IN_USE',
            'COINS',
            'TYPES',
            'DIES'
        ]);

        $not_linked = 0;*/

        foreach ($legends as $d) {
            $legend = $d['legend'];

            echo $d['id']."\t".$legend."\n";

            // Replace invalid Chars
            if (
                $d['legend_language'] === 'el' &&
                strpos($legend, 'ΡRΟ ϹΟS') === false &&
                strpos($legend, 'F') === false &&
                strpos($legend, 'COL') === false
            ) {
                foreach ($this->replacements as $o => $r) {
                    $legend = str_replace($o, $r, $legend);
                }

                $brOpen = count(explode('[', $legend));
                $brClosed = count(explode(']', $legend));

                if ($brOpen > 1 && $brClosed === 1) {
                    $legend = str_replace('[', 'ᛈ', $legend);
                }
            }

            // Replace multi Blanks
            $legend = trim(preg_replace('/\s+/', ' ', $legend));

            // monograms
            /*$monograms = '';
            if (strpos($d['legend'], 'mon.') !== false) {
                $monograms = explode('mon', $d['legend']);
                array_shift($monograms);
                $monograms = implode('mon', $monograms);
                $monograms = str_replace('/ i.f.l.', ' ', $monograms);
                $monograms = 'mon'.str_replace('/ ', ' mon ', $monograms);

                preg_match_all('/mon\.? ([^ ,]*)[ ,]?/', $monograms, $matches);
                $monograms = array_map(function ($match) { return trim(str_replace('*', '', $match)); }, $matches[1]);
                $monograms = implode(', ', $monograms);
                echo "\n".$d['legend'].' >>> '.$monograms;
            }*/

            // Create Sort Legend
            $handler = new handler();
            $index = $handler->createIndex($legend, $d['legend_language']);

            //echo $index."\n";

            /*fputcsv($csv, [
                $d['id'],
                $d['legend_language'],
                $d['id_legend_direction'].', https://data.corpus-nummorum.eu/storage/legend-directions/'.substr('000'.$d['id_legend_direction'], -3).'.svg',
                $d['legend'],
                $legend,
                $index,
                $d['keywords'],
                $monograms,
                $d['total'] > 0 ? 'Ja' : 'Nein',
                $d['coins'],
                $d['types'],
                $d['dies']
            ]);*/

            //if ($d['total'] === 0) ++$not_linked;

            DB::table(config('dbi.tablenames.legends'))->where('id', $d['id'])->update([
                'legend' => $legend,
                'legend_sort_basis' => $index
            ]);
        }

        //echo "\nNot in Use: ".$not_linked."\n";
        echo "\nFINISHED\n";

    }

}
