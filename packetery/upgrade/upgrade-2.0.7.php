<?php

// WARNING: there has to be the checkRequirements call at the beginning of each upgrade method

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Packetery $object
 * @return bool
 */
function upgrade_module_2_0_7($object)
{
    if (!$object->checkRequirements()) {
        return false;
    }

    $object->saveQuickLinks();

    return true;
}
