<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Packetery $object
 * @return bool
 */
function upgrade_module_2_0_6($object)
{
    if (
        !$object->unregisterHook('orderDetailDisplayed') ||
        !$object->registerHook(['displayOrderDetail', 'displayOrderConfirmation', 'actionGetExtraMailTemplateVars'])
    ) {
        return false;
    }

    return true;
}
