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

class helper_ikmk implements ExportInterface {


    public function ProvideDbi () {

        $dbase = 'ImportCheck';

        $dbi = DB::table (''.$dbase.'.IndividualCoins AS i')

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


        foreach ($items_raw as $item) {
            // helper variable to save some typing
            
            $id = $item ['cn_id'];

            $literature = '';
                $literature .= empty ($item ['author'])     ? '' : ''.$item ['author'].': ';
                $literature .= empty ($item ['title'])      ? '' : ''.$item ['title'].'';
                $literature .= empty ($item ['year'])       ? '' : ' | '.$item ['year'].'';
                $literature .= empty ($item ['page'])       ? '' : ', '.$item ['page'].'';
                $literature .= empty ($item ['number'])     ? '' : ', Nr. '.$item ['number'].'';
                $literature .= empty ($item ['plate'])      ? '' : ', Pl. '.$item ['plate'].'';
                $literature .= empty ($item ['picture'])    ? '' : ', Abb. '.$item ['picture'].'';
                $literature .= $item ['reference'] == 0     ? '' : ' (dieses Stück)';

            $o_image_description = $item ['o_image_description'];
            $r_image_description = $item ['r_image_description'];


            if ($item ['monogram_side'] == 0 && !empty ($item ['monogram_position']) ) {
                $o_image_description .= ' Mit Monogram ('.$item ['monogram_position'].')';
                $o_image_description .= empty ($item ['monogram']) ? '.' : ': "'.$item ['monogram'].'".';
            } elseif ($item ['monogram_side'] == 1 && !empty ($item ['monogram_position']) ) {
                $r_image_description .= ' Mit Monogram ('.$item ['monogram_position'].')';
                $r_image_description .= empty ($item ['monogram']) ? '.' : ': "'.$item ['monogram'].'".';
            }

            if ($item ['symbol_side'] == 0 && !empty ($item ['symbol_position']) ) {
                $o_image_description .= ' Mit Beizeichen ('.$item ['symbol_position'].')';
                $o_image_description .= empty ($item ['symbol']) ? '.' : ': "'.$item ['symbol'].'".';
            } elseif ($item ['symbol_side'] == 1  && !empty ($item ['symbol_position']) ) {
                $r_image_description .= ' Mit Beizeichen ('.$item ['symbol_position'].')';
                $r_image_description .= empty ($item ['symbol']) ? '.' : ': "'.$item ['symbol'].'".';
            }


            // JK: Let's build our Array
            
            $items [$id] ['ikmk_id']                                = empty ( $item ['ikmk_id'] )               ? NULL : $item ['ikmk_id'];
            
            $items [$id] ['item']                                   = array (
                'ikmk_item_id'      => 1,
                'item_de'           => 'Münze',
                'item_en'           => 'Coin',
                'nomisma'           => 'coin',
                'item_nomisma'      => 'http://nomisma.org/id/coin'
            );
            
            $items [$id] ['mint'] [0] ['mint_nomisma_id']           = empty ( $item ['mint_nomisma_id'] )       ? NULL : $item ['mint_nomisma_id'];
            $items [$id] ['material'] ['material_nomisma_id']       = empty ( $item ['material_nomisma_id'] )   ? NULL : $item ['material_nomisma_id'];
            $items [$id] ['diameter']                               = empty ( $item ['diameter'] )              ? NULL : ''.$item ['diameter'].' mm';
            $items [$id] ['die_axis']                               = empty ( $item ['die_axis'] )              ? NULL : ''.$item ['die_axis'].' h';
            $items [$id] ['weight']                                 = empty ( $item ['weight'] )                ? NULL : ''.$item ['weight'].' g';
            $items [$id] ['provenienz_extern']                      = empty ( $item ['provenienz_extern'] )     ? NULL : $item ['provenienz_extern'];
            $items [$id] ['avers'] ['leg_text']                     = empty ( $item ['o_leg_text'] )            ? NULL : $item ['o_leg_text'];
            $items [$id] ['avers'] ['image_description']            = empty ( $o_image_description )            ? NULL : $o_image_description;
            $items [$id] ['revers'] ['leg_text']                    = empty ( $item ['r_leg_text'] )            ? NULL : $item ['r_leg_text'];
            $items [$id] ['revers'] ['image_description']           = empty ( $r_image_description )            ? NULL : $r_image_description;

            if (!isset ( $items [$id] ['literatur'] ))              {$items [$id] ['literatur'] = NULL;}
            $items [$id] ['literatur']                              = empty ($items [$id] ['literatur']) ? $literature : ''.$items [$id] ['literatur'].'; '.$literature.'';

            $items [$id] ['period'] ['period_name']                 = empty ( $item ['period_period_name'] )    ? NULL : $item ['period_period_name'];
            $items [$id] ['year_start']                             = empty ( $item ['year_start'] )            ? NULL : $item ['year_start'];
            $items [$id] ['year_end']                               = empty ( $item ['year_end'] )              ? NULL : $item ['year_end'];
            
            $items [$id] ['manufacturing'] [0]                      = array (
                'ikmk_manufacturing_id' =>	21,
                'manufacturing_name_de' =>	'geprägt',
                'manufacturing_name_en' =>	'struck',
                'nomisma_id'            =>	'http://nomisma.org/id/struck',
            );
        }
        
        return $items;
    }

}
