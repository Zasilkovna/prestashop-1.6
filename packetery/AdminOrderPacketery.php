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

if (!defined('_PS_VERSION_')) {
    exit();
}

require_once(dirname(__FILE__) . '/packetery.php');

class AdminOrderPacketery extends AdminTab
{
    private $packetery = null;

    public function __construct()
    {
        $this->ensureInitialized();
        parent::__construct();
    }

    private $initialized = false;

    // initialize Packetery class
    private function ensureInitialized()
    {
        if ($this->initialized) {
            return;
        }

        $this->table = 'packetery_order';
        $this->packetery = new Packetery();

        $this->initialized = true;
    }

    // adoption of Module translation method
    public function l($str, $class = 'AdminOrderPacketery', $addslashes = false, $htmlentities = true)
    {
        $this->ensureInitialized();
        return $this->packetery->l($str, $class, $addslashes, $htmlentities);
    }

    /**
     * Escapes quotes
     * @param $s
     * @return mixed
     */
    private function csvEscape($s)
    {
        return str_replace('"', '""', $s);
    }

    public function exportCsv()
    {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"export-" . date("Ymd-His") . ".csv\"");

        $db = Db::getInstance();

        /* Update is_cod for packetery orders */
        $is_cods = (is_array(Tools::getValue('packetery_order_is_cod')) ? Tools::getValue('packetery_order_is_cod') : array());
        foreach ($is_cods as $id => $is_cod) {
            $db->execute(
                'update `' . _DB_PREFIX_ . 'packetery_order` set is_cod=' .
                ((int)$is_cod) . ' where id_order=' . ((int)$id)
            );
        }

        /* Get packetery order IDs */
        $ids = array_map(
            'floor',
            is_array(Tools::getValue('packetery_order_id')) &&
            count(Tools::getValue('packetery_order_id')) > 0 ? Tools::getValue('packetery_order_id') : array(0)
        );

        /* Select data */
        $data = $db->executeS(
            'SELECT
                `o`.`id_order`, `a`.`firstname`, `a`.`lastname`, `a`.`phone`, `a`.`phone_mobile`, `c`.`email`,
                `o`.`total_paid` `total`, `po`.`id_branch`, `po`.`is_cod`, `o`.`id_currency`,
                `a`.`company`, `a`.`address1`, `a`.`address2`, `a`.`postcode`, `a`.`city`,
                `po`.`is_carrier`, `po`.`carrier_pickup_point`, `o`.`reference` 
            FROM
                `' . _DB_PREFIX_ . 'orders` `o`
                JOIN `' . _DB_PREFIX_ . 'packetery_order` `po` ON (`po`.`id_order` = `o`.`id_order`)
                JOIN `' . _DB_PREFIX_ . 'customer` `c` ON (`c`.`id_customer` = `o`.`id_customer`)
                JOIN `' . _DB_PREFIX_ . 'address` `a` ON (`a`.`id_address` = `o`.`id_address_delivery`)
            WHERE `o`.`id_order` IN (' . pSQL(implode(',', $ids)) . ')'
        );

        $cnb_rates = null;
        echo "version 6;\r\n";
        echo ";;;;;;;;;;;;;;;;;;;;;;;\r\n";
        foreach ($data as $order) {
            $phone = "";
            foreach (array(
                         'phone',
                         'phone_mobile'
                     ) as $field) {

                if (!empty(trim($order[$field])))
                    $phone = trim($order[$field]);
            }

			$streetName = $order['address1'];
			$streetNumber = (!empty($order['address2'] ? $order['address2'] : null));

            $currency = new Currency($order['id_currency']);
            $precision = $currency->decimals ? 2 : 0;

            /* Convert prices to correct currency */
            $total = round($order['total'], $precision);

            $orderCore = new OrderCore($order['id_order']);
            $weight = $orderCore->getTotalWeight();

            $cod_total = $total;

            echo ';"' . $this->csvEscape(Packetery::ID_PREF_REF === Configuration::get('PACKETERY_ID_PREFERENCE') ? $order['reference'] : $order['id_order']) .
                '";"' . $this->csvEscape($order['firstname']) . '";"' . $this->csvEscape($order['lastname']) .
                '";"' . $this->csvEscape($order['company']) . '";"' . $this->csvEscape($order['email']) .
                '";"' . $this->csvEscape($phone) . '";"' . ($order['is_cod'] == 1 ? $this->csvEscape($cod_total) : "0") . '";"' . $currency->iso_code .
                '";"' . $this->csvEscape($total) . '";"' . $weight . '";"'
                . $this->csvEscape($order['id_branch']) .
                '";"' . Configuration::get('PACKETERY_ESHOP_DOMAIN') . '";;;"'
                . $this->csvEscape(
                    $streetName
                ) . '";' . $this->csvEscape($streetNumber) . ';"' . $this->csvEscape($order['city']) .
                '";"' . $this->csvEscape($order['postcode']) . '";' . $this->csvEscape($order['carrier_pickup_point']) . ';;;;' . "\r\n";
        }
        $db->execute(
            'update `' . _DB_PREFIX_ . 'packetery_order` set exported=1 where id_order in(' . implode(',', $ids) . ')'
        );

