<?php

$plugin_id = array('shop', 'discountcards');
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'status', '1');
$app_settings_model->set($plugin_id, 'frontend_cart', '1');
$app_settings_model->set($plugin_id, 'frontend_my_orders', '1');
$app_settings_model->set($plugin_id, 'order_action', '');
$app_settings_model->set($plugin_id, 'without_delivery', '1');
$app_settings_model->set($plugin_id, 'ignore_compare_price', '1');
$app_settings_model->set($plugin_id, 'recalculation', '1');
$app_settings_model->set($plugin_id, 'amounts', '[{"amount":0,"discount":0}]');
