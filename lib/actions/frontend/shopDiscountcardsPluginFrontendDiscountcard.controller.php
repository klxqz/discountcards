<?php

class shopDiscountcardsPluginFrontendDiscountcardController extends waJsonController {

    public function execute() {
        try {
            $app_settings_model = new waAppSettingsModel();
            if (waRequest::post('cancel')) {
                wa()->getStorage()->set('shop/discountcard', '');
            } else {
                if ($discountcard_number = waRequest::post('discountcard')) {
                    $model = new shopDiscountcardsPluginModel();
                    
                    if ($app_settings_model->get(shopDiscountcardsPlugin::$plugin_id, 'binding_customer')) {
                        $contact_id = wa()->getUser()->getId();
                        $discountcard = $model->getByField(array('contact_id' => $contact_id, 'discountcard' => $discountcard_number));
                        if (empty($discountcard)) {
                            $discountcard = $model->getByField(array('contact_id' => 0, 'discountcard' => $discountcard_number));
                        }
                    } else {
                        $discountcard = $model->getByField('discountcard', $discountcard_number);
                    }

                    if ($discountcard) {
                        wa()->getStorage()->set('shop/discountcard', $discountcard['discountcard']);
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
