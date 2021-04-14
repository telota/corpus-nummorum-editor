<?php

/*
|--------------------------------------------------------------------------
| Helper Script for ExportController via ExportInterface
|--------------------------------------------------------------------------
|
| This is a helper script providing data to the ExportController.
| The related entity is "IKMK"
|
*/

namespace App\Http\Controllers\export;

use DB;

/*
|--------------------------------------------------------------------------
| This class will be called from AppController via AppInterface
|--------------------------------------------------------------------------
*/

class helper_intern implements ExportInterface {


    public function ProvideDbi () {

        $dbase = 'ImportCheck';

        $dbi = DB::table (''.$dbase.'.Types AS t')

            -> leftJoin (''.$dbase.'.Mints AS mint',            'i.MintID',             '=', 'mint.MintID')
            -> leftJoin (''.$dbase.'.Materials AS mat',         'i.MaterialID',         '=', 'mat.MaterialID')
            -> leftJoin (''.$dbase.'.Legends AS lo',            'i.ObverseLegendID',    '=', 'lo.LegendID')
            -> leftJoin (''.$dbase.'.Legends AS lr',            'i.ReverseLegendID',    '=', 'lr.LegendID')
            -> leftJoin (''.$dbase.'.Designs AS do',            'i.ObverseDesignID',    '=', 'do.DesignID')
            -> leftJoin (''.$dbase.'.Designs AS dr',            'i.ReverseDesignID',    '=', 'dr.DesignID')
            -> leftJoin (''.$dbase.'.Epochs AS e',              'i.EpochID',            '=', 'e.EpochID')
            -> leftJoin (''.$dbase.'.PersonsHelper AS ph',      'i.CoinID',             '=', 'ph.CoinID')
            -> leftJoin (''.$dbase.'.Persons AS p',             'ph.PersonID',          '=', 'p.PersonID')
            -> leftJoin (''.$dbase.'.MonogramsHelper AS mh',    'i.CoinID',             '=', 'mh.CoinID')
            -> leftJoin (''.$dbase.'.Monograms AS mo',          'mh.MonogramID',        '=', 'mo.MonogramID')
            -> leftJoin (''.$dbase.'.Positions AS pm',          'mh.Position',          '=', 'pm.PositionID')
            -> leftJoin (''.$dbase.'.SymbolHelper AS sh',       'i.CoinID',             '=', 'sh.CoinID')
            -> leftJoin (''.$dbase.'.Symbols AS sy',            'sh.SymbolID',          '=', 'sy.SymbolID')
            -> leftJoin (''.$dbase.'.Positions AS ps',          'sh.Position',          '=', 'ps.PositionID')
            -> leftJoin (''.$dbase.'.ZoteroReferences AS zr',   'i.CoinID',             '=', 'zr.CoinID')
            -> leftJoin (''.$dbase.'.zotero_data AS zd',        'zr.ZoteroID',          '=', 'zd.url')

            -> select ( 			
                    'i.CollectionNr AS ikmk_id',
                    'i.CoinID AS cn_id',
                    'i.Provenience AS provenienz_extern',
                    'mint.NomismaID AS mint_nomisma_id',
                    'mat.MaterialNomisma AS material_nomisma_id',
                    'i.DiameterMax AS diameter',
                    'i.Axis AS die_axis',
                    'i.Weight AS weight',
                    'lo.Legend AS o_leg_text',
                    'do.Design AS o_image_description',
                    'lr.Legend AS r_leg_text',
                    'dr.Design AS r_image_description',
                    'mh.Side AS monogram_side',
                    'mo.LetterComb AS monogram',
                    'pm.Position AS monogram_position',
                    'sh.Side AS symbol_side',
                    'sy.Symbol AS symbol',
                    'ps.Position AS symbol_position',
                    'e.EpochName AS period_period_name',
                    'i.fromDate AS year_start',
                    'i.toDate AS year_end',
                    'p.PersonNomisma AS person_nomis_id',
                    'zr.ZoteroID AS link_zotero',
                    'zr.ThisCoin AS reference',
                    'zr.Page AS page',
                    'zr.Number AS number',
                    'zr.Plate AS plate',
                    'zr.Picture AS picture',
                    'zd.author AS author',
                    'zd.title AS title',
                    'zd.year AS year'
            )

            -> where ([
                ['i.Owner',         '=', 2],
                ['i.CollectionNr',  '=', '']
                , ['i.CoinID', '=', 21407]
            ])

            -> whereIn ('i.MintID', [96, 122, 126, 127, 128, 130, 131, 135, 136])
            
            //-> limit (100)
            -> get();
        
            DB::disconnect('$dbase');


        $items_raw = json_decode ($dbi, TRUE);

        // Ignore all items without description
        foreach ($items_raw as $item) {
            if ( !isset ($items_to_return) ) { $items_to_return [] = implode (",", array_keys ($item) );} // Build first Line for keys
            $items_to_return [$item ['id']]  = '"'.implode ("\",\"", $item).'"'; // build line, excaped by '"', separated by ','
        }

        # ----------------------------------------------------------------------------------------------------------------------------------------------

        die (implode ("\n", $items_to_return));        
        //return $items;
    }

}
