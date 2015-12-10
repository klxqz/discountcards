<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
return array(
    'shop_discountcards' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'discountcard' => array('varchar', 25),
        'discount' => array('int', 11, 'null' => 0),
        'amount' => array('decimal', '15,4', 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('id'),
        ),
    ),
    'shop_discountcards_order' => array(
        'order_id' => array('int', 11, 'null' => 0),
        'discountcard' => array('varchar', 25),
        'discount' => array('int', 11, 'null' => 0),
        ':keys' => array(
            'order_id' => array('order_id', 'unique' => 1),
            'discountcard' => 'discountcard',
        ),
    ),
);
