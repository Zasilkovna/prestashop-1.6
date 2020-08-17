[Návod v češtině](#modul-pro-prestashop-16)

# Module for PrestaShop 1.6

### Download link
[Download version 2.0.3](https://github.com/Zasilkovna/prestashop-1.6/releases/download/2.0.3/prestashop-1.6-modul-2.0.3.zip)

## Systém requirements
For installation of Prestashop 1.6.x there is required to install following components:
- System: Unix, Linux (recommended); or Windows
- Web server: Apache 1.3 and newer; NGINX 1.0 and newer
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

## Carrier methods
- To add a new carrier, enter the Carrier Name, Shipping Time, Country (select the country by pressing 
the Ctrl key and left-clicking to select more countries). Next you choose whether it is cash on delivery. 
Click Add to save everything.
- To delete, click the Delete button on the existing shipping method.

## List of carrier delivery to address
The module supports delivery to an address by external carriers. You can assign an external Packeta shipment carrier to each carrier.

## List of orders Packeta 
- The list of orders can be found in the menu *Orders* - *Packeta*.
- Check the list of orders you want to export. You can set whether the order is cash on delivery.
- To export the data to CSV, click on the "Save COD List and Export Selected" button.

## Informations about the module

#### Supported languages:
- czech
- english

#### Supported versions:
- PrestaShop vesion 1.6.x
- If there is any problem with the module feel free to contact us on support@packeta.com 

#### Provided functions:
- Widget integration for selection pickup points in the eshop cart
- Delivery on address by a Packeta external shipment carrier
- Export shipments to a csv file that can be imported in the [client section](https://client.packeta.com/).

### Limitations
- the module does not currently support multistore
- the module is intended only for the default baskets of PrestaShop. If you use a one-page checkout module 
for a third-party cart, the module may not work properly


# Modul pro PrestaShop 1.6

### Stažení modulu
[Aktuální verze 2.0.3 (Stáhnout »)](https://github.com/Zasilkovna/prestashop-1.6/releases/download/2.0.3/prestashop-1.6-modul-2.0.3.zip)

## Systémové požadavky
Pro instalaci Prestashop 1.6.x jsou vyžadovány následující komponenty:
- System: Unix, Linux (doporučené); nebo Windows
- Web server: Apache 1.3 a novější; NGINX 1.0 a novější
  - mod_rewrite povoleno
  - mod_security zakázáno
  - mod_auth_basic zakázáno 
  - DOPORUČENO: alespoň 64Mb paměti vyhrazené pro PHP, čím více, tím lépe
- PHP: verze 5.2 a novější
- MySQL: verze 5.0 a novější
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
Pro instalaci stačí poprvé otevřít Prestashop v prohlížeči a projít instalačním formulářem.
Pro úspěšnou instalaci je třeba, aby měl webserver právo k zápisu do potřebných složek. 
Databázi je možné vytvořit předem, nebo zvolit automatické vytvoření při instalaci.

_!!! Pokud používáte českou verzi e-shopu, navigujte se po instalaci do sekce KONFIGURACE->SEO A URLS, vyhledejte "objednavka" ve filtru "Přátelské URL" a 
ujistěte se, že stránky order a order-opc mají rozlišné přátelské URL. Pokud tak neučiníte, proces objednávky bude náchylný na chyby přesměrování !!!_

## Instalace plug-inu pro Zásilkovnu
 Pro instalaci plug-inu je potřeba provést následující kroky:
- Ujistěte se, že má webserver práva na zápis do složky /modules 
- Přihlašte se do administrace Prestashopu na adrese host/adminXXXXXX (vygenerováno automaticky, adresu administrace zjistíte dle jména složky)
- V sekci "Moduly a Služby" klikněte na tlačítko "Přidat nový modul" v pravém horním rohu
- Vyberte .zip archiv modulu a klikněte na tlačítko "Nahrát tento modul"

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
 
### Způsoby dopravy
 - Pro přidání nového dopravce vyplňte *Název dopravce*, *Doba přepravy* , *Země* (zemi označíte stiknutím klávesy *Ctrl* a kliknutím levým tlačítkem myši,
  můžete vybrat i více zemí). Dále zvolíte zda se jedná o přepravu na dobírku.  Vše uložíte kliknutím na tlačítko  *Přidat*.
 - Pro smazání klikněte na tlačítko *Odstranit* u existujícího způsobu dopravy. 

### Seznam dopravců doručení na adresu
Modul podporuje doručení na adresu přes Zásilkovnu prostřednictvím externích dopravců. 
Ke každému dopravci můžete přiřadit externího dopravce Zásilkovny. 
 
## Seznam objednávek Zásilkovna
 - Seznam objednávek naleznete v menu *Objednávky* - *Zásilkovna*.
 - Zaškrtněte seznam objednávek, které chcete exportovat.  U objednávky můžete nastavit zda se jedná o dobírku.
 - Pro export dat do CSV klikněte na tlačítko "Uložit seznam dobírek a exportovat vybrané".
 
 ## Informace o modulu
 
 **Podporované jazyky:**
 - čeština
 - angličtina
 
 #### Podporovaná verze
 - PrestaShop verze 1.6.x
 - Při problému s použitím modulu nás kontaktujte na adrese technicka.podpora@zasilkovna.cz
 
 #### Poskytované funkce
 - Integrace widgetu pro výběr výdejních míst v košíku eshopu
 - Doručení na adresu přes externí dopravce Zásilkovny
 - Export zásilek do csv souboru, který lze importovat v [klientské sekci](https://client.packeta.com/).
 
 ### Omezení
 - modul v současné době nepodporuje multistore
 - modul je určen pouze pro výchozí košíky PrestaShopu.  Pokud používáte nějaký one page checkout modul košíku třetí strany,  modul nemusí správně
 fungovat.
 
 
 
 