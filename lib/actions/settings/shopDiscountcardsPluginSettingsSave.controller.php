<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopDiscountcardsPluginSettingsSaveController extends waJsonController {

    private $templates = array(
        'FrontendCart' => array(
            'name' => 'Шаблон вывода подарков в корзине',
            'tpl_path' => 'plugins/discountcards/templates/',
            'tpl_name' => 'FrontendCart',
            'tpl_ext' => 'html',
            'public' => false
        ),
        'FrontendMyOrders' => array(
            'name' => 'Шаблон вывода подарков в корзине',
            'tpl_path' => 'plugins/discountcards/templates/',
            'tpl_name' => 'FrontendMyOrders',
            'tpl_ext' => 'html',
            'public' => false
        ),
    );

    public function execute() {
        try {
            $app_settings_model = new waAppSettingsModel();
            $shop_discountcards = waRequest::post('shop_discountcards');

            foreach ($shop_discountcards as $name => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $app_settings_model->set(shopDiscountcardsPlugin::$plugin_id, $name, $value);
            }

            $templates = waRequest::post('templates');

            foreach ($templates as $template_id => $template) {
                $s_template = $this->templates[$template_id];
                $tpl_full_path = $s_template['tpl_path'] . $s_template['tpl_name'] . '.' . $s_template['tpl_ext'];
                $template_path = wa()->getDataPath($tpl_full_path, $s_template['public'], 'shop', true);
                if (!file_exists($template_path)) {
                    $template_path = wa()->getAppPath($tpl_full_path, 'shop');
                }
                $content = file_get_contents($template_path);
                if (!empty($template['reset_tpl'])) {
                    @unlink($template_path);
                } elseif (!empty($template['template']) && $template['template'] != $content) {
                    $template_path = wa()->getDataPath($tpl_full_path, $s_template['public'], 'shop', true);
                    $f = fopen($template_path, 'w');
                    if (!$f) {
                        throw new waException('Не удаётся сохранить шаблон. Проверьте права на запись ' . $template_path);
                    }
                    fwrite($f, $template['template']);
                    fclose($f);
                }
            }


            $this->response['message'] = "Сохранено";
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
