<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_0_5($object)
{
    $result = Db::getInstance()->execute('
        DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'packetery_carrier`;
        
        ALTER TABLE `' . _DB_PREFIX_ . 'packetery_address_delivery`
        CHANGE `id_branch` `id_branch` int(11) NULL,
        CHANGE `name_branch` `name_branch` varchar(255) NULL,
        CHANGE `currency_branch` `currency_branch` char(3) NULL,
        ADD `is_pickup_point` tinyint(1) NOT NULL;
    ');

    if ($result) {
        Configuration::deleteByName('PACKETERY_FORCED_COUNTRY');
        Configuration::deleteByName('PACKETERY_FORCED_LANG');
    }

    return $result;
}
