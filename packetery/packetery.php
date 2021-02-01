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

class Packetery extends Module
{
    private $supported_countries_trans = array(); /* Used wherever countries with texts are needed */
    private $supported_languages = array('cs', 'sk', 'pl', 'hu', 'ro', 'en');
    private $supported_languages_trans = array(); /* Used wherever languages with texts are needed */
    private $currency_conversion;
    protected $_postErrors = array();
    const CC_PRESTASHOP = 1, CC_CNB = 2, CC_FIXED = 3;

    public static $is_before_carrier = false;

    public function __construct()
    {
        $this->name = 'packetery';
        $this->tab = 'shipping_logistics';
        $this->version = '2.0.4';
        $this->limited_countries = array('cz', 'sk', 'pl', 'hu', 'ro');
        parent::__construct();

        $this->author = $this->l('Packetery, Ltd.');
        $this->displayName = $this->l('Packetery');
        $this->description = $this->l(
            'Offers your customers the option to choose pick-up point in Packetery network,
            and export orders to Packetery system.'
        );

        $this->module_key = 'aa9b6f2b47192e6caae86b500177a861';
        $this->currency_conversion = array(
            self::CC_PRESTASHOP => $this->l('Use PrestaShop\'s currency conversion'),
            self::CC_CNB => $this->l('Use CNB rates with optional margin'),
            self::CC_FIXED => $this->l('Use fixed conversion rate'),
        );

        $this->supported_countries_trans = array(
            'cz' => $this->l('Czech Republic'),
            'sk' => $this->l('Slovakia'),
            'hu' => $this->l('Hungary'),
            'pl' => $this->l('Poland'),
            'ro' => $this->l('Romania')
        );

        $this->supported_languages_trans = array(
            'cs' => $this->l('Czech'),
            'sk' => $this->l('Slovak'),
            'hu' => $this->l('Hungarian'),
            'pl' => $this->l('Polish'),
            'ro' => $this->l('Romanian'),
            'en' => $this->l('English'),
        );

        // This is only used in admin of modules, and we're accessing Packetery API here, so don't do that elsewhere.
        if (self::_isInstalled($this->name) && strpos($_SERVER['REQUEST_URI'], 'tab=AdminModules') !== false) {
            $errors = array();
            $this->configuration_errors($errors);
            foreach ($errors as $error) {
                $this->warning .= $error;
            }
        }
    }

    /**
     * Checks if module is installed
     * @param $module_name
     * @return bool
     */
    public static function _isInstalled($module_name)
    {
        if (method_exists("Packetery", "isInstalled")) {
            return self::isInstalled($module_name);
        } else {
            return true;
        }
    }

    /**
     * Returns available data transport methods (curl / fopen), prioritizes curl
     * @return bool|string
     */
    private static function transportMethod()
    {
        if (extension_loaded('curl')) {
            $have_curl = true;
        }
        if (ini_get('allow_url_fopen')) {
            $have_url_fopen = true;
        }

        if ($have_curl) {
            return 'curl';
        }
        if ($have_url_fopen) {
            return 'fopen';
        }
        return false;
    }

