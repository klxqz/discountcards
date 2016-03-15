<?php

class shopDiscountcardsPluginBackendSaveDiscountcardController extends waJsonController {

    public function execute() {
        try {
            $discountcard = waRequest::post('discountcard', array());
            $model = new shopDiscountcardsPluginModel();
            if (!empty($discountcard['id'])) {

                $model->updateById($discountcard['id'], $discountcard);
                $discountcard = $model->getById($discountcard['id']);
            } elseif (empty($discountcard['discountcard'])) {
                throw new waException('Ошибка: Не указан номер дисконтной карты');
            } else {
                if ($model->getByField('discountcard', $discountcard['discountcard'])) {
                    throw new waException('Ошибка: Номер дисконтной карты не уникален');
                }
                $id = $model->insert($discountcard);
                $discountcard = $model->getById($id);
            }
            if(!empty($discountcard['contact_id'])) {
                $contact = new waContact($discountcard['contact_id']);
                $discountcard['contact_name'] = $contact->get('name');
            }
            $discountcard['amount'] = shop_currency($discountcard['amount']);
            $this->response = $discountcard;
        } catch (Exception $ex) {
            $this->setError($ex->getMessage());
        }
    }

}
