<?php

class shopDiscountcardsPluginSettingsAction extends waViewAction {

    private $templates = array(
        'FrontendCart' => array(
            'name' => 'Шаблон вывода в корзине',
            'tpl_path' => 'plugins/discountcards/templates/',
            'tpl_name' => 'FrontendCart',
            'tpl_ext' => 'html',
            'public' => false
        ),
        'FrontendMyOrders' => array(
            'name' => 'Шаблон вывода в личном кабинете (Заказы)',
            'tpl_path' => 'plugins/discountcards/templates/',
            'tpl_name' => 'FrontendMyOrders',
            'tpl_ext' => 'html',
            'public' => false
        ),
    );

    public function execute() {
        $app_settings_model = new waAppSettingsModel();
        $settings = $app_settings_model->get(shopDiscountcardsPlugin::$plugin_id);
        if (!empty($settings['amounts'])) {
            $settings['amounts'] = json_decode($settings['amounts'], true);
        } else {
            $settings['amounts'] = array();
        }
        $this->view->assign('settings', $settings);

        $templates = array();
        foreach ($this->templates as $template_id => $template) {
            $tpl_full_path = $template['tpl_path'] . $template['tpl_name'] . '.' . $template['tpl_ext'];
            $template_path = wa()->getDataPath($tpl_full_path, $template['public'], 'shop', true);
            if (file_exists($template_path)) {
                $template['template'] = file_get_contents($template_path);
                $template['change_tpl'] = 1;
            } else {
                $template_path = wa()->getAppPath($tpl_full_path, 'shop');
                $template['template'] = file_get_contents($template_path);
                $template['change_tpl'] = 0;
            }
            $templates[$template_id] = $template;
        }

        $this->view->assign('templates', $templates);
    }

}
