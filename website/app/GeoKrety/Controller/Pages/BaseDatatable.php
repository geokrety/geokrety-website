<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Smarty;
use UserLoader;

abstract class BaseDatatable extends Base {
    use UserLoader;

    public function asDataTable(\Base $f3) {
        $response = [
            'draw' => (int) $f3->get('GET.draw'),
        ];

        $object = $this->getObject();
        $error = $this->datatable_get_parameters($f3, $object);
        if ($error !== false) {
            $response['error'] = $error;
            echo json_encode($response);
            exit();
        }
        $filter = $this->getFilter();
        $this->getHas($object);
        $filter = $this->datatable_build_search($f3, $this->getSearchable(), $filter);
        $option = ['order' => $this->datatable_build_order($f3)];

        $start = $f3->get('GET.start') / $f3->get('GET.length');
        try {
            $subset = $object->paginate($start, $f3->get('GET.length'), $filter, $option);
        } catch (\PDOException $e) {
            $response['error'] = _('This query is invalid');
            echo json_encode($response);
            exit();
        }
        Smarty::assign($this->getObjectName(), $subset);

        $response['start'] = $start;
        $response['recordsTotal'] = $subset['total'];
        $response['recordsFiltered'] = $subset['total'];
        $response['data'] = '<tbody>'.Smarty::fetch($this->getTemplate()).'</tbody>';
        echo json_encode($response);
    }

    /**
     * Validate input from datatable Ajax query.
     *
     * @return string|false False if no errors else an array of error messages
     */
    protected function datatable_get_parameters(\Base $f3, \DB\Cortex $obj) {
        $start = $f3->get('GET.start');
        $length = $f3->get('GET.length');
        $order = $f3->get('GET.order');
        $columns = $f3->get('GET.columns');

        $error_str = _('invalid value for "%s" parameter');
        if (!ctype_digit($start) or $start < 0) {
            return sprintf($error_str, 'start');
        }
        if (!ctype_digit($length) or $length < 0 or $length > 25) {
            return sprintf($error_str, 'length');
        }
        if (!is_array($order)) {
            return sprintf(_('"%s" must be an array'), 'order');
        }
        foreach ($order as $i => $v) {
            if (!is_array($v)) {
                return sprintf(_('"%s" must be an array'), 'order');
            }
            if (!ctype_digit($v['column']) or $v['column'] < 0 or !$obj->exists($columns[$v['column']]['name'])) {
                return sprintf($error_str, 'order');
            }
            if ($v['dir'] !== 'asc' and $v['dir'] !== 'desc') {
                return sprintf($error_str, 'order');
            }
        }

        return false;
    }

    protected function datatable_build_order(\Base $f3): string {
        $orders = $f3->get('GET.order');
        $columns = $f3->get('GET.columns');
        $order = [];
        foreach ($orders as $v) {
            $order[] = sprintf('%s %s', $columns[$v['column']]['name'], $v['dir']);
        }

        return join(', ', $order);
    }

    protected function datatable_build_search(\Base $f3, array $searchable, array $filter = []): array {
        $search = $f3->get('GET.search.value');
        if (empty($search)) {
            return $filter;
        }
        $searches = [];
        $searches_values = array_fill(0, sizeof($searchable), "%$search%");
        // Special case if the first column is "gkid"
        if ($searchable[0] === 'gkid') {
            $s = array_shift($searchable);
            $gkid = Geokret::gkid2id($search);
            if (!is_null($gkid)) {
                $searches[] = "$s = ?";
                $searches_values[0] = $gkid;
            } else {
                array_shift($searches_values);
            }
        }
        foreach ($searchable as $s) {
            $searches[] = "$s like ?";
        }
        $filters = sizeof($filter) ? [array_shift($filter)] : [];
        $filters[] = sizeof($searches) ? '('.join(' OR ', $searches).')' : [];
        $query = join(' AND ', $filters);

        return [$query, ...(array_merge($filter, $searches_values))];
    }

    protected function getFilter(): array {
        return [];
    }

    protected function getHas(\GeoKrety\Model\Base $object): void {
    }

    /**
     * @return string[]
     */
    protected function getSearchable(): array {
        return ['gkid', 'name'];
    }

    abstract protected function getObject(): \GeoKrety\Model\Base;

    abstract protected function getObjectName(): string;

    abstract protected function getTemplate(): string;
}
