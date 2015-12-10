<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopDiscountcardsPluginBackendOrdersAction extends waViewAction {

    public function execute() {
        $lazy = waRequest::get('lazy', 0, waRequest::TYPE_INT);
        $offset = waRequest::get('offset', 0, waRequest::TYPE_INT);
        $limit = 30;


        $model = new shopDiscountcardsPluginOrderModel();

        $sql = "SELECT count(*) " . $this->getSql();
        $total_count = (int) $model->query($sql)->fetchField();

        $sql = "SELECT * " . $this->getSql() . " LIMIT $offset, $limit";
        $discountcards_orders = $model->query($sql)->fetchAll();

        $discountcards_model = new shopDiscountcardsPluginModel();
        $order_model = new shopOrderModel();
        $workflow = new shopWorkflow();
        foreach ($discountcards_orders as &$discountcards_order) {
            $order = $order_model->getOrder($discountcards_order['order_id']);
            $discountcards_order['order'] = $order;
            $discountcards_order['order_id_encode'] = shopHelper::encodeOrderId($order['id']);
            if ($discountcard = $discountcards_model->getByField('discountcard', $discountcards_order['discountcard'])) {
                $discountcards_order['_discountcard'] = $discountcard;
            }
            $discountcards_order['state'] = $workflow->getStateById($order['state_id']);
        }
        unset($discountcards_order);

        $this->view->assign(array(
            'discountcards_orders' => $discountcards_orders,
            'offset' => $offset,
            'limit' => $limit,
            'count' => count($discountcards_orders),
            'total_count' => $total_count,
            'lazy' => $lazy,
        ));
    }

    protected function getSql() {
        $model = new waModel();
        $where = array();
        if ($discountcard = waRequest::get('discountcard')) {
            $where[] = "`discountcard` LIKE '" . $model->escape($discountcard) . "'";
        }
        if ($order_id = waRequest::get('order_id')) {
            $order_id = $this->decodeOrderId($order_id);
            $where[] = "`order_id` = '" . $order_id . "'";
        }


        $sql = "FROM `shop_discountcards_order`" . ($where ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY `order_id` DESC";
        return $sql;
    }

    protected function decodeOrderId($encoded_id) {
        $format = wa('shop')->getConfig()->getOrderFormat();
        $format = str_replace('%', 'garbage', $format);
        $format = str_replace('{$order.id}', '%', $format);
        $format = preg_split('~[^0-9%]~', $format);
        foreach ($format as $part) {
            if (strpos($part, '%')) {
                $format = $part;
                break;
            }
        }
        if (!is_string($format)) {
            return '';
        }

        $format = '/^' . str_replace('%', '(\d+)', preg_quote($format, '/')) . '$/';
        if (!preg_match($format, $encoded_id, $m)) {
            return '';
        }
        return $m[1];
    }

}
