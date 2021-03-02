<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_0_5($object)
{
    $db = Db::getInstance();
    $db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'packetery_carrier`');
    $db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'packetery_address_delivery` CHANGE `currency_branch` `currency_branch` char(3) NULL');
    return true;
}
