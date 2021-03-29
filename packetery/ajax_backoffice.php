<?php
require_once('../../config/config.inc.php');
require_once('../../init.php');
require_once('packetery.php');

$cookie = new Cookie('psAdmin', '', (int)Configuration::get('PS_COOKIE_LIFETIME_BO'));
$employee = new Employee((int)$cookie->id_employee);
if (!$employee->isLoggedBack()) {
    return false;
}

if (Tools::getValue('action') === 'adminOrderChangeBranch') {
    Packetery::adminOrderChangeBranch();
}
