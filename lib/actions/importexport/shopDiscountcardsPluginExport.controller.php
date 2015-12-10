<?php

class shopDiscountcardsPluginExportController extends waJsonController {

    public function execute() {
        try {
            $model = new shopDiscountcardsPluginModel();
            $discountcards = $model->getAll();
            if (!$discountcards) {
                throw new waException('Список дисконтных карт пуст');
            }
            $filepath = wa()->getCachePath('plugins/discountcards/export-discountcards.csv', 'shop');

            $f = fopen($filepath, 'w');
            if (!$f) {
                throw new waException('Ошибка создания файла: ' . $filepath);
            }

            foreach ($discountcards as $discountcard) {
                unset($discountcard['id']);
                fputcsv($f, $discountcard, ';', '"');
            }

            fclose($f);

            $this->response['html'] = 'Выгрузка успешно завершена. <a href="?plugin=discountcards&module=download"><i class="icon16 download"></i>Скачать</a>';
        } catch (Exception $ex) {
            $this->setError($ex->getMessage());
        }
    }

}
