<?php

$model = new waModel();

try {
    $sql = "ALTER TABLE `shop_discountcards` ADD `contact_id` INT NOT NULL AFTER `id` , ADD INDEX ( `contact_id` ) ";
    $model->query($sql);
} catch (waDbException $e) {
    
}