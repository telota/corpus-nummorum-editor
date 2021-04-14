<?php

/*
|--------------------------------------------------------------------------
| Helper Script for ExportController via ExportInterface
|--------------------------------------------------------------------------
|
| This is a helper script providing data to the ExportController.
| The related entity is "Bibliothèque nationale de France, Département des Monnaies, médailles et antiques" (OwnerID = 35)
|
*/

namespace App\Http\Controllers\export;

use DB;


/*
|--------------------------------------------------------------------------
| This class will be called from AppController via AppInterface
|--------------------------------------------------------------------------
*/

class helper_bnf implements ExportInterface {

    public function ProvideDbi () {

        $dbase  = 'ImportCheck';

        $where  = array (
            array ('i.Owner', '=', 35),
            //array ('i.CoinID', '>', 4999),
            array ('i.CoinID', '<', 100)
        );

# ------------------------------------------------------------------------------------------------------------------------

        // Coins + Literature (Main Querey)
        $dbi_coins = DB::table (''.$dbase.'.IndividualCoins AS i')

            -> leftJoin (''.$dbase.'.Regions AS reg',           'i.Region',             '=', 'reg.RegionID')
            -> leftJoin (''.$dbase.'.Mints AS mint',            'i.MintID',             '=', 'mint.MintID')
            -> leftJoin (''.$dbase.'.Authorities AS a',         'i.AuthorityID',        '=', 'a.AuthorityID')
            -> leftJoin (''.$dbase.'.Persons AS pa',            'i.AuthorityPerson',    '=', 'pa.PersonID')
            -> leftJoin (''.$dbase.'.Tribes AS ta',             'i.AuthorityGroup',     '=', 'ta.TribeID')

            -> leftJoin (''.$dbase.'.Materials AS mat',         'i.MaterialID',         '=', 'mat.MaterialID')
            -> leftJoin (''.$dbase.'.Denominations AS de',      'i.DenominationID',     '=', 'de.DenominationID')
            -> leftJoin (''.$dbase.'.Standards AS s',           'i.StandardID',         '=', 's.StandardID')
            
            -> leftJoin (''.$dbase.'.Legends AS lo',            'i.ObverseLegendID',    '=', 'lo.LegendID')
            -> leftJoin (''.$dbase.'.Legends AS lr',            'i.ReverseLegendID',    '=', 'lr.LegendID')
            -> leftJoin (''.$dbase.'.Designs AS do',            'i.ObverseDesignID',    '=', 'do.DesignID')
            -> leftJoin (''.$dbase.'.Designs AS dr',            'i.ReverseDesignID',    '=', 'dr.DesignID')

            -> leftJoin (''.$dbase.'.Epochs AS e',              'i.EpochID',            '=', 'e.EpochID')

            -> leftJoin (''.$dbase.'.ZoteroReferences AS zr',   'i.CoinID',             '=', 'zr.CoinID')
            -> leftJoin (''.$dbase.'.zotero_data AS zd',        'zr.ZoteroID',          '=', 'zd.url')

            -> select (
                'i.CoinID AS cn_id',
                'i.forgery AS is_forgery',
                'i.PlasterCastNr AS PlasterCast',
                'i.CollectionNr AS CollectionNr',
                'i.SourceURL as source',
                'i.Provenience AS provenience',
                'reg.NomismaID as region_nomisma_id',
                'mint.NomismaID AS mint_nomisma_id',
                'a.AuthorityEng AS authority',
                'pa.PersonNomisma as ruler_nomisma_id',
                'ta.NameEngl as tribe_nomisma_id',

                'mat.MaterialNomisma AS material_nomisma_id',
                'i.DiameterMax AS diameter_max',
                'i.DiameterMin AS diameter_min',
                'i.Weight AS weight',

                'de.DenominationNomisma AS denomination_nomisma_id',
                'de.DenominationEng AS denomination',
                's.StandardEng AS standard',

                'i.ObverseUndertype AS o_struck',
                'do.DesignEng AS o_design',
                'lo.Legend AS o_legend',
                'i.ObverseCountermarkEng AS o_countermark',

                'i.ReverseUndertype AS r_struck',
                'dr.DesignEng AS r_design',
                'lr.Legend AS r_legend',
                'i.ReverseCountermarkEng AS r_countermark',

                'e.EpochNameEng AS period_name',
                'i.fromDate AS year_start',
                'i.toDate AS year_end',

                'zr.ZoteroID AS link_zotero',
                'zr.ThisCoin AS is_reference',
                'zr.Page AS page',
                'zr.Number AS number',
                'zr.Plate AS plate',
                'zr.Picture AS picture',
                'zd.author AS author',
                'zd.title AS title',
                'zd.year AS year'
            )

            -> where ($where)
            -> get();

       
        // Dies
        $dbi_dies = DB::table (''.$dbase.'.IndividualCoins AS i')

            -> leftJoin (''.$dbase.'.MonogramsHelper AS mh',    'i.CoinID',             '=', 'mh.CoinID')

            -> leftJoin (''.$dbase.'.Dies AS dio',              'i.ObverseDieID',       '=', 'dio.DieID')
            -> leftJoin (''.$dbase.'.Designs AS deo',           'dio.DesignID',         '=', 'deo.DesignID')            
            -> leftJoin (''.$dbase.'.Legends AS lo',            'dio.LegendID',         '=', 'lo.LegendID')

            -> leftJoin (''.$dbase.'.Dies AS dir',              'i.ReverseDieID',       '=', 'dir.DieID')
            -> leftJoin (''.$dbase.'.Designs AS der',           'dir.DesignID',         '=', 'der.DesignID')
            -> leftJoin (''.$dbase.'.Legends AS lr',            'dir.LegendID',         '=', 'lr.LegendID')

            -> select (
                'i.CoinID AS cn_id',
                'deo.DesignEng AS o_design',
                'lo.Legend AS o_legend',
                'der.DesignEng AS r_design',
                'lr.Legend AS r_legend'
            )

            -> where ($where)
            -> get();
        
        
        // Coins Monograms
        $dbi_coins_monograms = DB::table (''.$dbase.'.IndividualCoins AS i')

            -> leftJoin (''.$dbase.'.MonogramsHelper AS mh',    'i.CoinID',             '=', 'mh.CoinID')
            -> leftJoin (''.$dbase.'.Monograms AS mo',          'mh.MonogramID',        '=', 'mo.MonogramID')
            -> leftJoin (''.$dbase.'.Positions AS pm',          'mh.Position',          '=', 'pm.PositionID')

            -> select (
                'i.CoinID AS cn_id',
                'mh.Side AS side',
                'mo.LetterComb AS item',
                'pm.PositionEng AS position'
            )

            -> where ($where)
            -> get();


        // Coins Symbols
        $dbi_coins_symbols = DB::table (''.$dbase.'.IndividualCoins AS i')
        
            -> leftJoin (''.$dbase.'.SymbolHelper AS sh',       'i.CoinID',             '=', 'sh.CoinID')
            -> leftJoin (''.$dbase.'.Symbols AS sy',            'sh.SymbolID',          '=', 'sy.SymbolID')
            -> leftJoin (''.$dbase.'.Positions AS ps',          'sh.Position',          '=', 'ps.PositionID')

            -> select (
                'i.CoinID AS cn_id',
                'sh.Side AS side',
                'sy.SymbolEng AS item',
                'ps.PositionEng AS position'
            )

            -> where ($where)
            -> get();


        // Coins Persons
        $dbi_coins_persons = DB::table (''.$dbase.'.IndividualCoins AS i')

            -> leftJoin (''.$dbase.'.PersonsHelper AS ph',      'i.CoinID',             '=', 'ph.CoinID')
            -> leftJoin (''.$dbase.'.Persons AS p',             'ph.PersonID',          '=', 'p.PersonID')
            -> leftJoin (''.$dbase.'.PersonFunctions AS pf',    'ph.FunctionID',        '=', 'pf.PersonFunctionID')

            -> select (
                'i.CoinID AS cn_id',                    
                'p.PersonNomisma AS nomisma_id',
                'pf.FunctionEng AS function'
            )

            -> where ($where)
            -> get();


        // Types
        $dbi_types = DB::table (''.$dbase.'.IndividualCoins AS i')

            -> leftJoin (''.$dbase.'.Type_Coin_Helper AS tch',  'i.CoinID',             '=', 'tch.CoinID')
            -> leftJoin (''.$dbase.'.Types AS t',               'tch.TypeID',           '=', 't.TypeID')

            -> leftJoin (''.$dbase.'.Legends AS lo',            't.Legend_O',           '=', 'lo.LegendID')
            -> leftJoin (''.$dbase.'.Legends AS lr',            't.Legend_R',           '=', 'lr.LegendID')
            -> leftJoin (''.$dbase.'.Designs AS do',            't.Design_O',           '=', 'do.DesignID')
            -> leftJoin (''.$dbase.'.Designs AS dr',            't.Design_R',           '=', 'dr.DesignID')

            -> select (
                'i.CoinID AS cn_id',
                't.TypeID AS cn_type',

                'do.DesignEng AS o_design',
                'lo.Legend AS o_legend',
                'dr.DesignEng AS r_design',
                'lr.Legend AS r_legend'
            )

            -> where ($where)
            -> get();


        // Types Monograms
        $dbi_types_monograms = DB::table (''.$dbase.'.IndividualCoins AS i')
        
            -> leftJoin (''.$dbase.'.Type_Coin_Helper AS tch',      'i.CoinID',             '=', 'tch.CoinID')
            -> leftJoin (''.$dbase.'.Type_Monogram_Helper AS mh',   'tch.TypeID',           '=', 'mh.TypeID')
            -> leftJoin (''.$dbase.'.Monograms AS mo',              'mh.MonogramID',        '=', 'mo.MonogramID')
            -> leftJoin (''.$dbase.'.Positions AS pm',              'mh.Position',          '=', 'pm.PositionID')

            -> select (
                'i.CoinID AS cn_id',
                'mh.Side AS side',
                'mo.LetterComb AS item',
                'pm.PositionEng AS position'
            )

            -> where ($where)
            -> get();


        // Types Symbols
        $dbi_types_symbols = DB::table (''.$dbase.'.IndividualCoins AS i')
        
            -> leftJoin (''.$dbase.'.Type_Coin_Helper AS tch',      'i.CoinID',             '=', 'tch.CoinID')
            -> leftJoin (''.$dbase.'.Type_Symbol_Helper AS sh',     'tch.TypeID',           '=', 'sh.TypeID')
            -> leftJoin (''.$dbase.'.Symbols AS sy',                'sh.SymbolID',          '=', 'sy.SymbolID')
            -> leftJoin (''.$dbase.'.Positions AS ps',              'sh.Position',          '=', 'ps.PositionID')

            -> select (
                'i.CoinID AS cn_id',
                'sh.Side AS side',
                'sy.SymbolEng AS item',
                'ps.PositionEng AS position'
            )

            -> where ($where)
            -> get();
        
        
        // Types Persons
        $dbi_types_persons = DB::table (''.$dbase.'.IndividualCoins AS i')
            
            -> leftJoin (''.$dbase.'.Type_Coin_Helper AS tch',              'i.CoinID',             '=', 'tch.CoinID')
            -> leftJoin (''.$dbase.'.Type_Person_Function_Helper AS tpfh',  'tch.TypeID',           '=', 'tpfh.TypeID')
            -> leftJoin (''.$dbase.'.Persons AS p',                         'tpfh.PersonID',        '=', 'p.PersonID')
            -> leftJoin (''.$dbase.'.PersonFunctions AS pf',                'tpfh.FunctionID',      '=', 'pf.PersonFunctionID')

            -> select (
                'i.CoinID AS cn_id',                    
                'p.PersonNomisma AS nomisma_id',
                'pf.FunctionEng AS function'
            )

            -> where ($where)
            -> get();

        
        // Close Connection
        DB::disconnect ('$dbase');

# ----------------------------------------------------------------------------------------------------------------------------------------
        
        // Json decode
        $coins_raw              = json_decode ( $dbi_coins,           TRUE );
        $coins_monograms_raw    = json_decode ( $dbi_coins_monograms, TRUE );
        $coins_symbols_raw      = json_decode ( $dbi_coins_symbols,   TRUE );
        $coins_persons_raw      = json_decode ( $dbi_coins_persons,   TRUE );
        
        $types_raw              = json_decode ( $dbi_types,           TRUE );
        $types_monograms_raw    = json_decode ( $dbi_types_monograms, TRUE );
        $types_symbols_raw      = json_decode ( $dbi_types_symbols,   TRUE );
        $types_persons_raw      = json_decode ( $dbi_types_persons,   TRUE );

        $dies_raw               = json_decode ( $dbi_dies,            TRUE );

        
        // Helper Coins Dies
        foreach ($dies_raw as $die) {
            $id = $die ['cn_id'];

            $dies [$id] ['o_design']                   = empty ( $die ['o_design'] )  ? NULL : $die ['o_design'];
            $dies [$id] ['o_legend']                   = empty ( $die ['o_legend'] )  ? NULL : trim ( $die ['o_legend'] );
            $dies [$id] ['r_design']                   = empty ( $die ['r_design'] )  ? NULL : $die ['r_design'];
            $dies [$id] ['r_legend']                   = empty ( $die ['r_legend'] )  ? NULL : trim ( $die ['r_legend'] );
        }
        
        
        // Helper Coins Monograms
        foreach ($coins_monograms_raw as $monogram) {
            $id = $monogram ['cn_id'];

            if ( $monogram ['side'] == 0 ) {
                if ( empty ($coins_o_monograms [$id]) ) {
                    $coins_o_monograms [$id]          = empty ( $monogram ['item'] )  ? NULL      : ''.$monogram ['item'].' ('.$monogram ['position'].')';
                } else {
                    $coins_o_monograms [$id]         .= empty ( $monogram ['item'] )  ? ''        : '; '.$monogram ['item'].' ('.$monogram ['position'].')';
                }
            } elseif ( $monogram ['side'] == 1 ) {
                if ( empty ($coins_r_monograms [$id]) ) {
                    $coins_r_monograms [$id]          = empty ( $monogram ['item'] )  ? NULL      : ''.$monogram ['item'].' ('.$monogram ['position'].')';
                } else {
                    $coins_r_monograms [$id]         .= empty ( $monogram ['item'] )  ? ''        : '; '.$monogram ['item'].' ('.$monogram ['position'].')';
                }
            }
        }

        // Helper Coins Symbols
        foreach ($coins_symbols_raw as $symbol) {
            $id = $symbol ['cn_id'];

            if ( $symbol ['side'] == 0 ) {
                if ( empty ($coins_o_symbols [$id]) ) {
                    $coins_o_symbols [$id]            = empty ( $symbol ['item'] )    ? NULL      : ''.$symbol ['item'].' ('.$symbol ['position'].')';
                } else {
                    $coins_o_symbols [$id]           .= empty ( $symbol ['item'] )    ? ''        : '; '.$symbol ['item'].' ('.$symbol ['position'].')';
                }
            } elseif ( $symbol ['side'] == 1 ) {
                if ( empty ($coins_r_symbols [$id]) ) {
                    $coins_r_symbols [$id]            = empty ( $symbol ['item'] )    ? NULL      : ''.$symbol ['item'].' ('.$symbol ['position'].')';
                } else {
                    $coins_r_symbols [$id]           .= empty ( $symbol ['item'] )    ? ''        : '; '.$symbol ['item'].' ('.$symbol ['position'].')';
                }
            }
        }

        // Helper Coins Persons
        foreach ($coins_persons_raw as $person) {
            $id = $person ['cn_id'];

            if ( empty ($coins_persons [$id]) ) {
                $coins_persons [$id]         = empty ( $person ['nomisma_id'] ) ? NULL : ''.$person ['nomisma_id'].' ('.$person ['function'].')';
            } else {
                $coins_persons [$id]        .= empty ( $person ['nomisma_id'] ) ? '' : '; '.$person ['nomisma_id'].' ('.$person ['function'].')';
            }
        }

        // Helper Types
        foreach ($types_raw as $type) {
            $id = $type ['cn_id'];

            $types [$id] ['cn_type']            = $type ['cn_type'];
            $types [$id] ['o_design']           = empty ( $types ['o_design'] )  ? NULL : $types ['o_design'];
            $types [$id] ['o_legend']           = empty ( $types ['o_legend'] )  ? NULL : trim ( $types ['o_legend'] );
            $types [$id] ['r_design']           = empty ( $types ['r_design'] )  ? NULL : $types ['r_design'];
            $types [$id] ['r_legend']           = empty ( $types ['r_legend'] )  ? NULL : trim ( $types ['r_legend'] );
        }
        
        // Helper Types Monograms
        foreach ($types_monograms_raw as $monogram) {
            $id = $monogram ['cn_id'];

            if ( $monogram ['side'] == 0 ) {
                if ( empty ($types_o_monograms [$id]) ) {
                    $types_o_monograms [$id]          = empty ( $monogram ['item'] )  ? NULL      : ''.$monogram ['item'].' ('.$monogram ['position'].')';
                } else {
                    $types_o_monograms [$id]         .= empty ( $monogram ['item'] )  ? ''        : '; '.$monogram ['item'].' ('.$monogram ['position'].')';
                }
            } elseif ( $monogram ['side'] == 1 ) {
                if ( empty ($types_r_monograms [$id]) ) {
                    $types_r_monograms [$id]          = empty ( $monogram ['item'] )  ? NULL      : ''.$monogram ['item'].' ('.$monogram ['position'].')';
                } else {
                    $types_r_monograms [$id]         .= empty ( $monogram ['item'] )  ? ''        : '; '.$monogram ['item'].' ('.$monogram ['position'].')';
                }
            }
        }

        // Helper Types Symbols
        foreach ($types_symbols_raw as $symbol) {
            $id = $symbol ['cn_id'];

            if ( $symbol ['side'] == 0 ) {
                if ( empty ($types_o_symbols [$id]) ) {
                    $types_o_symbols [$id]            = empty ( $symbol ['item'] )    ? NULL      : ''.$symbol ['item'].' ('.$symbol ['position'].')';
                } else {
                    $types_o_symbols [$id]           .= empty ( $symbol ['item'] )    ? ''        : '; '.$symbol ['item'].' ('.$symbol ['position'].')';
                }
            } elseif ( $symbol ['side'] == 1 ) {
                if ( empty ($types_r_symbols [$id]) ) {
                    $types_r_symbols [$id]            = empty ( $symbol ['item'] )    ? NULL      : ''.$symbol ['item'].' ('.$symbol ['position'].')';
                } else {
                    $types_r_symbols [$id]           .= empty ( $symbol ['item'] )    ? ''        : '; '.$symbol ['item'].' ('.$symbol ['position'].')';
                }
            }
        }

        // Helper Types Persons
        foreach ($types_persons_raw as $person) {
            $id = $person ['cn_id'];

            if ( empty ($types_persons [$id]) ) {
                $types_persons [$id]         = empty ( $person ['nomisma_id'] ) ? NULL : ''.$person ['nomisma_id'].' ('.$person ['function'].')';
            } else {
                $types_persons [$id]        .= empty ( $person ['nomisma_id'] ) ? '' : '; '.$person ['nomisma_id'].' ('.$person ['function'].')';
            }
        }

# ----------------------------------------------------------------------------------------------------------------------------------------------

        // Build Array
        foreach ($coins_raw as $item) {
            
            $id = $item ['cn_id'];

            // build item for reference/literature
            $zotero = '';
                $zotero .= empty ($item ['author'])     ? '' : ''.$item ['author'].': ';
                $zotero .= empty ($item ['title'])      ? '' : ''.$item ['title'].'';
                $zotero .= empty ($item ['year'])       ? '' : ' | '.$item ['year'].'';
                $zotero .= empty ($item ['page'])       ? '' : ', '.$item ['page'].'';
                $zotero .= empty ($item ['number'])     ? '' : ', Nr. '.$item ['number'].'';
                $zotero .= empty ($item ['plate'])      ? '' : ', Pl. '.$item ['plate'].'';
                $zotero .= empty ($item ['picture'])    ? '' : ', Abb. '.$item ['picture'].'';


            // Let's build our Array
            
            $items [$id] ['cn_coin_id']                     = $item ['cn_id'];
            $items [$id] ['cn_type_id']                     = empty ( $types [$id] ['cn_type'] )            ? NULL : $types [$id] ['cn_type'];
            $items [$id] ['is_forgery']                     = $item ['is_forgery'] == 1                     ? TRUE : FALSE;
            $items [$id] ['PlasterCastNr']                  = empty ( $item ['PlasterCast'] )               ? NULL : $item ['PlasterCast'];
            $items [$id] ['source']                         = empty ( $item ['source'] )                    ? NULL : $item ['source'];
            $items [$id] ['CollectionNr']                   = empty ( $item ['CollectionNr'] )              ? NULL : $item ['CollectionNr'];
            $items [$id] ['provenience']                    = empty ( $item ['provenience'] )               ? NULL : $item ['provenience'];
            
            $items [$id] ['region_nomisma_id']              = empty ( $item ['region_nomisma_id'] )         ? NULL : $item ['region_nomisma_id'];
            $items [$id] ['mint_nomisma_id']                = empty ( $item ['mint_nomisma_id'] )           ? NULL : $item ['mint_nomisma_id'];
            $items [$id] ['authority']                      = empty ( $item ['authority'] )                 ? NULL : $item ['authority'];
            $items [$id] ['ruler_nomisma_id']               = empty ( $item ['ruler_nomisma_id'] )          ? NULL : $item ['ruler_nomisma_id'];
            $items [$id] ['tribe']                          = empty ( $item ['tribe_nomisma_id'] )          ? NULL : $item ['tribe_nomisma_id'];

            $items [$id] ['material_nomisma_id']            = empty ( $item ['material_nomisma_id'] )       ? NULL : substr ($item ['material_nomisma_id'], 22);
            $items [$id] ['diameter_max']                   = empty ( $item ['diameter_max'] )              ? NULL : ''.$item ['diameter_max'].' mm';
            $items [$id] ['diameter_min']                   = empty ( $item ['diameter_min'] )              ? NULL : ''.$item ['diameter_min'].' mm';
            $items [$id] ['weight']                         = empty ( $item ['weight'] )                    ? NULL : ''.$item ['weight'].' g';
            
            $items [$id] ['denomination']                   = empty ( $item ['denomination'] )              ? NULL : $item ['denomination'];
            $items [$id] ['denomination_nomisma_id']        = empty ( $item ['denomination_nomisma_id'] )   ? NULL : $item ['denomination_nomisma_id'];
            $items [$id] ['standard']                       = empty ( $item ['standard'] )                  ? NULL : $item ['standard'];


            if ( !empty ($item ['o_design']) ) { // O Design
                $items [$id] ['obverse_design']             = $item ['o_design'];
            } elseif ( !empty ($dies [$id] ['o_design']) ) {
                $items [$id] ['obverse_design']             = $dies [$id] ['o_design'];
            } else {
                $items [$id] ['obverse_design']             = empty ( $types [$id] ['o_design'] )           ? NULL : $types [$id] ['o_design'];
            }
            if ( !empty ($item ['o_legend']) ) { // O Legend
                $items [$id] ['obverse_legend']             = trim ( $item ['o_legend'] );
            } elseif ( !empty ($dies [$id] ['o_legend']) ) {
                $items [$id] ['obverse_legend']             = $dies [$id] ['o_legend'];
            } else {
                $items [$id] ['obverse_legend']             = empty ( $types [$id] ['o_legend'] )           ? NULL : $types [$id] ['o_legend'];
            }
            if ( !empty ($coins_o_monograms [$id]) ) { // O Monogram
                $items [$id] ['obverse_monogram']             = $coins_o_monograms [$id];
            } else {
                $items [$id] ['obverse_monogram']             = empty ( $types_o_monograms [$id] )          ? NULL : $types_o_monograms [$id];
            }
            if ( !empty ($coins_o_symbols [$id]) ) { // O Symbol
                $items [$id] ['obverse_symbol']             = $coins_o_symbols [$id];
            } else {
                $items [$id] ['obverse_symbol']             = empty ( $types_o_symbols [$id] )              ? NULL : $types_o_symbols [$id];
            }            
            $items [$id] ['obverse_countermark']            = empty ( $item ['o_countermark'] )             ? NULL : $item ['o_countermark'];
            $items [$id] ['obverse_struck_over']            = empty ( $item ['o_struck'] )                  ? FALSE : TRUE;


            if ( !empty ($item ['r_design']) ) { // R Design
                $items [$id] ['reverse_design']             = $item ['r_design'];
            } elseif ( !empty ($dies [$id] ['r_design']) ) {
                $items [$id] ['reverse_design']             = $dies [$id] ['r_design'];
            } else {
                $items [$id] ['reverse_design']             = empty ( $types [$id] ['r_design'] )           ? NULL : $types [$id] ['r_design'];
            }
            if ( !empty ($item ['r_legend']) ) { // R Legend
                $items [$id] ['reverse_legend']             = trim ( $item ['r_legend'] );
            } elseif ( !empty ($dies [$id] ['r_legend']) ) {
                $items [$id] ['reverse_legend']             = $dies [$id] ['r_legend'];
            } else {
                $items [$id] ['reverse_legend']             = empty ( $types [$id] ['r_legend'] )           ? NULL : $types [$id] ['r_legend'];
            }
            if ( !empty ($coins_r_monograms [$id]) ) { // R Monogram
                $items [$id] ['reverse_monogram']             = $coins_r_monograms [$id];
            } else {
                $items [$id] ['reverse_monogram']             = empty ( $types_r_monograms [$id] )          ? NULL : $types_r_monograms [$id];
            }
            if ( !empty ($coins_r_symbols [$id]) ) { // R Symbol
                $items [$id] ['reverse_symbol']             = $coins_r_symbols [$id];
            } else {
                $items [$id] ['reverse_symbol']             = empty ( $types_r_symbols [$id] )              ? NULL : $types_r_symbols [$id];
            }
            $items [$id] ['reverse_countermark']            = empty ( $item ['r_countermark'] )             ? NULL : $item ['r_countermark'];
            $items [$id] ['reverse_struck_over']            = empty ( $item ['r_struck'] )                  ? FALSE : TRUE;
            
            if ( !empty ( $coins_persons [$id] ) ) { // Person depicted
                $items [$id] ['person_depicted']            = $coins_persons [$id];
            } else {
                $items [$id] ['person_depicted']            = empty ( $types_persons [$id] )                ? NULL : $types_persons [$id];
            }
            
            $items [$id] ['period_name']                    = empty ( $item ['period_name'] )               ? NULL : $item ['period_name'];
            $items [$id] ['period_year_start']              = empty ( $item ['year_start'] )                ? NULL : $item ['year_start'];
            $items [$id] ['period_year_end']                = empty ( $item ['year_end'] )                  ? NULL : $item ['year_end'];


            if ( !isset ($items [$id] ['reference']) )      {$items [$id] ['reference']     = NULL;}
            if ( !isset ($items [$id] ['literature']) )     {$items [$id] ['literature']    = NULL;}

            if ($item ['is_reference'] == 1) { // is reference
                $items [$id] ['reference']                  = empty ( $items [$id] ['reference'] )          ? $zotero : ''.$items [$id] ['reference'].'; '.$zotero.'';
            }
            elseif ($item ['is_reference'] == 0) { // is literature
                $items [$id] ['literature']                 = empty ( $items [$id] ['literature'] )         ? $zotero : ''.$items [$id] ['literature'].'; '.$zotero.'';
            }
        
        }

        // Ignore all items without description
        foreach ($items as $item) {
            if ( !empty ($item ['obverse_design']) OR !empty ($item ['reverse_design']) ) {
                if ( !isset ($items_to_return) ) { $items_to_return [] = implode (",", array_keys ($item) );} // Build first Line for keys
                $items_to_return [$item ['cn_coin_id']]  = '"'.implode ("\",\"", $item).'"'; // build line, excaped by '"', separated by ','
            }
        }

# ----------------------------------------------------------------------------------------------------------------------------------------------

        die (implode ("\n", $items_to_return));
        // return $items;
    }

}
