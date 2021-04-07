<?php

namespace App\Http\Controllers\dbi\handler;


class pagination {

    public function process ($count, $input) {

        $config = config('dbi.settings');

        $limit_default  = empty($config['limit_default'])   ?   10              : $config['limit_default'];
        $limit_max      = empty($config['limit_max'])       ?   50              : $config['limit_max'];

        $offset         = empty($input['offset'])           ?   0               : intval($input['offset']);      // Set offset to 0 or given Parameter
        $limit          = empty($input['limit'])            ?   $limit_default  : intval($input['limit']);       // Set Limit to default or given Parameter
        $limit          = $limit > $limit_max               ?   $limit_max      : $limit;                        // Ensure Limit is not higher than allowed Maximum

        // calculate number of pages
        $page_count = [
            'current'   => $count > 0 ? (ceil($offset / $limit) + 1) : 1,
            'total'     => $count > 0 ? ceil($count / $limit) : 1
        ];

        // calculate current Offset
        if ($offset > 0) {
            if ($offset > $count) {
                $offset = floor($count / $limit) * $limit;
            }
            if ($offset == $count) {
                $offset = $offset > $limit ? ($offset - $limit) : 0;
            }
        }

        // calculate first page offset
        $first = $count > 0 ? 0 : null;

        // calcualte previous page offset
        if ($offset > $limit) {
            $previous = $offset - $limit;
        }
        else {
            $previous = $offset > 0 ? 0 : null;
        }

        // calculate next page offset
        $next = ($offset + $limit) < $count ? ($offset + $limit) : null;

        // calcualte last page offset
        $last = $count > 0 ? (floor(($count - 1) / $limit) * $limit) : null;

        return [
            'page'      => $page_count,
            'offset'    => $offset,
            'limit'     => $limit,
            'first'     => $first,
            'previous'  => $previous,
            'next'      => $next,
            'last'      => $last
        ];
    }    


    function finalize ($pagination, $where) {

        $sort_by = strtolower(str_replace(' ', '.', $pagination['sort_by']));
        $params = [];

        // Flat Single-Entry-Arrays for better Readability
        foreach ($where['accepted'] as $key => $val) {
            $params[$key] = isset($val[1]) ? $val : $val[0];
        }

        // Return Response
        return [
            'self'              => $this -> offset($params, $pagination['limit'], $pagination['offset'], $sort_by),
            'pageOf'            => http_build_query($params),
            'sort_by'           => $pagination['sort_by'],
            'limit'             => $pagination['limit'],
            'offset'            => $pagination['offset'],
            'count'             => $pagination['count'],
            'page'              => $pagination['page'],
            'firstPage'         => $pagination['first']    === null ? null : $this -> offset($params, $pagination['limit'], $pagination['first'], $sort_by),
            'previousPage'      => $pagination['previous'] === null ? null : $this -> offset($params, $pagination['limit'], $pagination['previous'], $sort_by),
            'nextPage'          => $pagination['next']     === null ? null : $this -> offset($params, $pagination['limit'], $pagination['next'], $sort_by),
            'lastPage'          => $pagination['last']     === null ? null : $this -> offset($params, $pagination['limit'], $pagination['last'], $sort_by)
        ];
    }
    

    function offset ($parameters, $limit, $offset, $sort_by) {

        $pagination = ['limit' => $limit, 'offset' => $offset];

        // add sort by if set
        if (!empty($sort_by)) {
            $pagination['sort_by'] = $sort_by;
        }

        return http_build_query(array_merge($parameters, $pagination));
    }
}
