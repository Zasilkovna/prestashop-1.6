<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_0_5($object)
{
    $sql = [];

    $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'packetery_carrier`';

    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'packetery_address_delivery`
        CHANGE `id_branch` `id_branch` int(11) NULL,
        CHANGE `currency_branch` `currency_branch` char(3) NULL,
        ADD `is_pickup_point` tinyint(1) NOT NULL';

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }
    return true;
}
