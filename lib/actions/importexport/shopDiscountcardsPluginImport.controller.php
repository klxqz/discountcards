<?php

class shopDiscountcardsPluginImportController extends waJsonController {

    public function execute() {
        try {
            $filepath = wa()->getCachePath('plugins/discountcards/import-discountcards.csv', 'shop');
            if (!file_exists($filepath)) {
                throw new waException('Ошибка загрузки файла');
            }

            $delimiter = waRequest::post('delimiter');
            $enclosure = waRequest::post('enclosure');

            $inserted = 0;
            $model = new shopDiscountcardsPluginModel();
            if (waRequest::post('empty')) {
                $model->truncate();
            }

            $f = fopen($filepath, "r");
            while (($data = fgetcsv($f, null, $delimiter, $enclosure)) !== FALSE) {
                if (count($data) == 3) {
                    $discountcard = array(
                        'discountcard' => $data[0],
                        'discount' => $data[1],
                        'amount' => $data[2],
                    );
                } else {
                    $discountcard = array(
                        'contact_id' => $data[0],
                        'discountcard' => $data[1],
                        'discount' => $data[2],
                        'amount' => $data[3],
                    );
                }
                if (empty($discountcard['discountcard'])) {
                    continue;
                }

                if ($model->getByField('discountcard', $discountcard['discountcard'])) {
                    throw new waException('Ошибка: Номер дисконтной карты не уникален ' . $discountcard['discountcard']);
                }
                if ($model->insert($discountcard)) {
                    $inserted++;
                }
            }
            fclose($f);
            $this->response['html'] = 'Добавлено записей: ' . $inserted;
        } catch (Exception $ex) {
            $this->setError($ex->getMessage());
        }
    }

}