    /**
     * Checks for errors in configuration
     * @param null $error
     * @return bool
     */
    public function configuration_errors(&$error = null)
    {
        $error = array();
        $have_error = false;

        if (!self::transportMethod()) {
            $error[] = $this->l(
                'No way to access Packetery API is available on the web server:
                please allow CURL module or allow_url_fopen setting.'
            );
            $have_error = true;
        }

        $key = Configuration::get('PACKETERY_API_KEY');
        $test = "https://www.zasilkovna.cz/api/$key/test";
        if (!$key) {
            $error[] = $this->l('Packetery API key is not set.');
            $have_error = true;
        } elseif (!$error) {
            if ($this->fetch($test) != 1) {
                $error[] = $this->l('Cannot access Packetery API with specified key. Possibly the API key is wrong.');
                $have_error = true;
            } else {
                $data = Tools::jsonDecode(
                    $this->fetch("https://www.zasilkovna.cz/api/$key/version-check-prestashop?my=" . $this->version)
                );
                if (self::compareVersions($data->version, $this->version) > 0) {
                    $cookie = Context::getContext()->cookie;
                    $def_lang = (int)($cookie->id_lang ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT'));
                    $def_lang_iso = Language::getIsoById($def_lang);
                    $error[] = $this->l('New version of Prestashop Packetery module is available.') . ' '
                        . $data->message->$def_lang_iso;
                }
            }
        }

        return $have_error;
    }

    /**
     * Compares two versions
     * @param $v1
     * @param $v2
     * @return mixed
     */
    public function compareVersions($v1, $v2)
    {
        return array_reduce(
            array_map(
                create_function('$a,$b', 'return $a - $b;'),
                explode('.', $v1),
                explode('.', $v2)
            ),
            create_function('$a,$b', 'return ($a ? $a : $b);')
        );
    }

    /**
     * Module installation script
     * @return bool
     */
    public function install()
    {
        $sql = array();
        $db = Db::getInstance();

        // backup possible old order table
        if (count($db->executeS('show tables like "' . _DB_PREFIX_ . 'packetery_order"')) > 0) {
            $db->execute('rename table `' . _DB_PREFIX_ . 'packetery_order` to `' . _DB_PREFIX_ . 'packetery_order_old`');
            $have_old_table = true;
        } else {
            $have_old_table = false;
        }

        // create tables
        if (!defined('_MYSQL_ENGINE_')) {
            define('_MYSQL_ENGINE_', 'MyISAM');
        }
        include(dirname(__FILE__) . '/sql-install.php');
        foreach ($sql as $s) {
            if (!$db->execute($s)) {
                return false;
            }
        }

        // copy data from old order table
        if ($have_old_table) {
            $fields = array();
            foreach ($db->executeS('show columns from `' . _DB_PREFIX_ . 'packetery_order_old`') as $field) {
                $fields[] = $field['Field'];
            }
            $db->execute(
                'insert into `' . _DB_PREFIX_ . 'packetery_order`(`' . implode('`, `', $fields) . '`)
                select * from `' . _DB_PREFIX_ . 'packetery_order_old`'
            );
            $db->execute('drop table `' . _DB_PREFIX_ . 'packetery_order_old`');
        }

        // module itself and hooks
        if (!parent::install()
            || !$this->registerHook('extraCarrier')
            || !$this->registerHook('updateCarrier')
            || !$this->registerHook('newOrder')
            || !$this->registerHook('header')
            || !$this->registerHook('displayFooter')
            || !$this->registerHook('adminOrder')
        ) {
            return false;
        }

        // for PrestaShop >= 1.4.0.2 there is one-page-checkout, more hooks are required
        $v = explode('.', _PS_VERSION_);
        if (_PS_VERSION_ > '1.4.0' || (array_slice($v, 0, 3) == array(1, 4, 0) && $v[3] >= 2)) {
            if (!$this->registerHook('processCarrier')
                || !$this->registerHook('paymentTop')
            ) {
                return false;
            }
        }

        // optional hooks (allow fail for older versions of PrestaShop)
        $this->registerHook('orderDetailDisplayed');
        $this->registerHook('backOfficeTop');
        $this->registerHook('beforeCarrier');
        $this->registerHook('displayMobileHeader');

        // create admin tab under Orders
        $db->execute(
            'insert into `' . _DB_PREFIX_ . 'tab` (id_parent, class_name, module, position)
            select id_parent, "AdminOrderPacketery", "packetery", coalesce(max(position) + 1, 0)
            from `' . _DB_PREFIX_ . 'tab` pt where id_parent=(select if (id_parent>0, id_parent, id_tab) from `' .
            _DB_PREFIX_ . 'tab` as tp where tp.class_name="AdminOrders") group by id_parent'
        );
        $tab_id = $db->insert_id();

        $tab_name = array('en' => 'Packetery', 'cs' => 'Zásilkovna', 'sk' => 'Zásielkovňa');
        foreach (Language::getLanguages(false) as $language) {
            $db->execute(
                'insert into `' . _DB_PREFIX_ . 'tab_lang` (id_tab, id_lang, name)
                values(' . (int)$tab_id . ', ' . (int)$language['id_lang'] . ', "' .
                pSQL($tab_name[$language['iso_code']] ? $tab_name[$language['iso_code']] : $tab_name['en']) . '")'
            );
        }

        if (!Tab::initAccess($tab_id)) {
            return false;
        }

        return true;
    }

    /**
     * Module uninstallation script
     * @return bool
     */
    public function uninstall()
    {
        foreach (array('PACKETERY_API_KEY', 'PACKETERY_ESHOP_DOMAIN') as $key) {
            Configuration::deleteByName($key);
        }

        // remove admin tab
        $db = Db::getInstance();
        if ($tab_id = $db->getValue(
            'select id_tab from `' . _DB_PREFIX_ . 'tab` where class_name="AdminOrderPacketery"'
        )
        ) {
            $db->execute('delete from `' . _DB_PREFIX_ . 'tab` WHERE id_tab=' . $tab_id);
            $db->execute('delete from `' . _DB_PREFIX_ . 'tab_lang` WHERE id_tab=' . $tab_id);
            $db->execute('delete from `' . _DB_PREFIX_ . 'access` WHERE id_tab=' . $tab_id);
        }

        // mark carriers deleted
        $db->execute(
            'update `' . _DB_PREFIX_ . 'carrier` set deleted=1 where external_module_name="packetery"
            or id_carrier in (select id_carrier from `' . _DB_PREFIX_ . 'packetery_carrier`)'
        );

        // remove our carrier and payment table, keep order table for reinstall
        $db->execute('drop table if exists `' . _DB_PREFIX_ . 'packetery_carrier`');
        $db->execute('drop table if exists `' . _DB_PREFIX_ . 'packetery_payment`');
        $db->execute('drop table if exists `' . _DB_PREFIX_ . 'packetery_address_delivery`');

        // module itself and hooks
        if (!parent::uninstall()
            || !$this->unregisterHook('beforeCarrier')
            || !$this->unregisterHook('extraCarrier')
            || !$this->unregisterHook('updateCarrier')
            || !$this->unregisterHook('newOrder')
            || !$this->unregisterHook('header')
            || !$this->unregisterHook('displayFooter')
            || !$this->unregisterHook('processCarrier')
            || !$this->unregisterHook('orderDetailDisplayed')
            || !$this->unregisterHook('adminOrder')
            || !$this->unregisterHook('paymentTop')
            || !$this->unregisterHook('backOfficeTop')
        ) {
            return false;
        }

        return true;
    }

    /**
     * Processes data on form save
     */
    private function cConfigurationPost()
    {
        // leave the function if nothing is set
        if (
            !Tools::getIsset('packetery_api_key') &&
            !Tools::getIsset('packetery_eshop_domain') &&
            !Tools::getIsset('packetery_forced_country') &&
            !Tools::getIsset('packetery_forced_lang')
        ) {
            return;
        }

        // save API KEY if changed
        if (Tools::getIsset('packetery_api_key') && Tools::getValue('packetery_api_key')) {
            if (trim(Tools::getValue('packetery_api_key')) != Configuration::get('PACKETERY_API_KEY')) {
                Configuration::updateValue('PACKETERY_API_KEY', trim(Tools::getValue('packetery_api_key')));
                @clearstatcache();
            }
        }

        // save e-shop domain
        if (Tools::getIsset('packetery_eshop_domain') && Tools::getValue('packetery_eshop_domain')) {
            Configuration::updateValue('PACKETERY_ESHOP_DOMAIN', trim(Tools::getValue('packetery_eshop_domain')));
        }

        // save forced country
        if (Tools::getIsset('packetery_forced_country')) {
            Configuration::updateValue('PACKETERY_FORCED_COUNTRY', implode(',', Tools::getValue('packetery_forced_country')));
        } else {
            Configuration::updateValue('PACKETERY_FORCED_COUNTRY', '');
        }

        // save forced language
        if (Tools::getIsset('packetery_forced_lang')) {
            Configuration::updateValue('PACKETERY_FORCED_LANG', trim(Tools::getValue('packetery_forced_lang')));
        } else {
            Configuration::updateValue('PACKETERY_FORCED_LANG', '');
        }
    }

    /**
     * Processes data on "add carrier" form save
     */
    public function cAddCarrierPost()
    {
        $db = Db::getInstance();
        /* Check if the button was submitted, all *Post functions are being called everytime */
        if (!Tools::getIsset('packetery_add_carrier') || !Tools::getValue('packetery_add_carrier')) {
            return;
        }

        $carrier = new Carrier();

        $carrier->name = Tools::getValue('packetery_carrier_name');
        $carrier->active = true;
        $carrier->shipping_method = defined('Carrier::SHIPPING_METHOD_WEIGHT') ? Carrier::SHIPPING_METHOD_WEIGHT : 1;
        $carrier->deleted = 0;

        $carrier->range_behavior = true; // true disables this carrier if outside weight range
        $carrier->is_module = true;
        $carrier->external_module_name = "packetery";
        $carrier->need_range = true;

        foreach (Language::getLanguages(true) as $language) {
            if (Tools::getIsset('delay_' . $language['id_lang']) && Tools::getValue('delay_' . $language['id_lang'])) {
                $carrier->delay[$language['id_lang']] = Tools::getValue('delay_' . $language['id_lang']);
            }
        }

        if (!$carrier->add()) {
            return false;
        }

        $country = "";
        $countries = Tools::getValue('packetery_carrier_country');
        foreach ($countries as $key => $countryCode) {
            $country .= $countryCode;
            if ($key != count($countries) - 1) {
                $country .= ',';
            }
        }

        // Saves carrier countries, if not set, use cz and sk
        $db->execute(
            'insert into `' . _DB_PREFIX_ . 'packetery_carrier` set id_carrier=' . ((int)$carrier->id) .
            ', country="' . pSQL($country ? $country : 'cz,sk') . '", is_cod=' .
            (Tools::getIsset('packetery_carrier_is_cod') ? (int)Tools::getValue('packetery_carrier_is_cod') : 0)
        );

        // Add carrier to all customer groups
        foreach (Group::getGroups(true) as $group) {
            $db->autoExecute(
                _DB_PREFIX_ . 'carrier_group',
                array(
                    'id_carrier' => (int)$carrier->id,
                    'id_group' => (int)$group['id_group']
                ),
                'INSERT'
            );
        }

        // Set default weight range
        $rangeWeight = new RangeWeight();
        $rangeWeight->id_carrier = $carrier->id;
        $rangeWeight->delimiter1 = '0';
        $rangeWeight->delimiter2 = '5';
        $rangeWeight->add();

        // Set default price range
        $rangePrice = new RangePrice();
        $rangePrice->id_carrier = $carrier->id;
        $rangePrice->delimiter1 = '0';
        $rangePrice->delimiter2 = '1000000';
        $rangePrice->add();

        // SAve additionally carrier relationships
        $zones = Zone::getZones(true);
        foreach ($zones as $zone) {
            $db->autoExecute(
                _DB_PREFIX_ . 'carrier_zone',
                array(
                    'id_carrier' => (int)$carrier->id,
                    'id_zone' => (int)$zone['id_zone']
                ),
                'INSERT'
            );
            $db->autoExecuteWithNullValues(
                _DB_PREFIX_ . 'delivery',
                array(
                    'id_carrier' => (int)$carrier->id,
                    'id_range_price' => (int)$rangePrice->id,
                    'id_range_weight' => null,
                    'id_zone' => (int)$zone['id_zone'],
                    'price' => '0'
                ),
                'INSERT'
            );
            $db->autoExecuteWithNullValues(
                _DB_PREFIX_ . 'delivery',
                array(
                    'id_carrier' => (int)$carrier->id,
                    'id_range_price' => null,
                    'id_range_weight' => (int)$rangeWeight->id,
                    'id_zone' => (int)$zone['id_zone'],
                    'price' => '0'
                ),
                'INSERT'
            );
        }
    }

    /**
     * Outputs html for configuration form
     * @return string
     */
    private function cConfiguration()
    {
        $html = "";
        $html .= "<fieldset><legend>" . $this->l('Module Configuration') . "</legend>";
        $html .= "<form method='post'>";

        $html .= "<label>" . $this->l('API key') . ": </label>";
        $html .= "<div class='margin-form'><input type='text' name='packetery_api_key' value='" .
            htmlspecialchars(Configuration::get('PACKETERY_API_KEY'), ENT_QUOTES) . "' /></div>";
        $html .= "<div class='clear'></div>";

        $html .= "<label>" . $this->l('Sender label') . ": </label>";
        $html .= "<div class='margin-form'><input type='text' name='packetery_eshop_domain' value='" .
            htmlspecialchars(Configuration::get('PACKETERY_ESHOP_DOMAIN'), ENT_QUOTES) . "' /><p>" .
            $this->l('If you\'re using one Packetery account for multiple e-shops, enter the domain of current one here, so that your customers are properly informed about what package they are receiving.')
            . "</p></div>";
        $html .= "<div class='clear'></div>";

        $defCountry = Configuration::get('PACKETERY_FORCED_COUNTRY');
        $html .= "<label>" . $this->l('Force Country') . ": </label>";
        $html .= "<div class='margin-form'>
            <select name='packetery_forced_country[]' multiple style='width: 180px; ' size='3'>";

        foreach ($this->supported_countries_trans as $code => $country) {
            if (strpos($defCountry, $code) !== false) {
                $html .= "<option value='$code' selected>$country</option>\n";
            } else {
                $html .= "<option value='$code'>$country</option>\n";
            }
        }
        $html .= "</select></div>";
        $html .= '<div class="clear"></div>';

        $defLang = Configuration::get('PACKETERY_FORCED_LANG');
        $html .= "<label>" . $this->l('Force Language') . ": </label>";
        $html .= "<div class='margin-form'>
            <select name='packetery_forced_lang' style='width: 180px; ' size='3'>";

        foreach (array(
                     '' => $this->l('Use e-shop language'),
                 ) + $this->supported_languages_trans as $code => $lang) {
            if (empty($defLang) && $code == '') {
                $html .= "<option value='$code' selected>$lang</option>\n";
            } else if (strpos($defLang, $code) !== false) {
                $html .= "<option value='$code' selected>$lang</option>\n";
            } else {
                $html .= "<option value='$code'>$lang</option>\n";
            }
        }
        $html .= "</select></div>";
        $html .= '<div class="clear"></div>';

        $html .= "<div class='margin-form'><input class='button' type='submit' value='" .
            htmlspecialchars($this->l('Save'), ENT_QUOTES) . "'  /></div>";

        $html .= "</form>";
        $html .= "</fieldset>";

        return $html;
    }

    /**
     * Outputs html for "add carrier" form
     * @return string
     */
    private function cAddCarrier()
    {
        $html = "";
        $html .= "<fieldset><legend>" . $this->l('Add Carrier') . "</legend>";

        $html .= "<form method='post'>";
        $html .= "<input type='hidden' name='packetery_add_carrier' value='1' />";

        $html .= "<label>" . $this->l('Carrier Name') . ": </label>";
        $html .= "<div class='margin-form'><input type='text' name='packetery_carrier_name' size='41' value='" .
            htmlspecialchars($this->l('Personal pick-up – Packetery'), ENT_QUOTES) . "' /></div>";
        $html .= "<div class='clear'></div>";

        $html .= "<label>" . $this->l('Delay') . ": </label>";
        $html .= '<div class="margin-form">';

        $cookie = Context::getContext()->cookie;
        $def_lang = (int)($cookie->id_lang ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT'));

        /**
        * Je záměrně natvrdo, jedná se pouze o defaultní texty, pro které je potřeba mít všechny 3 verze připravené,
        * aby bylo možné mezi nimi přepínat
        */
        $delay = array(
            'en' => '1-3 days when in stock',
            'cs' => "Do 1-3 dní je-li skladem",
            'sk' => "Do 1-3 dní ak je skladom"
        );

        foreach (Language::getLanguages(false) as $language) {
            if ($def_lang == $language['id_lang']) {
                $def_lang_code = $language['iso_code'];
            }
            $html .= '<div id="delay_' . $language['id_lang'] . '" style="display: ' .
                ($language['id_lang'] == 1 ? 'block' : 'none') .
                '; float: left;"><input type="text" size="41" maxlength="128" name="delay_' .
                $language['id_lang'] . '" value="' .
                htmlspecialchars(
                    $delay[$language['iso_code']] ? $delay[$language['iso_code']] : $delay['en'],
                    ENT_QUOTES
                ) . '" /></div>';
        }

        $html .= $this->displayFlags(Language::getLanguages(false), $def_lang, 'delay', 'delay', true);
        $html .= '<p class="clear"></p></div>';
        $html .= "<div class='clear'></div>";
        $html .= "
            <script type='text/javascript'> 
                changeLanguage('delay', 'delay', $def_lang, '$def_lang_code'); 
            </script>";

        $html .= "<label>" . $this->l('Countries') . ": </label>";
        $html .= "<div class='margin-form'>
            <select name='packetery_carrier_country[]' multiple style='width: 180px; ' size='3'>";

        foreach ($this->supported_countries_trans as $code => $country) {
            $html .= "<option value='$code'>$country</option>\n";
        }
        $html .= "</select>";
        $html .= "<p class='clear'>" . $this->l('You can select one or more countries by using the Ctrl key. Only branches in selected countries will be shown in this shipping method – you can e.g. set different price based on country.')
            . "</p>";
        $html .= "</div>";
        $html .= "<div class='clear'></div>";

        $html .= "<label for='packetery_carrier_is_cod'>" . $this->l('Is COD') . ": </label>";
        $html .= "<div class='margin-form'>
            <input type='checkbox' id='packetery_carrier_is_cod' name='packetery_carrier_is_cod' value='1'><p>" .
            $this->l('When exporting order with this carrier, the order total will be put as COD.') . "</div>";
        $html .= "<div class='clear'></div>";

        $html .= "<div class='margin-form'><input class='button' type='submit' value='" .
            htmlspecialchars($this->l('Add'), ENT_QUOTES) . "' /></div>";

        $html .= "</form>";

        $html .= "</fieldset>";

        return $html;
    }

    /**
     * Save changed cod for carriers in list
     */
    private function cListCarriersPost()
    {
        if (Tools::getIsset('packetery_remove_carrier') && Tools::getValue('packetery_remove_carrier')) {
            $db = Db::getInstance();
            $db->execute(
                'update `' . _DB_PREFIX_ .
                'carrier` set deleted=1 where external_module_name="packetery"
                and id_carrier=' . ((int)Tools::getValue('packetery_remove_carrier'))
            );
        }
    }

    /**
     * Outputs html for carriers list
     * @return string
     */
    private function cListCarriers()
    {
        $db = Db::getInstance();
        $html = "";
        $html .= "<fieldset><legend>" . $this->l('Carrier List') . "</legend>";
        if ($list = $db->executeS(
            'select c.id_carrier, c.name, pc.country, pc.is_cod
            from `' . _DB_PREFIX_ . 'carrier` c join `' . _DB_PREFIX_ . 'packetery_carrier` pc
            on(pc.id_carrier=c.id_carrier) where c.deleted=0'
        )
        ) {
            $html .= "<table class='table' cellspacing='0'>";
            $html .= "<tr><th>" . $this->l('Carrier Name') . "</th><th>" . $this->l('Countries') . "</th><th>" . $this->l('Is COD') . "</th><th>" . $this->l('Action') .
                "</th></tr>";

            foreach ($list as $carrier) {
                $html .= "<tr><td>$carrier[name]</td><td>$carrier[country]</td><td>" .
                    ($carrier['is_cod'] == 1 ? $this->l('Yes') : $this->l('No')) . "</td><td><form method='post'>
                    <input type='hidden' name='packetery_remove_carrier' value='$carrier[id_carrier]'>
                    <input type='submit' class='button' value='" . htmlspecialchars($this->l('Remove'), ENT_QUOTES) .
                    "'></form></td></tr>";
            }
            $html .= "</table>";
            $html .= "<p>" . $this->l('If you want to set price, use standard PrestaShop functions (see Shipping in top menu).') . "</p>";
        } else {
            $html .= "<p>" . $this->l('There are no carriers created yet. Please create some below.') . "</p>";
        }
        $html .= "</fieldset>";
        return $html;
    }

    /**
     * Processes change of COD in payments list
     */
    private function cListPaymentsPost()
    {
        if (Tools::getIsset('packetery_payment_module') && Tools::getValue('packetery_payment_module') && Tools::getValue('packetery_payment_submit')) {
            $db = Db::getInstance();
            if ($db->getValue(
                    'select 1 from `' . _DB_PREFIX_ . 'packetery_payment` where module_name="' .
                    pSQL(Tools::getValue('packetery_payment_module')) . '"'
                ) == 1
            ) {
                $db->execute(
                    'update `' . _DB_PREFIX_ . 'packetery_payment` set is_cod=' .
                    ((int)Tools::getValue('packetery_payment_is_cod')) . ' where module_name="' .
                    pSQL(Tools::getValue('packetery_payment_module')) . '"'
                );
            } else {
                $db->execute(
                    'insert into `' . _DB_PREFIX_ . 'packetery_payment` set is_cod=' .
                    ((int)Tools::getValue('packetery_payment_is_cod')) . ', module_name="' .
                    pSQL(Tools::getValue('packetery_payment_module')) . '"'
                );
            }
        }
    }

    /**
     * Outputs HTML for payments list
     * @return string
     */
    private function cListPayments()
    {
        $db = Db::getInstance();
        $html = "";
        $html .= "<fieldset><legend>" . $this->l('Payment List') . "</legend>";
        $html .= "<table class='table' cellspacing='0'>";
        $html .= "<tr><th>" . $this->l('Module') . "</th><th>" . $this->l('Is COD') .
            "</th><th>" . $this->l('Action') . "</th></tr>";
        $modules = $db->executeS(
            'select distinct m.name
            from `' . _DB_PREFIX_ . 'module` m
            left join `' . _DB_PREFIX_ . 'hook_module` hm on(hm.id_module=m.id_module)
            left join `' . _DB_PREFIX_ . 'hook` h on(hm.id_hook=h.id_hook)
            WHERE h.name in ("payment", "displayPayment", "displayPaymentReturn")
            AND m.active=1
        '
        );
        foreach ($modules as $module) {
            $instance = Module::getInstanceByName($module['name']);
            $is_cod = ($db->getValue(
                    'select is_cod from `' . _DB_PREFIX_ . 'packetery_payment`
                where module_name="' . pSQL($module['name']) . '"'
                ) == 1);
            $html .= "<tr><td>$instance->displayName</td><td>" . ($is_cod == 1 ? $this->l('Yes') : $this->l('No')) .
                "</td><td><form method='post'><input type='hidden' name='packetery_payment_module' value='" .
                htmlspecialchars($module['name'], ENT_QUOTES) . "' />
                <input type='hidden' name='packetery_payment_is_cod' value='" . (1 - $is_cod) . "' />
                <input type='submit' name='packetery_payment_submit' class='button' value='" .
                htmlspecialchars(
                    $is_cod ? $this->l('Clear COD setting') : $this->l('Set COD setting'),
                    ENT_QUOTES
                ) . "'></form></td></tr>";
        }
        $html .= "</table>";
        $html .= "<p>" . $this->l('When exporting order paid using module which has COD setting, the order total will be put as COD.') . "</p>";
        $html .= "<p>" . $this->l('Changes will not affect existing orders, only those created after your changes.') . "</p>";
        $html .= "</fieldset>";
        return $html;
    }

    /**
     * Processes adress delivery form
     */
    private function cListAddressDeliveryCarriersPost()
    {
        if (!Tools::getIsset('address_delivery_carriers') || !Tools::getValue('address_delivery_carriers')) {
            return;
        }

        $data = (Tools::getIsset("data") && is_array(Tools::getValue("data")) ? Tools::getValue("data") : array());
        $db = Db::getInstance();
        $address_deliveries = self::addressDeliveries();
        foreach ($data as $id_carrier => $attr) {
            if ($attr['id_branch']) {
                $a = $address_deliveries[$attr['id_branch']];
                $db->execute(
                    'insert into `' . _DB_PREFIX_ . 'packetery_address_delivery`(id_carrier, id_branch, name_branch,
                     currency_branch, is_cod) values(' . ((int)$id_carrier) . ', ' . ((int)$attr['id_branch']) .
                    ', "' . pSQL($a->name) . '", "' . pSQL($a->currency) . '", ' . ((int)$attr['is_cod']) . ')
                    on duplicate key update id_branch=' . ((int)$attr['id_branch']) . ',
                    is_cod=' . ((int)$attr['is_cod']) . ', name_branch="' . pSQL($a->name) . '",
                    currency_branch="' . pSQL($a->currency) . '"'
                );
            } else {
                $db->execute(
                    'delete from `' . _DB_PREFIX_ . 'packetery_address_delivery` where id_carrier=' . ((int)$id_carrier)
                );
            }
        }
    }

    /**
     * Öutputs html for address delivery form
     * @return string
     */
    private function cListAddressDeliveryCarriers()
    {
        $db = Db::getInstance();
        $html = "";
        $html .= "<fieldset><legend>" . $this->l('Address Delivery Carriers List') . "</legend>";
        $html .= "<form method='post'>";
        $html .= "<input type='hidden' name='address_delivery_carriers' value='1'>";
        $html .= "<table class='table' cellspacing='0'>";
        $html .= "<tr><th>" . $this->l('Carrier') . "</th><th>" . $this->l('Is Address Delivery via Packetery') .
            "</th><th>" . $this->l('Is COD') . "</th></tr>";

        $carriers = $db->executeS(
            'select pad.*, c.name, c.id_carrier
            from `' . _DB_PREFIX_ . 'carrier` c
            LEFT JOIN `' . _DB_PREFIX_ . 'packetery_address_delivery` pad using(id_carrier)
            WHERE 
              c.external_module_name<>"packetery"
              and c.id_carrier not in (select id_carrier from `' . _DB_PREFIX_ . 'packetery_carrier`)
              and c.deleted=0
              and c.active=1
        '
        );

        foreach ($carriers as $carrier) {
            $html .= "<tr><td>" . ($carrier['name'] != "0" ? $carrier['name'] : Configuration::get('PS_SHOP_NAME')) .
                "</td><td><select name='data[$carrier[id_carrier]][id_branch]'>";
            foreach ((
                array(
                    '' => (object)array(
                        'name' => '–– ' . Tools::strtolower($this->l('No')) . ' ––')
                ) + self::addressDeliveries()
            ) as $k => $v) {
                $html .= "<option value='$k'" .
                    ($carrier['id_branch'] == $k ? " selected" : "") . ">$v->name</option>\n";
            }
            $html .= "</select></td><td><select name='data[$carrier[id_carrier]][is_cod]'>";
            foreach (array(
                         $this->l('No'),
                         $this->l('Yes')
                     ) as $k => $v) {
                $html .= "<option value='$k'" . ($carrier['is_cod'] == $k ? " selected" : "") . ">$v</option>\n";
            }
            $html .= "</select></td></tr>";
        }
        $html .= "</table>";
        $html .= "<input type='submit' class='button' value='" .
            htmlspecialchars($this->l('Save settings'), ENT_QUOTES) . "'>";
        $html .= "<p>" . $this->l(
                'Changes will not affect existing orders, only those created after your changes.'
            ) . "</p>";
        $html .= "</fieldset>";
        return $html;
    }

    /**
     * Primary method for Packetery settings page in administration
     * @return string
     */
    public function getContent()
    {
        /* Update list of carriers for address delivery if not up to date */
        $this->ensureUpdatedAPI();

        /* Process all forms */
        $this->cConfigurationPost();
        $this->cAddCarrierPost();
        $this->cListCarriersPost();
        $this->cListPaymentsPost();
        $this->cListAddressDeliveryCarriersPost();

        $html = '';
        $html .= '<h2>' . $this->l('Packetery Shipping Module Settings') . '</h2>';
        $errors = array();

        /* Display configuration errors */
        $this->configuration_errors($errors);
        if ($errors) {
            $html .= "<fieldset><legend>" . $this->l('Configuration Errors') . "</legend>";
            foreach ($errors as $error) {
                $html .= "<p style='font-weight: bold; color: red'>" . $error . "</p>";
            }
            $html .= "</fieldset>";
        }

        /* Output all sections */
        $html .= "<br>";
        $html .= $this->cConfiguration();
        $html .= "<br>";
        $html .= $this->cListCarriers();
        $html .= "<br>";
        $html .= $this->cAddCarrier();
        $html .= "<br>";
        $html .= $this->cListAddressDeliveryCarriers();
        $html .= "<br>";
        $html .= $this->cListPayments();

        return $html;
    }

    /**
     * Hook call
     * @param $params
     * @return string
     */
    public function hookBeforeCarrier($params)
    {
        self::$is_before_carrier = true;
        $res = $this->hookExtraCarrier($params);
        self::$is_before_carrier = false;
        return $res;
    }

    /**
     * Called from hook to display Packetery widget button and some extra data to each Packetery carrier
     * @param $params
     * @return string
     */
    public function hookExtraCarrier($params)
    {
        $db = Db::getInstance();

        /* Check if the hooks are active */
        if ($db->getValue(
                'select 1 from `' . _DB_PREFIX_ . 'hook` where name in ("beforeCarrier", "displayBeforeCarrier")'
            ) == 1 && !self::$is_before_carrier
        ) {
            return "";
        }

        $address = new AddressCore($params['cart']->id_address_delivery);
        $country_iso = CountryCore::getIsoById($address->id_country)    ;

        $carrier_data = array();
        $carrier_ids = array();
        foreach ($db->executeS(
            'select pc.id_carrier, pc.country from `' .
            _DB_PREFIX_ . 'packetery_carrier` pc join `' .
            _DB_PREFIX_ . 'carrier` c using(id_carrier) where c.deleted=0'
        ) as $carrier) {

            $carrier_ids[] = $carrier['id_carrier']; //9 10
            $carrier_data[$carrier['id_carrier']] = array(
                'country' => $carrier['country']
            );
        }

        $forcedCountry = Configuration::get('PACKETERY_FORCED_COUNTRY');
        $forcedLang = Configuration::get('PACKETERY_FORCED_LANG');
        $api_key = Configuration::get('PACKETERY_API_KEY');

        $json = Tools::jsonEncode($carrier_data);

        /* Get language from cart, global $language updates weirdly */
        $language = new LanguageCore($this->context->cart->id_lang);

        /* Check if forced country is set, if not, use user country */
        if ($forcedCountry) {
            $country = $forcedCountry;
        } else {
            $country = strtolower($country_iso);
        }
        $country = strtolower($country);

        /* Use user's language if supported, english otherwise */
        $lang = in_array($language->iso_code, $this->supported_languages) ? $language->iso_code : 'en';

        /* Use forced lang if set */
        if ($forcedLang) {
            $lang = $forcedLang;
        }

        /* Prepare langs to be used by JS */
        $mod_dir = _MODULE_DIR_;
        $must_select_point_text = $this->l('You must select a pick-up point before continuing');
        $select_point_text = $this->l('Please select a pick-up point');
        $selected_point_text = $this->l('Selected pick-up point');
        $invalid_country_text = $this->l('Selected carrier is not available for your country.');
        $module_version = $this->version;

        $lang = strtolower($lang);

        /* Define some JS variables and inicialize widget */
        return <<< END
        <script type="text/javascript">
            var carrier_data = $json;           
            var api_key = "$api_key";           
            var country = "$country";
            var lang = "$lang";            
            var module_dir = "$mod_dir";
            var selected_text = "$selected_point_text"; 
            var select_text = "$select_point_text";
            var must_select_text = "$must_select_point_text";
            var invalid_country_text = "$invalid_country_text";
            var module_version = "$module_version";
            
            $(function(){
                $("input.delivery_option_radio").on('change', function(){
                    initializePacketaWidget();
                });
                initializePacketaWidget();
            });
        </script>
END;
    }

    /**
     * Hook call - saves additional data after an order has been created.
     * @param $params
     */
    public function hookNewOrder($params)
    {
        $db = DB::getInstance();
        if ($packetery_carrier = $db->getRow(
            'select is_cod from `' . _DB_PREFIX_ . 'packetery_carrier`
            where id_carrier=' . ((int)$params['order']->id_carrier))) {
            // branch
        }
        elseif ($packetery_carrier = $db->getRow(
            'select is_cod, id_branch, name_branch, currency_branch
            from `' . _DB_PREFIX_ . 'packetery_address_delivery`
            where id_carrier=' . ((int)$params['order']->id_carrier))) {
            // address
            $db->execute(
                'insert ignore into `' . _DB_PREFIX_ . 'packetery_order` set id_cart=' . ((int)$params['cart']->id)
            );
            $db->execute(
                'update `' . _DB_PREFIX_ . 'packetery_order` set id_branch=' . ((int)$packetery_carrier['id_branch']) .
                ', name_branch="' . pSQL($packetery_carrier['name_branch']) . '", currency_branch="' .
                pSQL($packetery_carrier['currency_branch']) . '" where id_cart=' . ((int)$params['cart']->id)
            );
        }
        else {
            return;
        }

        /* Save id order. update cart to packetery_order */
        $db->execute(
            'update `' . _DB_PREFIX_ . 'packetery_order` set id_order=' . ((int)$params['order']->id) .
            ' where id_cart=' . ((int)$params['cart']->id)
        );

        $carrier_is_cod = ($packetery_carrier['is_cod'] == 1);
        $payment_is_cod = ($db->getValue(
                'select is_cod from `' . _DB_PREFIX_ . 'packetery_payment` where module_name="' .
                pSQL($params['order']->module) . '"'
            ) == 1);

        /* If payment or carrier is set as cod - set order as cod */
        if ($carrier_is_cod || $payment_is_cod) {
            $db->execute(
                'update `' . _DB_PREFIX_ . 'packetery_order` set is_cod=1 where id_order=' . ((int)$params['order']->id)
            );
        }
    }

    /**
     * Output additional carrier info in admin order detail
     * @param $params
     * @return string
     */
    public function hookAdminOrder($params)
    {
        if (!($res = Db::getInstance()->getRow(
            'SELECT o.name_branch FROM `' . _DB_PREFIX_ . 'packetery_order` o
            WHERE o.id_order = ' . ((int)$params['id_order'])
        ))
        ) {
            return "";
        }

        return "<p>" . sprintf(
                $this->l('Selected packetery branch: %s'),
                "<strong>" . $res['name_branch'] . "</strong>"
            ) . "</p>";
    }

    /**
     * Output additional carrier info in frontend order detail
     * @param $params
     * @return string|void
     */
    public function hookOrderDetailDisplayed($params)
    {
        if (!($res = Db::getInstance()->getRow(
            'SELECT o.name_branch FROM `' . _DB_PREFIX_ . 'packetery_order` o WHERE o.id_order = ' .
            ((int)$params['order']->id)
        ))
        ) {
            return;
        }

        return "<p>" . sprintf(
                $this->l('Selected packetery branch: %s'),
                "<strong>" . $res['name_branch'] . "</strong>"
            ) . "</p>";
    }


    /**
     * Sets new carrier ID after update
     * @param $params
     */
    public function hookUpdateCarrier($params)
    {
        if ($params['id_carrier'] != $params['carrier']->id) {
            Db::getInstance()->execute(
                'update `' . _DB_PREFIX_ . 'packetery_carrier`
                set id_carrier=' . ((int)$params['carrier']->id) . '
                where id_carrier=' . ((int)$params['id_carrier'])
            );
        }
    }

    /**
     * Hook call, display header on mobile
     * @param $params
     * @return string
     */
    public function hookDisplayMobileHeader($params)
    {
        return $this->hookHeader($params);
    }

    /**
     * Hook call, display header - adds js files
     * @param $params
     * @return string
     */
    public function hookHeader($params)
    {
        return '
        <script type="text/javascript" src="https://widget.packeta.com/www/js/library.js"></script>
        <script type="text/javascript" src="' . _MODULE_DIR_ . 'packetery/views/js/front.js"></script>       
        <link rel="stylesheet" href="' . _MODULE_DIR_ . 'packetery/views/css/packetery.css" />
        ';
    }

    /**
     * get data from packetery api
     * @param $url
     * @return bool|mixed|string
     */
    private function fetch($url)
    {
        $transportMethod = self::transportMethod();
        if (Tools::substr($transportMethod, -1) == 's') {
            $url = preg_replace('/^http:/', 'https:', $url);
            $transportMethod = Tools::substr($transportMethod, 0, -1);
            $ssl = true;
        } else {
            $ssl = false;
        }

        switch ($transportMethod) {
            case 'curl':
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                curl_setopt($ch, CURLOPT_AUTOREFERER, false);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                if ($ssl) {
                    curl_setopt($ch, CURLOPT_CAINFO, _MODULE_DIR_ . "packetery/godaddy.crt");
                }
                $body = curl_exec($ch);
                if (curl_errno($ch) > 0) {
                    return false;
                }
                return $body;
            case 'fopen':
                if (function_exists('stream_context_create')) {
                    // set longer timeout here, because we cannot detect timeout errors
                    $ctx = stream_context_create(
                        array(
                            'http' => array(
                                'timeout' => 60
                            ),
                            'ssl' => array(
                                'cafile' => _MODULE_DIR_ . "packetery/godaddy.crt",
                                'verify_peer' => true
                            )
                        )
                    );
                    return Tools::file_get_contents($url, false, $ctx);
                }
                return Tools::file_get_contents($url);

            default:
                return false;
        }
    }

    /*
      Try to update Branch XML file once a day. If it's older than five days and still
      can't update, then remove it - the e-shop owner must solve it.
    */
    private function ensureUpdatedAPI()
    {
        $key = Configuration::get('PACKETERY_API_KEY');
        $files = array(
            _PS_MODULE_DIR_ . "packetery/address-delivery.xml" =>
                "https://www.zasilkovna.cz/api/v4/$key/branch.xml"
        );

        foreach ($files as $local => $remote) {
            if (date("d.m.Y", @filemtime($local)) != date("d.m.Y") && (!file_exists($local) || date("H") >= 1)) {
                if ($this->configuration_errors()) {
                    if (file_exists($local)) {
                        $error_count = @Tools::file_get_contents($local . ".error");
                        if ($error_count > 5) {
                            unlink($local);
                        } else {
                            touch($local);
                        }
                        @file_put_contents($local . ".error", $error_count + 1);
                    }
                    return;
                }

                $data = $this->parseBranches($remote);

                file_put_contents($local, $data);
                @unlink($local . ".error");
            }
        }
    }

    /**
     * Parses through carriers list and selects address deliveries only
     * @param $branch_url
     * @return bool|mixed
     */
    public function parseBranches($branch_url)
    {
        ignore_user_abort(true);
        $module = new Packetery();
        if ($response = Tools::file_get_contents($branch_url)) {
            if (Tools::strpos($response, 'invalid API key') == false) {
                $xml = simplexml_load_string($response);

                $toRemove = [];
                foreach ($xml->carriers->carrier as $k => $carrier) {
                    if ("$carrier->pickupPoints" == "true") {
                        $dom = dom_import_simplexml($carrier);
                        $toRemove[] = $dom;
                    }
                }

                $dom = dom_import_simplexml($xml->branches);
                $toRemove[] = $dom;

                foreach ($toRemove as $row) {
                    $row->parentNode->removeChild($row);
                }

                return $xml->asXML();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $id_carrier
     * @return array|bool|null|object
     */
    public function getPacketeryCarrier($id_carrier)
    {
        $db = Db::getInstance();
        return $db->getRow("SELECT * FROM " . _DB_PREFIX_ . "packetery_carrier WHERE id_carrier='".pSQL($id_carrier)."'");

    }

    /**
     * @return array
     */
    public static function addressDeliveries()
    {
        $res = array();
        $fn = _PS_MODULE_DIR_ . "packetery/address-delivery.xml";
        if (function_exists("simplexml_load_file") && file_exists($fn)) {
            $xml = simplexml_load_file($fn);
            foreach ($xml->carriers->carrier as $branch) {
                $res[(string)$branch->id] = (object)array(
                    'name' => (string)$branch->name,
                    'currency' => (string)$branch->currency,
                );
            }
            if (function_exists('mb_convert_encoding')) {
                $fn = create_function(
                    '$a,$b',
                    'return strcmp(mb_convert_encoding($a->name, "ascii", "utf-8"),
                    mb_convert_encoding($b->name, "ascii", "utf-8"));'
                );
            } else {
                $fn = create_function(
                    '$a,$b',
                    'return strcmp($a->name, $b->name);'
                );
            }
            uasort($res, $fn);
        }
        return $res;
    }

    /**
     * invalidates carrier if it's packetery and doesn't have a selected branch
     * @param $params
     */
    public function hookPaymentTop($params)
    {
        $db = Db::getInstance();
        $is_packetery_carrier = ($db->getValue(
                'select 1 from `' . _DB_PREFIX_ . 'packetery_carrier`
            where id_carrier=' . ((int)$params['cart']->id_carrier)
            ) == 1);
        $has_selected_branch = ($db->getValue(
                'select id_branch from `' . _DB_PREFIX_ . 'packetery_order` where id_cart=' . ((int)$params['cart']->id)
            ) > 0);

        if ($is_packetery_carrier && !$has_selected_branch) {
            $params['cart']->id_carrier = 0;
        }
    }

    /**
     * Hook call in a hook call
     * @param $params
     */
    public function hookProcessCarrier($params)
    {
        $this->hookPaymentTop($params);
    }

    /**
     * display existing errors, TODO: delete?
     * @param $params
     * @return string
     */
    public function hookBackOfficeTop($params)
    {
        $cookie = Context::getContext()->cookie;
        if ($cookie->packetery_seen_warning < 3) {
            $cookie->packetery_seen_warning++;
            $errors = array();
            if (!$this->configuration_errors($errors) && count($errors) > 0) {
                return "<div style='float: right; width: 400px; font-weight: bold; color: red'>" . $errors[0] .
                    "</div>";
            }
        }
    }
}