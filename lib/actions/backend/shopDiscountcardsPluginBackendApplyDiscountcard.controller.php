<?php

class shopDiscountcardsPluginBackendApplyDiscountcardController extends waJsonController {

    public function execute() {
        try {
            $app_settings_model = new waAppSettingsModel();

            if (waRequest::post('cancel')) {
                wa()->getStorage()->set('shop/discountcard', '');
                wa()->getStorage()->set('shop/discountcard/customer_id', '');
            } else {
                if ($discountcard_number = waRequest::post('discountcard')) {
                    if (!($customer_id = waRequest::post('customer_id', 0, waRequest::TYPE_INT))) {
                        //throw new waException('Укажите покупателя');
                    }
                    $model = new shopDiscountcardsPluginModel();
                    
                    if ($app_settings_model->get(shopDiscountcardsPlugin::$plugin_id, 'binding_customer')) {
                            $discountcard = $model->getByField(array('contact_id' => $customer_id, 'discountcard' => $discountcard_number));
                            if (empty($discountcard)) {
                                $discountcard = $model->getByField(array('contact_id' => 0, 'discountcard' => $discountcard_number));
                            }
                    } else {
                        $discountcard = $model->getByField('discountcard', $discountcard_number);
                    }

                    if ($discountcard) {
                        wa()->getStorage()->set('shop/discountcard', $discountcard['discountcard']);
                        wa()->getStorage()->set('shop/discountcard/customer_id', $customer_id);
                        $discountcard['amount_format'] = shop_currency($discountcard['amount']);
                        $contact = new waContact($discountcard['contact_id']);
                        $discountcard['contact_name'] = $contact->get('name');
                        $this->response = $discountcard;
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
