<?php

class shopDiscountcardsPluginDownloadController extends waController {

    public function execute() {
        $filepath = wa()->getCachePath('plugins/discountcards/export-discountcards.csv', 'shop');
        if (!file_exists($filepath)) {
            throw new waException(_ws("Page not found"), 404);
        }
        waFiles::readFile($filepath, 'export-discountcards.csv');
    }

}
