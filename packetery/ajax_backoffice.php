<?php

if (!defined('_PS_ADMIN_DIR_')) {
    define('_PS_ADMIN_DIR_', getcwd());
}

require_once('../../config/config.inc.php');
require_once('packetery.php');

if (!Context::getContext()->employee->isLoggedBack()) {
    $packetery = new Packetery();
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
    echo json_encode(['error' => $packetery->l('Please log in to the administration again.')]);
    exit;
}

if (Tools::getValue('action') === 'adminOrderChangeBranch') {
    Packetery::adminOrderChangeBranch();
}
