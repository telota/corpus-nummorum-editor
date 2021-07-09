<?php

namespace App\Http\Controllers\storage;

use Storage;
use App\Http\Controllers\storage\StorageActions;
use DB;

class DirectoriesHandler {

    public function createMintDirectory ($id = null) {

        $query = DB::table(config('dbi.tablenames.mints'))->select('id')->where('mint', 'NOT LIKE', '%(%');
        if (!empty($id)) $query->where('id', $id);
        $dbi = $query->get();
        $dbi = json_decode($dbi, true);

        foreach ($dbi as $d) {
            $directory = 'coin-images/'.$this->dirID('mint', $d['id']);
            if (Storage::missing($directory)) Storage::makeDirectory($directory);
        }
    }

    public function createUserDirectory ($id = null) {
        if (empty($id)) die ('To create a User-Directory an ID must be given!');
        $directory = 'coin-images-ext/'.$this->dirID('user', $id);
        if (Storage::missing($directory)) Storage::makeDirectory($directory);
    }

    public function indexCoinImages () {

        $base = 'coin-images';
        $img = [$base => 'Coin Images (CN)'];
        $base .= '/';

        $dbi = DB::table(config('dbi.tablenames.mints').' AS m')
        -> leftJoin(config('dbi.tablenames.mints_nomisma').' AS mn', 'mn.nomisma_id_mint', '=', 'm.nomisma_id')
        -> leftJoin(config('dbi.tablenames.regions_to_subregions').' AS sr', 'sr.nomisma_id_region', '=', 'm.imported_nomisma_subregion')
        -> leftJoin(config('dbi.tablenames.regions').' AS r', 'r.id', '=', 'sr.id_region')
        -> select([
            'm.id       AS  mint_id',
            'm.mint     AS  mint_name',
            DB::Raw('IFNULL(r.id, 0)  AS  region_id'),
            DB::Raw('IFNULL(r.de, "Andere Regionen")  AS  region_name')
        ])
        ->where('m.mint', 'NOT LIKE', '%(%')
        ->orderByRaw(
            'r.de = 0, '.
            'r.de ASC, '.
            'm.mint ASC'
        )
        ->get();
        $dbi = json_decode($dbi, true);

        $subdirectories = Storage::allDirectories('coin-images');

        for ($i = 0; $i < count($dbi); ++$i) {
            if ($i === 0 || $dbi[$i - 1]['region_id'] !== $dbi[$i]['region_id']) $img[$base.$dbi[$i]['region_id']] = $dbi[$i]['region_name'];
            $mint = $this->dirID('mint', $dbi[$i]['mint_id']);

            foreach ($subdirectories as $subdir) {
                if (str_starts_with($subdir, $base.$mint)) {
                    $fragment = substr($subdir, 22);
                    $name = explode('/', $fragment);
                    $name = end($name);
                    $img[$base.$dbi[$i]['region_id'].'/'.$mint.$fragment] = $name;
                }
            }

            $img[$base.$dbi[$i]['region_id'].'/'.$mint] = $dbi[$i]['mint_name'];
        }

        return $img;
    }

    public function redirectCoinImages ($path) {

        $path = explode('/', $path);
        unset($path[1]);

        return implode('/', $path);
    }

    public function dirID ($entity, $id) {
        $id = '00000'.$id;
        $id = substr($id, -5);

        return $entity.'_'.$id;
    }
}
