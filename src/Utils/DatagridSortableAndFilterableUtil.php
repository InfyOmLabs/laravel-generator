<?php

namespace InfyOm\Generator\Utils;

use Illuminate\Http\Request;

trait DatagridSortableAndFilterableUtil
{
    /**
     * Convert datagrid filters to repository filters.
     *
     * @param Request &$request
     */
    private function convertDatagridFilterToRepositoryFilter(Request &$request)
    {
        if (!isset($request['f'])) {
            return;
        }

        $f = $request['f'];

        if (isset($f['order_by']) && !empty($f['order_by'])
            && isset($f['order_dir']) && !empty($f['order_dir'])) {
            $request['orderBy'] = $f['order_by'];
            $request['sortedBy'] = $f['order_dir'] !== 'DESC' ? 'asc' : 'desc';
        }

        unset($f['order_by']);
        unset($f['order_dir']);
        $searches = [];
        foreach ($f as $field => $value) {
            if ($value == '') {
                continue;
            }
            $searches[] = $field.':'.$value;
        }
        $request['search'] = implode(';', $searches);
        $request['searchUseAnd'] = 1;
    }

    /**
     * Set default order by and direction.
     *
     * @param Request $request
     */
    private function setDefaultOrder(&$request, $by, $dir = 'ASC')
    {
        if (!isset($request['f']['order_by']) ||
            isset($request['f']['order_by']) && $request['f']['order_by'] == '') {
            $request['orderBy'] = $by;
            $request['sortedBy'] = $dir;
        }
    }
}
