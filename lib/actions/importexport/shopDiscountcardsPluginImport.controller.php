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
                if (empty($data[0])) {
                    continue;
                }
                $discountcard = array(
                    'discountcard' => $data[0],
                    'discount' => $data[1],
                    'amount' => $data[2],
                );
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
