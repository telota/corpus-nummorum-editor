<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Http\Controllers\dbi\entities\types\update_inheritance as handler;

class check_inheritance extends Command {
    protected $signature = 'check:inheritance {id?}';
    protected $description = 'Check Inheritance for all or given type(s)';
    public function __construct() { parent::__construct(); }


    public function handle() {
        $time = date('U');

        echo "\n--------------------- Create Index ----------------------\n\n";

        $id = $this->argument('id');

        if (empty($id)) echo 'UPDATING all inheriting coins...'."\n";
        else echo 'UPDATING coins related to type '.$id;

        $handler = new handler();
        $handler->updateInheritance($id, true);
        echo "\nDONE\n\n";

        echo "Script finished\nExecution time: ".(date('U') - $time)." sec\n\n";
    }





















    private function InheritValuesfromType($coinid = 0) {

        $check_inherit_coin = [
            'date_start' => 'date_inherited',
            'date_end' => 'date_inherited',
            'date_string' => 'date_inherited',
            'id_period' => 'period_inherited',
            'id_legend_o' => 'legend_o_inherited',
            'id_legend_r' => 'legend_r_inherited',
            'id_design_o' => 'design_o_inherited',
            'id_design_r' => 'design_r_inherited',
            'id_issuer' => 'issuer_inherited',
            'id_authority' => 'authority_inherited',
            'id_authority_person' => 'authority_person_inherited',
            'id_authority_group' => 'authority_group_inherited',
            'id_mint' => 'mint_inherited',
            'id_material' => 'material_inherited',
            'id_standard' => 'standard_inherited',
            'id_denomination' => 'denomination_inherited',
        ];

        $i=0;
        $db_entry = [];
        $sql_coin_id = '';
        $sql_inherit_id = '';

        if($coinid > 0) {
            $sql_coin_id = ' && table_coin.id = '.$coinid.' ';
            $sql_inherit_id = ' && inherit.id = '.$coinid.' ';
        }

        foreach ($check_inherit_coin as $field => $inherit) {

            # get ids to update
            $sql_log= DB::select(DB::raw(
                "SELECT
                    table_coin.id as id_coin,
                    table_type.id as id_type,
                    table_type.".$field." as field_type,
                    table_coin.".$field." as field_coin,
                    table_type.updated_at as type_update,
                    table_coin.updated_at as coin_update

                FROM cn_data.data_coins table_coin

                LEFT OUTER JOIN cn_data.data_coins_to_types_inherit inherit
                    ON inherit.id = table_coin.id

                LEFT OUTER JOIN cn_data.data_types table_type
                    ON inherit.id_type = table_type.id

                WHERE inherit.".$inherit." = 1
                    AND (
                        (table_coin.".$field." != table_type.".$field.")
                        OR (table_coin.".$field." IS NULL AND table_type.".$field." IS NOT NULL)
                        OR (table_coin.".$field." IS NOT NULL AND table_type.".$field." IS NULL)
                    )
                    ".$sql_coin_id."
                ;"));

                foreach ($sql_log as $value) {
                    $i++;
                    $db_entry[] = "('".$value->id_coin."', '".$value->id_type."', '".$field."', '".$value->field_coin."', '".$value->field_type."')";
                }

            # update inherit period rows, if the type has different values
            $sql_inherit = DB::select(DB::raw(
                "INSERT INTO  cn_data.data_coins (id, ".$field.")
                (
                SELECT
                    table_coin.id as id,
                    table_type.".$field." as ".$field."

                FROM cn_data.data_coins table_coin

                LEFT OUTER JOIN cn_data.data_coins_to_types_inherit inherit
                    ON inherit.id = table_coin.id

                LEFT OUTER JOIN cn_data.data_types table_type
                    ON inherit.id_type = table_type.id

                WHERE inherit.".$inherit." = 1
                    AND (
                        (table_coin.".$field." != table_type.".$field.")
                        OR (table_coin.".$field." IS NULL AND table_type.".$field." IS NOT NULL)
                        OR (table_coin.".$field." IS NOT NULL AND table_type.".$field." IS NULL)
                    )
                    ".$sql_coin_id."
                )
                ON DUPLICATE KEY UPDATE
                    ".$field." = VALUES(".$field.")
                ;"));
        }

        # relations
        $check_inherit_coin = array(
            'person_inherit' => array(
                'table_heritage' => 'data_coins_to_persons',
                'value_heritage'  => 'id_coin',
                'table_ancestor' => 'data_types_to_persons',
                'value_ancestor' => 'id_type',
                'check_field' => array(
                    'id_person',
                    'id_function',
                ),
            ),
            'symbol_o_inherited' => array(
                'table_heritage' => 'data_coins_to_symbols',
                'value_heritage'  => 'id_coin',
                'table_ancestor' => 'data_types_to_symbols',
                'value_ancestor' => 'id_type',
                'marker' => array(
                    'side' => 0,
                ),
                'check_field' => array(
                    'id_symbol',
                    'id_position',
                ),
            ),
            'symbol_r_inherited' => array(
                'table_heritage' => 'data_coins_to_symbols',
                'value_heritage'  => 'id_coin',
                'table_ancestor' => 'data_types_to_symbols',
                'value_ancestor' => 'id_type',
                'marker' => array(
                    'side' => 1,
                ),
                'check_field' => array(
                    'id_symbol',
                    'id_position',
                ),
            ),
            'monogram_o_inherited ' => array(
                'table_heritage' => 'data_coins_to_monograms',
                'value_heritage'  => 'id_coin',
                'table_ancestor' => 'data_types_to_monograms',
                'value_ancestor' => 'id_type',
                'marker' => array(
                    'side' => 0,
                ),
                'check_field' => array(
                    'id_monogram',
                    'id_position',
                ),
            ),
            'monogram_r_inherited ' => array(
                'table_heritage' => 'data_coins_to_monograms',
                'value_heritage'  => 'id_coin',
                'table_ancestor' => 'data_types_to_monograms',
                'value_ancestor' => 'id_type',
                'marker' => array(
                    'side' => 1,
                ),
                'check_field' => array(
                    'id_monogram',
                    'id_position',
                ),
            ),
        );

        foreach ($check_inherit_coin as $inherit=>$values) {
            $display = [];
            $delete  = [];
            $insert  = [];
            $header_sql = [];

            foreach ($values["check_field"] as $field) {
                $display[]  = "table_coin.".$field. " as coin_".$field.",
                table_type.".$field." as type_".$field;
                $delete[]   = "((table_coin.".$field." = table_type.".$field.") OR (table_coin.".$field." IS NULL AND table_type.".$field." IS NULL) )";
                $insert[]   = "((table_coin.".$field." = table_type.".$field.") OR (table_coin.".$field." IS NULL AND table_type.".$field." IS NULL))";
                $header_sql[] = "`".$field."`";
            }

            # set marker, as example for obverse and revers
            $marker_coin = [];
            $marker_type = [];
            $marker_sql  = [];

            if(!empty($values['marker'])) {
                foreach ($values['marker'] as $key=>$field) {
                    $marker_coin[] = " AND table_coin.".$key." = ".$field." ";
                    $marker_type[] = " AND table_type.".$key." = ".$field." ";
                    $header_sql[] = $key;
                    $marker_sql[] = "'".$field."'";
                }
            }

            ## check which values should be deleted cause the coin inherit from the type
            $sql_delete = DB::select(DB::raw(
                "SELECT

                    inherit.id_type,
                    inherit.id as id_coin,
                    table_coin.id as delete_id,
                    ".implode(",", $display)."

                    FROM cn_data.data_coins_to_types_inherit inherit

                    LEFT OUTER JOIN cn_data.".$values["table_heritage"]." table_coin
                        ON inherit.id = table_coin.".$values["value_heritage"]."

                    LEFT OUTER JOIN cn_data.".$values["table_ancestor"]." table_type
                        ON (inherit.".$values["value_ancestor"]." = table_type.".$values["value_ancestor"]."
                        AND  ".implode(" AND ", $delete)."
                        ".implode(" ", $marker_type)."
                        )

                    WHERE inherit.".$inherit." = 1
                        AND table_coin.".$values["value_heritage"]." IS NOT NULL
                        ".implode(" ", $marker_coin)."
                        AND table_type.id IS NULL
                        ".$sql_inherit_id.";"));
            $del_ids = [];  # these ids should be deleted

            # writing the log and collecting the ids
            foreach ($sql_delete as $value)  {
                $i++;
                $log_coin = [];
                $log_type = [];

                foreach ($values["check_field"] as $field) {
                    $coinfieldname = 'coin_'.$field;
                    $typefieldname = 'type_'.$field;
                    $log_coin[]  = $value->$coinfieldname;
                    $log_type[]  = $value->$typefieldname;
                }
                $db_entry[] = "('".$value->id_coin."', '".$value->id_type."', '".$inherit."', '".implode(',', $log_coin)."', '".implode(',', $log_type)."')";
                # del id
                $del_ids[] = "id = ".$value->delete_id;
            }

            # if there are same values, delete them from the table
            if (count($del_ids) > 0) {
                $sql_log = DB::select(DB::raw(
                    "DELETE FROM  cn_data.".$values["table_heritage"]."
                        WHERE
                            ".implode(' OR ', $del_ids)."
                    ;"
                ));
            }

            # which values from the type do not exist in the value table
            $sql_add = DB::select(DB::raw(
                "SELECT

                    inherit.id_type,
                    inherit.id as id_coin,
                    ".implode(",", $display)."

                FROM cn_data.data_coins_to_types_inherit inherit

                LEFT OUTER JOIN cn_data.".$values["table_ancestor"]." table_type
                    ON inherit.id_type = table_type.".$values["value_ancestor"]."

                LEFT OUTER JOIN cn_data.".$values["table_heritage"]." table_coin
                    ON (inherit.id = table_coin.".$values["value_heritage"]."
                        AND  ".implode(" AND ", $insert)."
                        ".implode(" ", $marker_coin)."
                    )
                WHERE inherit.".$inherit." = 1
                    AND table_type.".$values["value_ancestor"]." IS NOT NULL
                    ".implode(" ", $marker_type)."
                    AND table_coin.id IS NULL
                    ".$sql_inherit_id.";"));

            $add_fields = [];

            # collect the values to add and write the log
            foreach ($sql_add as $value) {
                $i++;
                $log_coin = [];
                $log_type = [];
                $new_values = [];

                foreach ($values["check_field"] as $field) {
                    $coinfieldname = 'coin_'.$field;
                    $typefieldname = 'type_'.$field;
                    $log_coin[]  = $value->$coinfieldname;
                    $log_type[]  = $value->$typefieldname;
                    if (!empty($value->$typefieldname)) {
                        $new_values[] = "'".$value->$typefieldname."'";
                    }
                    else {
                        $new_values[] = "NULL";
                    }
                }
                $marker = '';

                if (count($marker_sql) > 0) {
                    $marker = ", ".implode(",", $marker_sql);
                }
                $db_entry[] = "('".$value->id_coin."', '".$value->id_type."', '".$inherit."', '".implode(',', $log_coin)."', '".implode(',', $log_type)."')";
                $add_fields[] = "('".$value->id_coin."', ".implode(",", $new_values).$marker.")";
            }

            # if there are same values, delete them from the table
            if (count($add_fields) > 0) {
                $sql_log = DB::select(DB::raw(
                    "INSERT INTO cn_data.".$values["table_heritage"]." (`id_coin`, ".implode(',', $header_sql).")
                        VALUES
                            ".implode(', ', $add_fields)."
                    ;"
                ));
            }
        }

        # insert values into log
        if ($i > 0) {
            $date_update= DB::select(DB::raw(
                "INSERT INTO
                    cn_import.log_inheritance_control

                    (id_coin, id_type, field_value, value_coin, value_type)
                VALUES
                    ".implode(",", $db_entry)."

                ;"));
        }

        $this->info('updated '.$i.' datasets.');
    }
}
