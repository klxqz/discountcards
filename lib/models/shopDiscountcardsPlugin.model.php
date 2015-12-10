<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopDiscountcardsPluginModel extends waModel {

    protected $table = 'shop_discountcards';

    public function truncate() {
        $sql = 'TRUNCATE TABLE `' . $this->table . '`';
        $this->query($sql);
    }

}
