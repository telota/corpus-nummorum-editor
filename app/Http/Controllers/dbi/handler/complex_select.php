<?php

namespace App\Http\Controllers\dbi\handler;

use DB;
use App\Http\Controllers\dbi\handler\pagination;
use App\Http\Controllers\dbi\handler\index_handler;


class complex_select {

    public function paramPreprocessing ($entity, $user, $input) {
        // Hide not published objects for any average user
        if($user['level'] < 9) $input['state_public'] = 1;
        else {
            if (!isset($input['state_public']) && isset($input['public'])) $input['state_public'] = $input['public'];
            if (isset($input['state_public']) && $input['state_public'] === 'all') $input['state_public'] = [0, 1, 2];
        }
        if (!empty($input['sort_by'])) $input['sort_by'] = trim(str_replace(' ', '.', $input['sort_by']));

        return $input;
    }

    public function handleRequest ($entity, $user, $id, $input) {
        $base_name  = config('dbi.tablenames.'.$entity);
        $base_alias = substr($entity, 0, 1);
        $base_id    = $base_alias.'.id';

        // ID given->single detailed Request -----------------------------------------------------
        if(!empty($id)) {
            $query = DB::table($base_name.' AS '.$base_alias);

            // Join
            $join = 'App\Http\Controllers\dbi\entities\\'.$entity.'\request_id_join';
            $join = new $join();
            $query = $join->instructions($query);

            // Select
            $select = 'App\Http\Controllers\dbi\entities\\'.$entity.'\request_id_select';
            $select = new $select();
            $query->select($this->createSelect($select->instructions($user)));

            $query->where($base_id, $id);

            return $this->postProcessing($query->get());
        }


        // No ID given->parametric request -----------------------------------------------------
        else {
            //$input = Request::post();

            // Set basic variables
            $pagination = $where = $where_display = $selection = [];


            // Prequery -------------------------------------------------------------------
            $prequery = DB::table($base_name.' AS '.$base_alias)->select($base_id.' AS id');

            // Where
            $where = 'App\Http\Controllers\dbi\entities\\'.$entity.'\request_parametric_where';
            $where = new $where();
            $allowed_where = $where->instructions();
            $where = $this->getWhereParams($allowed_where, $input);
            $where_display = $where['display'];

            if (!empty($where['where'])) $prequery = $this->handleWhere($entity, $allowed_where, $where, $prequery);

            // Quicksearch
            if (!empty($input['q']) && in_array($entity, ['coins', 'types'])) {
                $indexHandler = new index_handler();
                $handled = $indexHandler->handleSearch($entity, $prequery, $input);
                $where_display = array_merge($handled['where'], $where_display);
                $prequery = $handled['query'];
            }

            // Order by
            if (empty($input['sort_by'])) {
                $pagination['sort_by'] = 'id ASC';
                $prequery->orderBy($base_id, 'ASC');
            }
            else {
                $order_by = 'App\Http\Controllers\dbi\entities\\'.$entity.'\request_parametric_order_by';
                $order_by = new $order_by();
                $allowed_order_by = $order_by->instructions();

                $ordered = $this->orderBy($base_id, $allowed_order_by, $input, $prequery);
                $pagination['sort_by'] = $ordered['sort_by'];
                $prequery = $ordered['query'];
            }

            // Execute Prequery
            $dbi = $prequery->get();
            $dbi = json_decode($dbi, TRUE);

            // count records
            $count = $pagination['count'] = empty($dbi[0]) ? 0 : count($dbi);

            // Pagination
            $paginator = new pagination;
            $pagination = array_merge($pagination, $paginator->process($count, $input));
            $offset = $pagination['offset'];
            $limit = $pagination['limit'];

            // Get selection of IDs
            for ($i = $offset; $i < ($offset + $limit); $i++) {
                if ($i >= $count) { break; }
                $selection[] = $dbi[$i]['id'];
            }

            // Mainquery -------------------------------------------------------------------
            if (!empty($selection)) {
                $query = DB::table($base_name.' AS '.$base_alias);

                // Select
                $select = 'App\Http\Controllers\dbi\entities\\'.$entity.'\request_parametric_select';
                $select = new $select();
                $query->select($this->createSelect($select->instructions($user)));

                // Join
                $join = 'App\Http\Controllers\dbi\entities\\'.$entity.'\request_parametric_join';
                $join = new $join();
                $query = $join->instructions($query);

                $dbi = $query
                   ->whereIntegerInRaw($base_id, $selection)
                   ->orderByRaw('FIELD('.$base_id.', '.implode(',', $selection).')')
                   ->get();

                $dbi = $this->postProcessing($dbi);
            }

            // Return -------------------------------------------------------------------
            return [
                'pagination'    => $paginator->finalize($pagination, $where_display),
                'where'         => $where_display,
                'contents'      => empty($dbi) ? [] : $dbi
            ];
        }
    }


