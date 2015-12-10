<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopDiscountcardsPluginBackendLayout extends shopBackendLayout {

    public function execute() {
        parent::execute();
        $this->assign('page', 'discountcards');
    }

}
