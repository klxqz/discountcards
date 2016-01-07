<?php

class shopDiscountcardsPlugin extends shopPlugin {

    public static $plugin_id = array('shop', 'discountcards');

    public function backendMenu() {
        if ($this->getSettings('status')) {
            $html = '<li ' . (waRequest::get('plugin') == $this->id ? 'class="selected"' : 'class="no-tab"') . '>
                        <a href="?plugin=discountcards">Дисконтные карты</a>
                    </li>';
            return array('core_li' => $html);
        }
    }

    public function frontendCart() {
        if ($this->getSettings('frontend_cart')) {
            return self::display();
        }
    }

    public static function display() {
        $app_settings_model = new waAppSettingsModel();
        if ($app_settings_model->get(self::$plugin_id, 'status')) {
            $view = wa()->getView();
            if ($discountcard = wa()->getStorage()->get('shop/discountcard')) {
                $model = new shopDiscountcardsPluginModel();
                if ($res = $model->getByField('discountcard', $discountcard)) {
                    $view->assign('discountcard', $discountcard);
                    $view->assign('discountcard_discount', $res['discount']);
                } else {
                    wa()->getStorage()->set('shop/discountcard', '');
                }
            }
            $template_path = wa()->getAppPath('plugins/discountcards/templates/FrontendCart.html', 'shop');
            $html = $view->fetch($template_path);
            return $html;
        }
    }

    public function orderCalculateDiscount($params) {
        if ($this->getSettings('status')) {
            if ($discountcard = wa()->getStorage()->get('shop/discountcard')) {
                $model = new shopDiscountcardsPluginModel();
                if ($discountcard = $model->getByField('discountcard', $discountcard)) {
                    if ($discountcard['discount']) {
                        $discount = array();
                        $def_currency = wa('shop')->getConfig()->getCurrency(true);
                        foreach ($params['order']['items'] as $item_id => $item) {
                            if ($item['type'] == 'product') {
                                if (!($this->getSettings('ignore_compare_price') && $item['product']['compare_price'] > 0)) {
                                    $discount['items'][$item_id] = array(
                                        'discount' => shop_currency($item['price'] * $discountcard['discount'] / 100.00, $item['currency'], $params['order']['currency'], false) * $item['quantity'],
                                        'description' => "Скидка по дисконтной карте {$discountcard['discount']}%"
                                    );
                                }
                            }
                        }
                        return $discount;
                    }
                } else {
                    wa()->getStorage()->set('shop/discountcard', '');
                }
            }
        }
    }

    public function orderActionCreate($params) {
        if ($this->getSettings('status')) {
            if ($discountcard = wa()->getStorage()->get('shop/discountcard')) {
                $discountcard_model = new shopDiscountcardsPluginModel();
                if ($discountcard = $discountcard_model->getByField('discountcard', $discountcard)) {
                    $discountcard_order_model = new shopDiscountcardsPluginOrderModel();
                    $data = array(
                        'order_id' => $params['order_id'],
                        'discountcard' => $discountcard['discountcard'],
                        'discount' => $discountcard['discount'],
                    );
                    $discountcard_order_model->insert($data);
                }
            }
        }
    }

    public function orderActionPay($params) {
        if ($this->getSettings('status') && $this->getSettings('order_action') == 'pay') {
            $this->additionalSum($params['order_id']);
        }
    }

    public function orderActionComplete($params) {
        if ($this->getSettings('status') && $this->getSettings('order_action') == 'complete') {
            $this->additionalSum($params['order_id']);
        }
    }

    public function orderActionRefund($params) {
        if ($this->getSettings('status') && $this->getSettings('order_action')) {
            $this->additionalSum($params['order_id'], true);
        }
    }

    protected function additionalSum($order_id, $refund = false) {
        $discountcard_order_model = new shopDiscountcardsPluginOrderModel();
        if ($discountcard_order = $discountcard_order_model->getByField('order_id', $order_id)) {
            $discountcard_model = new shopDiscountcardsPluginModel();
            if ($discountcard = $discountcard_model->getByField('discountcard', $discountcard_order['discountcard'])) {
                $order_model = new shopOrderModel();
                $order = $order_model->getOrder($order_id);
                $total = $order['total'];
                if ($this->getSettings('without_delivery') && !empty($order['shipping'])) {
                    $total -= $order['shipping'];
                }
                $def_currency = wa('shop')->getConfig()->getCurrency(true);
                $total = shop_currency($total, $order['currency'], $def_currency, false);
                if ($refund) {
                    $amount = $discountcard['amount'] - $total;
                } else {
                    $amount = $discountcard['amount'] + $total;
                }
                $data = array(
                    'amount' => $amount
                );

                if ($this->getSettings('recalculation')) {
                    $discount = $this->defineDiscount($amount);
                    $data['discount'] = $discount;
                }
                $discountcard_model->updateById($discountcard['id'], $data);
            }
        }
    }

    private static function cmpAmount($a, $b) {
        if ($a['amount'] == $b['amount']) {
            return 0;
        }
        return ($a['amount'] > $b['amount']) ? -1 : 1;
    }

    protected function defineDiscount($amount) {
        $amounts = $this->getSettings('amounts');
        if (!empty($amounts) && is_array($amounts)) {
            uasort($amounts, array('self', 'cmpAmount'));
            foreach ($amounts as $_amount) {
                if ($amount >= $_amount['amount']) {
                    return $_amount['discount'];
                }
            }
        }
        return 0;
    }

    public function frontendMyOrders($params) {
        if ($this->getSettings('frontend_my_orders')) {
            return self::displayFrontendMyOrders();
        }
    }

    public static function displayFrontendMyOrders() {
        $app_settings_model = new waAppSettingsModel();
        if ($app_settings_model->get(self::$plugin_id, 'status')) {
            $view = wa()->getView();
            if ($discountcard = wa()->getStorage()->get('shop/discountcard')) {
                $model = new shopDiscountcardsPluginModel();
                if ($res = $model->getByField('discountcard', $discountcard)) {
                    $view->assign('discountcard', $discountcard);
                    $view->assign('discountcard_discount', $res['discount']);
                    $view->assign('discountcard_amount', $res['amount']);
                } else {
                    wa()->getStorage()->set('shop/discountcard', '');
                }
            }
            $template_path = wa()->getAppPath('plugins/discountcards/templates/FrontendMyOrders.html', 'shop');
            $html = $view->fetch($template_path);
            return $html;
        }
    }

}
