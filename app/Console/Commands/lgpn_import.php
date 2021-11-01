<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class lgpn_import extends Command {

    protected $signature = 'lgpn:import';
    protected $description = 'Get LGPN Data';
    public function __construct() { parent::__construct(); }

    static $src = 'http://clas-lgpn2.classics.ox.ac.uk/cgi-bin/lgpn_search.cgi?qtype=names&chr=&callback=jsonp1633425256757&_=1633425267308';
    static $table = 'lgpn_names';

    public function handle() {
        $time = date('U');

        echo "\n--------------------- Get LGPN Data ----------------------\n\n";

        $items = self::fetch();
        $file = fopen('/opt/projects/corpus-nummorum/output/lgpn.csv', 'w');
        //fputcsv($file, ['ID', 'NAME', 'notBefore', 'notAfter', 'firstChar', 'combination']);
        $sql = [];

        foreach ($items as $item) {
            $parsed = self::parseString($item['id']);

            if (!empty($parsed['letters']) && $item['firstChar'] != 9) {
                if ($item['notBefore'] == 0) $item['notBefore'] = 1;
                if ($item['notBefore'] == 999 || $item['notBefore'] == -999) $item['notBefore'] = 'null';
                if ($item['notAfter'] == 0) $item['notAfter'] = -1;
                if ($item['notAfter'] == 999 || $item['notAfter'] == -999) $item['notAfter'] = 'null';

                //$item['combination'] = self::parseString($item['id']);
                $sql[] = '('.implode(',', [
                    'null',
                    '"'.$item['id'].'"',
                    '"'.$item['name'].'"',
                    '"'.$parsed['sanitized'].'"',
                    '"'.$item['firstChar'].'"',
                    '"'.$parsed['letters'].'"',
                    $item['notBefore'],
                    $item['notAfter'],
                    $item['number'],
                    'null'
                ]).')';
                //fputcsv($file, array_values($item));
            }
        }

        self::writeSQLFile($sql);

        //fclose($file);

        echo "Script finished\nExecution time: ".(date('U') - $time)." sec\n\n";
    }

    static function fetch () {
        $src = self::$src;

        echo "\n".'Fetching Data from "'.$src.'" ... ';
        $data = file_get_contents($src);

        if (empty($data)) die('Error: Data is empty ...' + "\n\n");
        $data = substr($data, 19, -5).']';
        $data = json_decode($data, true);

        if (empty($data)) {
            echo 'no Data'."\n";
            return [];
        }
        else {
            echo count($data).' records retrieved'."\n";
            return $data;
        }
    }

    static function parseString ($string) {
        $list = [
            'A' => 'Α',
            'B' => 'Β',
            'C' => 'Χ',
            'D' => 'Δ',
            'E' => 'Ε',
            'F' => 'Φ',
            'G' => 'Γ',
            'H' => 'Η',
            'I' => 'Ι',
            'K' => 'Κ',
            'L' => 'Λ',
            'M' => 'Μ',
            'N' => 'Ν',
            'O' => 'Ο',
            'P' => 'Π',
            'Q' => 'Θ',
            'R' => 'Ρ',
            'S' => 'Σ',
            'T' => 'Τ',
            'U' => 'Υ',
            'V' => 'Ͷ',
            'W' => 'Ω',
            'X' => 'Ξ',
            'Y' => 'Ψ',
            'Z' => 'Ζ'
        ];

        $string = mb_convert_encoding($string, 'UTF-8');
        $string = mb_strtoupper($string, 'UTF-8');

        $keys = [];
        $name = '';

        foreach (str_split($string) as $letter) {
            $greek = empty($list[$letter]) ? false : $list[$letter];

            if ($greek !== false) {
                $keys[$letter] = $greek;
                $name .= $greek;

                //if (empty($name)) $name = $greek;
                //else $name .= mb_strtolower($greek, 'UTF-8');
            }
        }

        $keys = array_values($keys);
        sort($keys);
        $keys = implode('', $keys);

		return [
            'sanitized' => $name,
            'letters' => $keys
        ];
	}

    static function writeSQLFile ($content) {
        echo('WRITING SQL FILE ... ');
        $table = self::$table;

        $sql =
            '/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;'."\n".
            '/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;'."\n".
            '/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;'."\n".
            '/*!40101 SET NAMES utf8 */;'."\n".
            '/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;'."\n".
            '/*!40103 SET TIME_ZONE=\'+01:00\' */;'."\n".
            '/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;'."\n".
            '/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;'."\n".
            '/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=\'NO_AUTO_VALUE_ON_ZERO\' */;'."\n".
            '/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;'."\n".
            "\n".
            '--'."\n".
            '-- Table structure for table `'.$table.'`'."\n".
            '--'."\n".
            "\n".
            'DROP TABLE IF EXISTS `'.$table.'`;'."\n".
            '/*!40101 SET @saved_cs_client     = @@character_set_client */;'."\n".
            '/*!40101 SET character_set_client = utf8 */;'."\n".

            // Create Table Statement
            'CREATE TABLE `'.$table.'` (
                `id` int NOT NULL AUTO_INCREMENT,
                `id_lgpn` varchar(255) NOT NULL,
                `name_original` varchar(255) NOT NULL,
                `name_sanitized` varchar(255) NOT NULL,
                `first_letter` char(1) NOT NULL,
                `letters` varchar(255) DEFAULT NULL,
                `date_from` int DEFAULT NULL,
                `date_to` int DEFAULT NULL,
                `occurances` int DEFAULT NULL,
                `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'."\n".
            '/*!40101 SET character_set_client = @saved_cs_client */;'."\n".
            "\n".
            '--'."\n".
            '-- Dumping data for table `'.$table.'`'."\n".
            '--'."\n".
            "\n".
            'LOCK TABLES `'.$table.'` WRITE;'."\n".
            '/*!40000 ALTER TABLE `'.$table.'` DISABLE KEYS */;'."\n".

            // Insert Values
            'INSERT INTO `'.$table.'` VALUES '."\n".
            implode(",\n", $content)."\n".
            ';'."\n".
            '/*!40000 ALTER TABLE `'.$table.'` ENABLE KEYS */;'."\n".
            'UNLOCK TABLES;';

        file_put_contents('/opt/projects/corpus-nummorum/output/'.$table.'.sql', $sql);
        echo("SUCCESS\n");
    }
}
