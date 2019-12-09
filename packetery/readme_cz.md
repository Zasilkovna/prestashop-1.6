# Instalace Modulu

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
- Ujistit se, že má webserver práva na zápis do složky /modules 
- Přihlásit se do administrace Prestashopu na adrese host/adminXXXXXX (vygenerováno automaticky, adresu administrace zjistíte dle jména složky)
- V sekci "Moduly a Služby" kliknout na tlačítko "Přidat nový modul" v pravém horním rohu
- Vybrat .zip archiv modulu

Po těchto krocích je plug-in nainstalovaný. Dále je potřeba provést základní konfiguraci, v nastavení modulu vyplňte následující:
- API klíč (získáte po přihlášení na http://client.packeta.com/)
- Vytvořte dopravce v sekci "Přidání způsobu dopravy".

Každý z bloků konfigurace se ukládá vlastním tlačítkem "Přidat", nebo "Uložit".

## Konfigurace plug-inu
### Základní konfigurace
 - Nejprve se zaregistrujte na http://client.packeta.com/ a zkopírujte API klíč do pole na kartě modulu.
 - Každý z bloků konfigurace se ukládá vlastním tlačítkem "Přidat", nebo "Uložit".
 - Pro přidání vyplňte formulář v sekci "Přidání způsobu dopravy"
 - Je možné vynutit použití specifické země a jazyka ve widgetu nastavením hodnot "Vynucená země" a "Vynucený jazyk". Pokud jsou hodnoty prázdné, vybere se země podle adresy zákazníka.. 
 - K export dat do CSV souboru zvolte objednávky, které chcete vyexportovat na záložce "Objednávky" a klikněte na tlačítko "CSV Export"