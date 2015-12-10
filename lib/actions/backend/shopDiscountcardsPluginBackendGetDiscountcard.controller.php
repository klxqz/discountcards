<?php

class shopDiscountcardsPluginBackendGetDiscountcardController extends waJsonController {

    public function execute() {
        try {
            if ($id = waRequest::post('id', 0, waRequest::TYPE_INT)) {
                $model = new shopDiscountcardsPluginModel();
                $this->response = $model->getById($id);
            } else {
                throw new waException('Ошибка: Идентификатор неопределен');
            }
        } catch (Exception $ex) {
            $this->setError($ex->getMessage());
        }
    }

}
