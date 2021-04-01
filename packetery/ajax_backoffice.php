<?php

if (!defined('_PS_ADMIN_DIR_')) {
    define('_PS_ADMIN_DIR_', getcwd());
}

require_once('../../config/config.inc.php');
require_once('packetery.php');

if (!Context::getContext()->employee->isLoggedBack()) {
    $packetery = new Packetery();
    exit(json_encode(['error' => $packetery->l('Please log in to the administration again.')]));
}

if (Tools::getValue('action') === 'adminOrderChangeBranch') {
    Packetery::adminOrderChangeBranch();
}
