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
            `id_order` int,
            `id_cart` int,
            `id_branch` int NOT NULL,
            `name_branch` varchar(255) NOT NULL,
            `currency_branch` char(3) NOT NULL,
            `is_cod` tinyint(1) NOT NULL DEFAULT 0,
            `exported` tinyint(1) NOT NULL DEFAULT 0,
            `is_carrier` tinyint(1) NOT NULL DEFAULT 0,
            `carrier_pickup_point` varchar(40) NULL,
            `weight` decimal(20,6) NULL,
            UNIQUE(`id_order`),
            UNIQUE(`id_cart`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',

    'create table if not exists `' . _DB_PREFIX_ . 'packetery_payment` (
            `module_name` varchar(255) not null primary key,
            `is_cod` tinyint(1) not null default 0
        ) engine=' . _MYSQL_ENGINE_ . ' default charset=utf8;',

    'create table if not exists `' . _DB_PREFIX_ . 'packetery_address_delivery` (
            `id_carrier` int not null primary key,
            `id_branch` int null,
            `name_branch` varchar(255) null,
            `currency_branch` char(3) null,
            `is_cod` tinyint(1) not null default 0,
            `is_pickup_point` tinyint(1) not null default 0
        ) engine=' . _MYSQL_ENGINE_ . ' default charset=utf8;'
);