    public function createSelect ($input) {
        foreach ($input As $key => $val) {
            if(isset($val['raw'])) {
                $select[] = DB::raw($val['raw'].' AS '.$key);
            }
            else {
                $select[] = $val.' AS '.$key;
            }
        }
        return $select;
    }


    public function getWhereParams ($allowed_keys, $input) {
        $where  = $accepted = $ignored = [];

        //die(print_r(array_keys($allowed_keys)));
        foreach ($input as $key => $val) {
            foreach ((is_array($val) ? $val : [$val]) as $value) { // ensure given value is an array
                // check if parameter is not null/empty
                if ($value !== null && $value !== '') {
                    // set given key to lower case to improve compability
                    $key = strtolower($key);
                    // check if parameter is allowed
                    if (in_array($key, array_keys($allowed_keys), true)) {
                        $where[$key][] = $accepted[$key][] = $value;
                    }
                    else if (!in_array($key, ['sort_by', 'limit', 'offset'])) { // ignore pagination params
                        $ignored[$key][] = $value;
                    }
                }
            }
        };

        return [
            'display' => $accepted,
            'where' => $where
        ];
    }


    public function handleWhere ($entity, $allowed_keys, $where, $query) {
        $alias = substr($entity, 0, 1);
        $table_name = config('dbi.tablenames.'.$entity).' AS '.$alias;
        $table_id = $alias.'.id';

        foreach ($where['where'] as $key => $value) {

            // Subquery with joined Tables
            if (isset($allowed_keys[$key]['joins'])) {
                $src = $allowed_keys[$key];

                // Generic Search without specific Connector ( === OR)
                if (!isset($src['connector'])) {
                    $query->whereIn($table_id, function ($subquery) use ($src, $value, $table_name, $table_id) {

                        // Set base table
                        $subquery->select($table_id)->from($table_name);
                        // Joins
                        foreach ($src['joins'] as $join) {
                            $subquery->leftJoin(...$join);
                        }
                        // Conditions if given
                        if (isset($src['conditions'])) {
                            $subquery->where($src['conditions']);
                        }
                        // Where
                        $subquery->where(function ($subsubquery) use ($src, $value) {
                            foreach($this->createWhere($src['where'], $value) AS $val) {
                                $subsubquery->orWhere([$val]);
                            }
                        });
                    });
                }
                // Connected Search - AND is given
                elseif ($src['connector'] === 'AND') {
                    foreach($this->createWhere($src['where'], $value) AS $val) {
                        $query->whereIn($table_id, function ($subquery) use ($src, $val, $table_name, $table_id) {

                            // Set base table
                            $subquery->select($table_id)->from($table_name);
                            // Joins
                            foreach ($src['joins'] as $join) {
                                $subquery->leftJoin(...$join);
                            }
                            // Conditions if given
                            if (isset($src['conditions'])) {
                                $subquery->where($src['conditions']);
                            }
                            // Where
                            $subquery->where([$val]);
                        });

                    }
                }
            }

            // Subquery without any Joins
            else {
                $src = $allowed_keys[$key];

                $query->where(function ($subquery) use ($src, $value) {
                    foreach($this->createWhere($src, $value) AS $val) {
                        $subquery->orWhere([$val]);
                    }
                });
            }
        }

        return $query;
    }


    public function createWhere ($src, $value) {

        if(!is_array($src) || isset($src['raw'])) {
            foreach ($value as $val) {
                $wheres[] = [
                    isset($src['raw']) ? DB::raw($src['raw']) : $src,
                    $val
                ];
            }
        }
        else {
            $column     = $src[0];
            $operator   = isset($src[1]) ? $src[1] : '=';
            $wc_start   = isset($src[2]) ? $src[2] : '';
            $wc_end     = isset($src[3]) ? $src[3] : '';

            foreach ($value as $val) {
                $wheres[] = [
                    isset($column['raw']) ? DB::raw($column['raw']) : $column,
                    $operator,
                    $wc_start.$val.$wc_end
                ];
            }
        }

        return $wheres;
    }


