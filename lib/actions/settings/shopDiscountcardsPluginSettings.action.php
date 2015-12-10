<?php

class shopDiscountcardsPluginSettingsAction extends waViewAction {

    public function execute() {
        $app_settings_model = new waAppSettingsModel();
        $settings = $app_settings_model->get(shopDiscountcardsPlugin::$plugin_id);
        if (!empty($settings['amounts'])) {
            $settings['amounts'] = json_decode($settings['amounts'], true);
        } else {
            $settings['amounts'] = array();
        }
        $this->view->assign('settings', $settings);
    }

}
