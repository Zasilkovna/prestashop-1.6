<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Zásilkovna, s.r.o.
 * @copyright 2012-2016 Zásilkovna, s.r.o.
 * @license   LICENSE.txt
 */

require_once('../../config/config.inc.php');
require_once('../../init.php');
require_once('packetery.php');

$context = Context::getContext();
$cart = $context->cart;
if (Tools::getIsset('pickup_point')) {
    $pickupPoint = Tools::getValue('pickup_point');
}

if (!$cart || !$cart->id || !$pickupPoint) {
    return;
}

$packeteryOrderFields = [
    'id_branch' => (int)$pickupPoint['id'],
    'name_branch' => pSQL($pickupPoint['name']),
    'currency_branch' => pSQL($pickupPoint['currency']),
];
if ($pickupPoint['pickupPointType'] == 'external') {
    $packeteryOrderFields['is_carrier'] = 1;
    $packeteryOrderFields['id_branch'] = (int)$pickupPoint['carrierId'];
    $packeteryOrderFields['carrier_pickup_point'] = pSQL($pickupPoint['carrierPickupPointId']);
}

$db = Db::getInstance();
$isOrderSaved = $db->getValue('SELECT 1 FROM `' . _DB_PREFIX_ . 'packetery_order` WHERE `id_cart` = ' . ((int)$cart->id));
if ($isOrderSaved) {
    $db->update('packetery_order', $packeteryOrderFields, '`id_cart` = ' . ((int)$cart->id));
} else {
    $packeteryOrderFields['id_cart'] = ((int)$cart->id);
    $db->insert('packetery_order', $packeteryOrderFields);
}

header("Content-Type: application/json");
echo Tools::jsonEncode(array('success' => true));
