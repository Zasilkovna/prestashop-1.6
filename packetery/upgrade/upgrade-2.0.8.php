<?php

// WARNING: there has to be the checkRequirements call at the beginning of each upgrade method

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Packetery $module
 * @return bool
 */
function upgrade_module_2_0_8($module)
{
    if (!$module->checkRequirements()) {
        return false;
    }

    if (
        !$module->unregisterHook('paymentTop') ||
        !$module->unregisterHook('processCarrier')
    ) {
        return false;
    }

    return true;
}
