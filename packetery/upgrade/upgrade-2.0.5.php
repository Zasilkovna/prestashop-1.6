<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_0_5($object)
{
    $db = Db::getInstance();
    $db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'packetery_carrier`');
    return true;
}
