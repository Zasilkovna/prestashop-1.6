# Modul pro PrestaShop 1.6

### Stažení modulu
[Aktuální verze 2.0.2 (Stáhnout »)](https://github.com/Zasilkovna/prestashop-1.6/raw/master/releases/prestahop-1.6-packetery-2.0.2.zip)

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
 - **Klíč API:**  - váš klíč API naleznete v [klientské sekci Zásilkovny](https://client.packeta.com/cs/support/) v části **Klientská podpora**
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
 
 
 
 