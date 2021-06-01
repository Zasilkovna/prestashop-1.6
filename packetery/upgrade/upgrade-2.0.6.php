<?php

// WARNING: there has to be the checkRequirements call at the beginning of each upgrade method

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Packetery $object
 * @return bool
 */
function upgrade_module_2_0_6($object)
{
    if (!$object->checkRequirements()) {
        return false;
    }

    if (
        !$object->unregisterHook('orderDetailDisplayed') ||
        !$object->registerHook(['displayOrderDetail', 'displayOrderConfirmation', 'actionGetExtraMailTemplateVars'])
    ) {
        return false;
    }

    return Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'packetery_order` ADD `is_pickup_point` tinyint(1) NOT NULL DEFAULT 0;
        UPDATE `' . _DB_PREFIX_ . 'packetery_order` SET `is_pickup_point` = 1 WHERE `name_branch` NOT LIKE "% HD";
    ');
}
