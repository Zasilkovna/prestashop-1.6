[Návod v češtině](#modul-pro-prestashop-16)

# Module for PrestaShop 1.6

### Download link
[Download version 2.0.5](https://github.com/Zasilkovna/prestashop-1.6/releases/download/2.0.5/prestashop-1.6-modul-2.0.5.zip)

## System requirements
For installation of Prestashop 1.6.x there is required to install following components:
- System: Unix, Linux (recommended); or Windows
- Web server: Apache or NGINX
  - mod_rewrite allowed
  - mod_security allowed
  - mod_auth_basic denied
  - RECOMMENDED: at least 64Mb of memory reserved for PHP, the more the better
- PHP: version 5.2 and newer
- MySQL: version 5.0 and newer
- PHP extension:
  - PDO_MySQL
  - cURL
  - SimpleXML
  - mcrypt
  - GD
  - OpenSSL
  - DOM
  - SOAP
  - Zip
- PHP settings:
  - allow_url_fopen allowed
  - register_globals allowed
  - magic_quotes_* allowed
  - safe_mode denied
  - upload_max_filesize set to 16MB and more
- For more informations please visit: http://doc.prestashop.com/display/PS16/What+you+need+to+get+started

## Installation and first setup
For installation, just open Prestashop in your browser for the first time and go through the installation settings.
For a successful installation, the webserver must have the right to write to the necessary folders. You can create
the database in advance or choose to create it automatically during installation.

_!!! If you are using czech version of e-shop, after installation you have to open section „KONFIGURACE->SEO A URLS,
and search for "objednavka" in filter "Přátelské URL" and make sure that pages order and order-opc have different
URL adresses. If you don’t do so, the order process could cause errors with redirecting !!!_

## Installation of plugin for Packeta
For installation of this plugin there is required to do following steps:

- Make sure your webserver has write access to the folder /modules.
- Log in to Prestashop administration at host/adminXXXXXX (generated automatically, You can find the administration
  address by folder name).
- In the "Modules and Services" section, click the "Add new module" button in the upper right corner.
- Select the .zip archive of the module and click the "Upload this module" button

After these steps the plugin is installed. It i salso necessary to perform basic configuration of the module.

## Configuration of plugin
Each of the configuration blocks is saved with its own "Add" or "Save" button.

### Module configuration
- **API key** - your API key can be found in the [Packeta client section](https://client.packeta.com/en/support/)
  in section of Client support
- **Sender name** - the sender name you have set in the client section of the sender list
- **Force country** - select the countries that will be offered in the e-shop basket when selecting the Dispatch
  Point of Delivery. To select, press Ctrl + left mouse button to select the desired country. You can select more than one
  country at a time. In the same way, you will remove the country. If no country is selected, all supported countries will
  be offered automatically.
- **Force language** – The language of widget for pickup points selection is set according to the currently selected
  eshop language version. If you set a forced language, this language is always set in the widget, regardless
  of the language version of the eshop.

### Carrier settings
- To add a carrier enter the menu *Shipping* - *Carriers*.
- In the module settings, you select whether it is a *Packeta pickup point* or delivery to the address.
- If you select YES for the carrier in the "Is COD" column, the orders of this carrier
  will be always cash on delivery.
- If you want cash on delivery to be controlled by the payment method and not the carrier, fill NO
  in the "Is cash on delivery" column for the carrier, and set YES in the "Is COD" column for
  the payment method.
- For carriers whose shipments will not be transported by Packetery, select "- no -".

#### Set country restrictions
- In the carrier settings you specify for which zones is the selected carrier allowed and for what price.
- You can create these zones in the *Localization* - *Zones* menu and assign individual countries to them in the *Localization* - *Countries* menu.
- It is also necessary to enable selected payment modules in the menu *Modules and Services* - *Payment* at the bottom of the page
  in the *Country Restrictions* menu.

## List of Packetery orders
- The list of orders can be found in the menu *Orders* - *Packetery*.
- Check the list of orders you want to export. You can set whether the order is cash on delivery.
- To export the data to CSV, click on the "Save COD List and Export Selected" button.

## Module update
- To update the module, download the new version and upload it in the menu *Modules and Services* - *Modules and Services*.

## Uninstalling the module
- To uninstall, find the module in the menu *Modules and Services* - *Modules and Services* and choose *Uninstall*.
- If you want to remove the module completely, choose *Remove*.

### Reinstallation
- If you decide to reinstall the module, you may encounter an error during the installation
  "Unable to install override: The method __construct in the class Carrier is already overridden."
- In this case, you need to delete two files: `/cache/class_index.php` and `/override/classes/Carrier.php` before installation.

## Information about the module

#### Supported languages:
- czech
- english

#### Supported versions:
- PrestaShop vesion 1.6.x
- If there is any problem with the module feel free to contact us on support@packeta.com

#### Provided functions:
- Widget integration for selection pickup points in the eshop cart
- Delivery on address by a Packetery external shipment carrier
- Export shipments to a csv file that can be imported in the [client section](https://client.packeta.com/).

### Limitations
- the module does not currently support multistore
- the module is intended only for the default baskets of PrestaShop. If you use a one-page checkout module
  for a third-party cart, the module may not work properly


# Modul pro PrestaShop 1.6

### Stažení modulu
[Aktuální verze 2.0.5 (Stáhnout »)](https://github.com/Zasilkovna/prestashop-1.6/releases/download/2.0.5/prestashop-1.6-modul-2.0.5.zip)

## Systémové požadavky
Pro instalaci Prestashop 1.6.x jsou vyžadovány následující komponenty:
- System: Unix, Linux (doporučené); nebo Windows
- Web server: Apache 2.x nebo Nginx
  - mod_rewrite povoleno
  - mod_security zakázáno
  - mod_auth_basic zakázáno
  - DOPORUČENO: alespoň 64Mb paměti vyhrazené pro PHP, čím více, tím lépe
- PHP: verze 5.6 a novější
- MySQL: verze 5.0 a novější nebo MariaDB
- Rozšíření PHP:
  - PDO_MySQL
  - cURL
  - SimpleXML
  - mcrypt
  - GD
  - OpenSSL
  - DOM
  - SOAP
  - Zip
- Nastavení PHP:
  - allow_url_fopen povoleno
  - register_globals zakázáno
  - magic_quotes_* zakázáno
  - safe_mode zakázáno
  - upload_max_filesize nastaven an 16M a více
- Pro více informací navštivte http://doc.prestashop.com/display/PS16/What+you+need+to+get+started

## Instalace a úvodní konfigurace
- Pro instalaci stačí poprvé otevřít Prestashop v prohlížeči a projít instalačním formulářem.
- Pro úspěšnou instalaci je třeba, aby měl webserver právo k zápisu do potřebných složek.
- Databázi je možné vytvořit předem, nebo zvolit automatické vytvoření při instalaci.

_!!! Pokud používáte českou verzi e-shopu, navigujte se po instalaci do sekce KONFIGURACE->SEO A URLS, vyhledejte "objednavka" ve filtru "Přátelské URL" a
ujistěte se, že stránky order a order-opc mají rozlišné přátelské URL. Pokud tak neučiníte, proces objednávky bude náchylný na chyby přesměrování !!!_

## Instalace plug-inu pro Zásilkovnu
Pro instalaci plug-inu je potřeba provést následující kroky:
- Ujistěte se, že má webserver práva na zápis do složky `/modules`.
- Přihlašte se do administrace Prestashopu na adrese host/adminXXXXXX (vygenerováno automaticky, adresu administrace zjistíte dle jména složky).
- V sekci "Moduly a Služby" klikněte na tlačítko "Přidat nový modul" v pravém horním rohu.
- Vyberte .zip archiv modulu a klikněte na tlačítko "Nahrát tento modul".

Po těchto krocích je plug-in nainstalovaný. Dále je potřeba provést základní konfiguraci modulu.

## Konfigurace plug-inu
Každý z bloků konfigurace se ukládá vlastním tlačítkem "Přidat", nebo "Uložit".

### Nastavení modulu
- **Klíč API**  - váš klíč API naleznete v [klientské sekci Zásilkovny](https://client.packeta.com/cs/support/) v části **Klientská podpora**
- **Označení odesílatele** - označení odesílatele které máte nastaveno v [klientské sekci](https://client.packeta.com/cs/senders/) v seznamu odesílatelů
- **Vynutit zemi** - vyberte země, které se budou nabízet v košíku eshopu při výběru výdejního místa Zásilkovny. Výběr provedete tak, že stisknete
  klávesu *Ctrl* + levým tlačítkem myši vyberete požadovanou zemi.  Můžete vybrat více zemí zároveň.  Stejným způsobem zemi odeberete. Jestliže
  nevyberete žádnou zemi, budou se nabízet automaticky všechny podporované země.
- **Vynutit jazyk** - Jazyk widgetu pro výběr výdejních míst se nastavuje podle aktuálně zvolené jazykové mutace eshopu.  Pokud nastavíte vynucený jazyk,  
  nastaví se tento jazyk ve widgetu vždy, bez ohledu na nastavenou jazykovou mutaci eshopu.

### Nastavení dopravců
- Dopravce vytvoříte v menu *Doručení* - *Dopravci*.
- V nastavení modulu vyberete, zda jde o *Výdejní místo Zásilkovny* nebo doručení na adresu.
- Pokud zvolíte u dopravce ve sloupci "Je dobírka" ANO, budou objednávky s použitím
  tohoto dopravce vždy na dobírku.
- Pokud chcete, aby se dobírka řídila podle platební metody, a ne podle dopravce, nechte
  u dopravce ve sloupci "Je dobírka" NE, a nastavte dobírku ve sloupci "Je dobírka" u
  způsobu platby.
- U dopravců, jejichž zásilky nebudou přepravované Zásilkovnou, zvolíte "-- ne --".

#### Nastavení omezení na zemi
- V nastavení dopravců určujete, pro které zóny je zvolený dopravce povolený a za jakou cenu.
- Tyto zóny můžete vytvořit v menu *Lokalizace* - *Zóny* a přiřadit do nich v menu *Lokalizace* - *Země* jednotlivé země.
- Dále je potřeba povolit vybrané platební moduly v menu *Moduly a služby* - *Platba* ve spodní části stránky
  v nabídce *Omezení pro země*.

## Seznam objednávek Zásilkovna
- Seznam objednávek naleznete v menu *Objednávky* - *Zásilkovna*.
- Zaškrtněte seznam objednávek, které chcete exportovat.  U objednávky můžete nastavit zda se jedná o dobírku.
- Pro export dat do CSV klikněte na tlačítko "Uložit seznam dobírek a exportovat vybrané".

## Aktualizace modulu
- Pro aktualizaci modulu stáhněte novou verzi a nahrajte ji v menu *Moduly a služby* - *Moduly a služby*.

## Odinstalace modulu
- Odinstalování provedete po vyhledání modulu v menu *Moduly a služby* - *Moduly a služby* kliknutím na *Odinstalovat*.
- Pokud chcete modul úplně odstranit, kliknete na *Odstranit*.

### Opětovná instalace
- Pokud se rozhodnete modul znovu instalovat, můžete se při instalaci setkat s chybou
  "Nelze nainstalovat přepsání: Metoda __construct ve třídě Carrier je již přepsána."
- V tomto případě je potřeba před instalací smazat dva soubory: `/cache/class_index.php` a `/override/classes/Carrier.php`.

## Informace o modulu

#### Podporované jazyky
- čeština
- angličtina

#### Podporovaná verze
- PrestaShop verze 1.6.x.
- Při problému s použitím modulu nás kontaktujte na adrese technicka.podpora@zasilkovna.cz.

#### Poskytované funkce
- Integrace widgetu pro výběr výdejních míst v košíku eshopu.
- Doručení na adresu přes externí dopravce Zásilkovny.
- Export zásilek do csv souboru, který lze importovat v [klientské sekci](https://client.packeta.com/).

### Omezení
- Modul v současné době nepodporuje multistore.
- Modul je určen pouze pro výchozí košíky PrestaShopu. Pokud používáte nějaký one page checkout modul košíku třetí
  strany, modul nemusí správně fungovat.