        exit();
    }

    /**
     * Display orders grid
     */
    public function display()
    {
        echo '<h2>' . $this->l('Packeta Order Export') . '</h2>';

        $errors = array();
        $have_error = $this->packetery->configuration_errors($errors);
        foreach ($errors as $error) {
            echo "<p style='font-weight: bold; color: red'>" . $error . "</p>";
        }
        if ($have_error) {
            echo "<p style='font-weight: bold;'>" . $this->l(
                    'Please go to Packeta module configuration before You start to use this page.'
                ) . "</p>";
            return;
        } else {
            echo "<br>";
        }

        $db = Db::getInstance();

        echo "<fieldset><legend>" . $this->l('Order List') . "</legend>";
        echo "<form method='post' action='" .
            _MODULE_DIR_ . "packetery/export.php?admindir=" . htmlspecialchars(basename(_PS_ADMIN_DIR_)) . "'>";
        $sql_from = '
            from
            `' . _DB_PREFIX_ . 'orders` o
            join `' . _DB_PREFIX_ . 'packetery_order` po on(po.id_order=o.id_order)
            join `' . _DB_PREFIX_ . 'customer` c on(c.id_customer=o.id_customer)';
        $items = $db->getValue('select count(*) ' . $sql_from);
        $per_page = 50;
        $page = (Tools::getIsset('packetery_page') && $_GET['packetery_page'] > 0 ? (int)$_GET['packetery_page'] : 1);
        $paging = '';
        if ($items > $per_page) {
            $paging .= "<p>" . $this->l('Pages') . ": ";
            for ($i = 1; $i <= ceil($items / $per_page); $i++) {
                if ($i == $page) {
                    $paging .= '<strong>&nbsp;' . $i . '&nbsp;</strong> ';
                } else {
                    $paging .= '<a href="' . $_SERVER['REQUEST_URI'] .
                        '&packetery_page=' . $i . '">&nbsp;' . $i . '&nbsp;</a> ';
                }
            }
            $paging .= "</p>";
        }
        echo $paging;

        /* Table structure and headings */
        echo "<table id='packetery-order-export' class='table'>";
        echo "<tr>
            <th>" . $this->l('Ord.nr.') . "</th>
            <th>" . $this->l('Reference') . "</th>
            <th>" . $this->l('Customer') . "</th>
            <th>" . $this->l('Total Price') . "</th>
            <th>" . $this->l('Order Date') . "</th>
            <th>" . $this->l('Is COD') . "</th>
            <th>" . $this->l('Destination branch') . "</th>
            <th>" . $this->l('Exported') . "</th>
        </tr>";

        /* Select data */
        $orders = $db->executeS(
            'SELECT
            `o`.`id_order`,
            `o`.`reference`,
            `o`.`id_currency`,
            `o`.`id_lang`,
            CONCAT(`c`.`firstname`, " ", `c`.`lastname`) AS `customer`,
            `o`.`total_paid` AS `total`,
            `o`.`date_add` AS `date`,
            `po`.`is_cod`,
            `po`.`name_branch`,
            `po`.`exported`
            ' . $sql_from . ' ORDER BY `o`.`date_add` DESC LIMIT ' . (($page - 1) * $per_page) . ',' . $per_page
        );

        /* Displaying itself */
        foreach ($orders as $order) {
            echo "<tr" . ($order['exported'] == 1 ? " style='background-color: #ddd'" : '') . ">
                <td><input name='packetery_order_id[]' value='{$order['id_order']}' type='checkbox'> {$order['id_order']}</td>
                <td>{$order['reference']}</td>
                <td>{$order['customer']}</td>
                <td align='right'>" . Tools::displayPrice($order['total'], new Currency($order['id_currency'])) . "</td>
                <td>" . Tools::displayDate($order['date'], $order['id_lang'], true) . "</td>
                <td><select name='packetery_order_is_cod[{$order['id_order']}]'>
                    <option value='0'" . ($order['is_cod'] == 0 ? ' selected="selected"' : '') . '>' . $this->l('No') . "</option>
                    <option value='1'" . ($order['is_cod'] == 1 ? ' selected="selected"' : '') . '>' . $this->l('Yes') . "</option>
                </select></td>
                <td>{$order['name_branch']}</td><td>" .
                ($order['exported'] == 1 ? $this->l('Yes') : $this->l('No')) . "</td>
            </tr>";
        }

        echo "</table>";
        echo $paging;
        echo "<br><input type='submit' value='" . htmlspecialchars(
                $this->l('Save COD setting and export selected'),
                ENT_QUOTES
            ) . "' class='button'>";
        echo "<br><br><p>" . $this->l('The exported file can be uploaded in Packeta client area, under Parcels import, section Import parcels file.') . "</p>";
        echo "</fieldset>";
        echo "</form>";
        echo "<script type='text/javascript' src='//www.zasilkovna.cz/api/" .
            Configuration::get('PACKETERY_API_KEY') . "/branch.js?sync_load=1&amp;prestashop=1'></script>";
        /* Check checkbox by clicking on table row */
        echo '
            <script type="text/javascript">
                window.packetery.jQuery(function() {
                    var $ = window.packetery.jQuery;
                    $("#packetery-order-export").find("tr").css({cursor: "pointer"}).end().on("click", "tr", function(e) {
                        if($(e.target).is("input")) return;
                        if($(e.target).is("select")) return;
                        if($(e.target).is("option")) return;
    
                        var i = $(this).find("input");
                        i.attr("checked", !i.is(":checked"));
                    });
                });
            </script>';
    }
}
