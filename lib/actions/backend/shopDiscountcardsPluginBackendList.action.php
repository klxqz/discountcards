<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopDiscountcardsPluginBackendListAction extends waViewAction {

    public function execute() {
        $lazy = waRequest::get('lazy', 0, waRequest::TYPE_INT);
        $offset = waRequest::get('offset', 0, waRequest::TYPE_INT);
        $limit = 30;


        $model = new shopDiscountcardsPluginModel();

        $sql = "SELECT count(*) " . $this->getSql();
        $total_count = (int) $model->query($sql)->fetchField();

        $sql = "SELECT * " . $this->getSql() . " LIMIT $offset, $limit";
        $discountcards = $model->query($sql)->fetchAll();

        foreach ($discountcards as &$discountcard) {
            if (!empty($discountcard['contact_id'])) {
                $discountcard['contact'] = new waContact($discountcard['contact_id']);
            }
        }

        $this->view->assign(array(
            'discountcards' => $discountcards,
            'offset' => $offset,
            'limit' => $limit,
            'count' => count($discountcards),
            'total_count' => $total_count,
            'lazy' => $lazy,
        ));
    }

    protected function getSql() {
        $model = new waModel();
        $where = array();
        if ($discountcard = waRequest::get('discountcard')) {
            $where[] = "discountcard LIKE '" . $model->escape($discountcard) . "'";
        }

        $sql = "FROM `shop_discountcards`" . ($where ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY `id` DESC";
        return $sql;
    }

}
