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

$sql = array(
    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'packetery_order` (
            `id_order` INT,
            `id_cart` INT,
            `id_branch` INT NOT NULL,
            `name_branch` VARCHAR(255) NOT NULL,
            `currency_branch` CHAR(3) NOT NULL,
            `is_cod` tinyint(1) NOT NULL DEFAULT 0,
            `exported` tinyint(1) NOT NULL DEFAULT 0,
            `is_carrier` tinyint(1) NOT NULL DEFAULT 0,
            `carrier_pickup_point` VARCHAR(40) NULL,
            `weight` DECIMAL(20,6) NULL,
            UNIQUE(`id_order`),
            UNIQUE(`id_cart`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'packetery_payment` (
            `module_name` varchar(255) not null primary key,
            `is_cod` tinyint(1) not null default 0
        ) engine=' . _MYSQL_ENGINE_ . ' default charset=utf8;',

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'packetery_address_delivery` (
            `id_carrier` INT NOT NULL PRIMARY KEY,
            `id_branch` INT NULL,
            `name_branch` VARCHAR(255) NULL,
            `currency_branch` CHAR(3) NULL,
            `is_cod` tinyint(1) NOT NULL DEFAULT 0,
            `is_pickup_point` tinyint(1) NOT NULL DEFAULT 0
        ) engine=' . _MYSQL_ENGINE_ . ' DEFAULT charset=utf8;'
);