    public function orderBy ($base_id, $allowed_order_by, $input, $prequery) {
        // Explode sort by to extract key and operator
        $sort_explode   = explode('.', $input['sort_by']);
        $exploded_op    = isset($sort_explode[1]) ? strtoupper(array_pop($sort_explode)) : 'ASC';
        $ob_key         = implode('.', $sort_explode); // implode remaining sort by (theoretically a dot could be given)

        // Check if extracted order by key is allowed
        if (in_array($ob_key, array_keys($allowed_order_by))) {
            $ob_op  = $exploded_op === 'DESC' ? 'DESC' : 'ASC';
            $sort_by = $ob_key.' '.$ob_op;

            // Some keys differ depend on ASC or DESC
            if (isset($allowed_order_by[$ob_key][strtolower($ob_op)])) {
                $ob_col = $allowed_order_by[$ob_key][strtolower($ob_op)];
            }
            else {
                $ob_col = $allowed_order_by[$ob_key];
            }

            $prequery->orderByRaw(
                $ob_col.' IS NULL, '.   // NULLs last
                $ob_col.' '.$ob_op      // order by given key and operator
            );
        }
        else {
            $prequery->orderBy($base_id, 'ASC');
            $sort_by = 'id ASC';
        }

        return [
            'sort_by' => $sort_by,
            'query' => $prequery
        ];
    }


    public function postProcessing ($dbi) {
        foreach ($dbi as $item) {
            $rows = [];
            foreach ($item as $key => $val) {
                if ($key === 'dbi') {
                    $val = json_decode($val, true);
                    if (!empty($val['images'])) $val['images'] = $this->handleImages($val['images'], true);
                    $rows[$key] = $val;
                }
                else if ($key === 'images') {
                    $val = json_decode($val, true);
                    if (!empty($val)) $val = $this->handleImages($val);
                    $rows[$key] = $val;
                }
                else if ($val === '') {
                    $rows[$key] = null;
                }
                else if (is_numeric($val)) {
                    $rows[$key] = floatval($val);
                }
                else if ($key === 'axes') { // Special Treatment for Axes in Types...
                    $rows[$key] = null;
                    foreach(json_decode($val, true) as $axis) {
                        if (!empty($axis)) { $rows[$key][] = $axis; }
                    }
                }
                else if (substr($val, 0, 2) === '[{' || substr($val, 0, 1) === '{') {
                    $rows[$key] = json_decode($val, true);
                }
                else {
                    $rows[$key] = $val;
                }
            }
            $items[] = $rows;
        }
        return empty($items) ? [] : $items;
    }

    public function handleImages ($images, $is_dbi = false) {
        $thumbnails = config('dbi.settings.thumbnails');
        $digilib_scaler = config('dbi.url.digilib_scaler');
        $digilib_viewer = config('dbi.url.digilib_viewer');

        $column = array_column($images, 'kind');
        array_multisort($column, SORT_ASC, $images);

        foreach ($images as $i => $img) {
            foreach (['obverse', 'reverse'] as $side) {
                $images[$i][$side]['digilib'] = null;
                foreach($thumbnails as $size => $value) $images[$i][$side]['thumbnail'][$size] = $img[$side]['link'];
                if ($is_dbi === true) $images[$i][$side]['path'] = null;

                if (!empty($img[$side]['link'])) {
                    $src = trim($img[$side]['link'], '/');
                    if ($is_dbi === true) $images[$i][$side]['path'] = $src;

                    if (substr($src, 0, 4) !== 'http') {
                        $split = explode('.', $src);
                        $ext = strtolower(end($split));
                        if (substr($src, 0, 8) === 'storage/') $src = substr($src, 8);
                        if ($is_dbi === true) $images[$i][$side]['path'] = $src;

                        $images[$i][$side]['digilib'] = $digilib_viewer.urlencode($src);

                        if ($ext === 'tif' || $ext === 'tiff') $images[$i][$side]['link'] = $digilib_scaler.$src.'&dw=500&dh=500';
                        else $images[$i][$side]['link'] = trim(config('dbi.url.storage'), '/').'/'.$src;

                        // Add digilib Thumbnails
                        foreach($thumbnails as $size => $value) {
                            $images[$i][$side]['thumbnail'][$size] = $digilib_scaler.urlencode($src).'&dw='.$value.'&dh='.$value;
                        }
                    }
                }
            }
        }
        return $images;
    }
}
