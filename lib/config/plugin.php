<?php

return array(
    'name' => 'Дисконтные карты',
    'description' => 'Предоставление накопительной скидки по дисконтным картам',
    'vendor' => 985310,
    'version' => '1.0.0',
    'img' => 'img/discountcards.png',
    'shop_settings' => true,
    'frontend' => true,
    'importexport' => true,
    'handlers' => array(
        'backend_menu' => 'backendMenu',
        'frontend_cart' => 'frontendCart',
        'order_calculate_discount' => 'orderCalculateDiscount',
        'order_action.create' => 'orderActionCreate',
        'order_action.pay' => 'orderActionPay',
        'order_action.complete' => 'orderActionComplete',
        'order_action.refund' => 'orderActionRefund',
    ),
);
//EOF