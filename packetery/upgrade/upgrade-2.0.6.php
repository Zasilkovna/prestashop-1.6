<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_0_6($object)
{
    if (
        !$object->unregisterHook('orderDetailDisplayed') ||
        !$object->registerHook(['displayOrderDetail', 'displayOrderConfirmation', 'actionGetExtraMailTemplateVars'])
    ) {
        return false;
    }
}
