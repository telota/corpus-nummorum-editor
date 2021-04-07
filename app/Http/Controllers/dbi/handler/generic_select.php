<?php

namespace App\Http\Controllers\dbi\handler;

use DB;

class generic_select {

    public function preQuery ($table, $allowed_keys, $input) {
        // Set Table Instructions
        $table = $this -> parseTable($table);

        // Set Where and Order By Instructions
        if (!isset($allowed_keys['where'])) {
            $allowed_where = $allowed_order_by = $allowed_keys;
        }
        else {
            $allowed_where = $allowed_keys['where'];
            $allowed_order_by = $allowed_keys['order_by'];
        }

        // Set basic variables
        $pagination = $where = $where_keys = $selection = [];

        // Where Conditions
        foreach ($input as $key => $value) {
            if (in_array($key, array_keys($allowed_where)) && ($value !== null && $value !== '')) {

                if(!is_array($allowed_where[$key]) || isset($allowed_where[$key]['raw'])) {
                    foreach ((is_array($value) ? $value : [$value]) AS $val) {
                        $where[] = [isset($allowed_where[$key]['raw']) ? DB::raw($allowed_where[$key]['raw']) : $allowed_where[$key], $val];                        
                        $where_keys['accepted'][$key][] = $val;
                    }
                }
                else {
                    $column     = $allowed_where[$key][0];
                    $operator   = isset($allowed_where[$key][1]) ? $allowed_where[$key][1] : '=';
                    $wc_start   = isset($allowed_where[$key][2]) ? $allowed_where[$key][2] : '';
                    $wc_end     = isset($allowed_where[$key][3]) ? $allowed_where[$key][3] : '';

                    foreach ((is_array($value) ? $value : [$value]) AS $val) {
                        $where[] = [
                            isset($column['raw']) ? DB::raw($column['raw']) : $column, 
                            $operator, 
                            $wc_start.$val.$wc_end
                        ];
                        $where_keys['accepted'][$key][] = $val;
                    }
                }
            }
            else if (!in_array($key, ['sort_by', 'limit', 'offset'])) {
                $where_keys['ignored'][$key][] = $value;
            }
        };

        // Order by
        if (!empty($input['sort_by'])) {
            // Explode sort by to extract key and operator
            $sort_explode = explode('.', $input['sort_by']);
            $ob_op  = isset($sort_explode[1]) ? strtoupper(array_pop($sort_explode)) : 'ASC';
            $ob_op  = $ob_op === 'DESC' ? 'DESC' : 'ASC';
            $ob_key = implode('.', $sort_explode);

            if (in_array($ob_key, array_keys($allowed_order_by))) {
                $ob_col = $allowed_order_by[$ob_key];
                $pagination['sort_by'] = $ob_key.' '.$ob_op;
            }
            else {
                $ob_col = 'id';
                $pagination['sort_by'] = 'id DESC';
                $ob_op = 'DESC';
            }
        } 
        else {
            $ob_col = 'id';
            $pagination['sort_by'] = 'id DESC';
            $ob_op = 'DESC';
        }

        // Query
        $query = DB::table($table['base']);

        // Join if given
        if(!empty($table['joins'])) {
            foreach ($table['joins'] as $join) {
                $query -> leftJoin (...$join);
            }
        };

        // Execute Query
        $dbi = $query -> select($table['as'].'id') 
            -> where($where) 
            -> orderBy($ob_col, $ob_op)
            -> get();

        $dbi = json_decode ($dbi, TRUE);

        // count records
        $count = $pagination['count'] = empty($dbi[0]) ? 0 : count($dbi);

        // Limit
        $input_LIMIT    = empty($input['limit']) ? 10 : $input['limit']; // JK: ensure limit is set, otherwise set it to dafault of 10
        $limit          = $pagination['limit'] = intval($input_LIMIT > 50 ? 50 : $input_LIMIT); // JK: ensure limit is not higher than 50 to save performance

        // Offset
        $input_OFFSET   = empty($input['offset']) ? 0 : $input['offset']; // JK: ensure offset is set, otherwise set it to dafault of 0
        $offset         = $pagination['offset'] = intval($input_OFFSET < $count ? $input_OFFSET : floor($count / $limit) * $limit ); // JK: ensure offset is not higher than count

        // Get selection of IDs
        for ($i = $offset; $i <= $offset + $limit -1; $i++) {
            if ($i >= $count) { break; }
            $selection[] = $dbi[$i]['id'];
        }        

        return [
            'pagination'    => $pagination,
            'where'         => $where_keys,
            'ids'           => $selection
        ];
    }


    public function mainQuery ($table, $select, $ids) {
        // Set Table Instructions
        $table = $this -> parseTable($table);

        // DB Main Query
        $query = DB::table($table['base']);

        // Join if given
        if(!empty($table['joins'])) {
            foreach ($table['joins'] as $join) {
                $query -> leftJoin(...$join);
            }
        };

        // Execute Query
        $dbi = $query -> select($select)
            -> whereIntegerInRaw($table['as'].'id', $ids)
            -> orderByRaw('FIELD('.$table['as'].'id, '.implode(',', $ids).')')
            -> get();

        $dbi = json_decode($dbi, TRUE);

        return $dbi;
    }


    public function parseTable ($input) {
        // Just table name is given
        if (!isset($input['base'])) {
            return [
                'base' => $input,
                'as' => ''
            ];
        }

        // Table name and tables to join are given
        else {
            $explode = explode(' as ', strtolower($input['base']));
            $as = empty($explode[1]) ? '' : (trim($explode[1]).'.');

            return [
                'base' => $input['base'],
                'as' => $as,
                'joins' => $input['joins']
            ];
        }
    }
}