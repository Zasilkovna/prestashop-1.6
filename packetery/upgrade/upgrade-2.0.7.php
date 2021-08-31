<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Packetery $object
 * @return bool
 */
function upgrade_module_2_0_7($object)
{
    $result = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'packetery_order`
        ADD `weight` DECIMAL(20,6) NULL
    ');
  
    if ($result === false) {
      return false;
    }
  
    if (!$object->checkRequirements()) {
        return false;
    }

    $object->saveQuickLinks();

    return true;
}
