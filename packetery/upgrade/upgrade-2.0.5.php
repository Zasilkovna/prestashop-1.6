<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_0_5($object)
{
    $result = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'packetery_address_delivery`
        CHANGE `id_branch` `id_branch` int(11) NULL,
        CHANGE `name_branch` `name_branch` varchar(255) NULL,
        CHANGE `currency_branch` `currency_branch` char(3) NULL,
        ADD `is_pickup_point` tinyint(1) NOT NULL
    ');
    if ($result === false) {
        return $result;
    }

    $oldPacketeryCarriers = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'packetery_carrier`');
    if ($oldPacketeryCarriers) {
        $psCarriers = Db::getInstance()->executeS('SELECT `id_carrier` FROM `' . _DB_PREFIX_ . 'carrier` WHERE `deleted` = 0');
        $psCarriers = array_column($psCarriers, 'id_carrier');
        $carriersToPair = [];
        foreach ($oldPacketeryCarriers as $oldPacketeryCarrier) {
            if (in_array($oldPacketeryCarrier['id_carrier'], $psCarriers)) {
                $carriersToPair[] = ['id_carrier' => $oldPacketeryCarrier['id_carrier'], 'is_cod' => $oldPacketeryCarrier['is_cod'], 'is_pickup_point' => 1];
            }
        }
    }
    if ($carriersToPair) {
        Db::getInstance()->insert('packetery_address_delivery', $carriersToPair);
    }

    $result = ('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'packetery_carrier`');

    if ($result) {
        Configuration::deleteByName('PACKETERY_FORCED_COUNTRY');
        Configuration::deleteByName('PACKETERY_FORCED_LANG');
    }

    return $result;
}
