<?php

class shopDiscountcardsPluginFrontendDiscountcardController extends waJsonController {

    public function execute() {
        try {
            if (waRequest::post('cancel')) {
                wa()->getStorage()->set('shop/discountcard', '');
            } else {
                if ($discountcard = waRequest::post('discountcard')) {
                    $model = new shopDiscountcardsPluginModel();
                    if ($model->getByField('discountcard', $discountcard)) {
                        wa()->getStorage()->set('shop/discountcard', $discountcard);
                    } else {
                        throw new waException('Дисконтная карта не найдена');
                    }
                } else {
                    throw new waException('Укажите номер дисконтной карты');
                }
            }
        } catch (Exception $ex) {
            $this->setError($ex->getMessage());
        }
    }

}
