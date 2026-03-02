# Projekt: International Mixer – Rozřazovací Algoritmus

## 1. Popis projektu
Cílem aplikace je spravedlivé a strategické rozdělení účastníků do cílových skupin (každá po 8 dětech). Hlavním kritériem je **maximální mezinárodní diverzita** a zajištění, aby děti z jedné země (a zejména z jedné konkrétní vyslané skupiny) byly co nejvíce promíchány s ostatními.

## 2. Klíčové funkce
### Administrační modul (Admin)
* **Import dat:** Nahrání souboru `.xlsx` se seznamem dětí a vedoucích (sloupec "Number of Children" a "Number of Leaders"). Vstupní sobor pro testování a vývoj 20250524_Groups per SC_Intercamp_V5.xlsx a data jsou na záložce All Subcamps.
* **Validace:** * Žádná vstupní skupina nesmí mít více než 20 dětí (jinak error).
    * Celkový počet dětí musí odpovídat počtu skupin (v poměru k 8 dětem na skupinu).
* **Zpracování:** Spuštění míchacího algoritmu pro děti a následné přiřazení vedoucích.
* **Export:** Stažení výsledného mapování dětí i vedoucích v `.csv`.
* **Persistence:** Uložení výsledků do PostgreSQL (přepisuje předchozí běh).

### Uživatelský modul (Veřejný)
* **Vyhledávání:** Jednoduchý formulář pro zadání unikátního kódu dítěte.
* **Výsledek:** Zobrazení názvu cílové skupiny (např. "B4") a informací o posledním platném rozřazení.

## 3. Postup míchání (Algoritmus)
Algoritmus nepoužívá náhodné míchání (`shuffle`), aby byla zajištěna maximální kontrola nad diverzitou. Postupuje v následujících krocích:

### Krok A: Dekompozice na balíčky
Každá vstupní skupina (max. 20 dětí) je rozdělena na menší nedělitelné celky:
1.  Pokud je počet dětí v původní skupině sudý, rozdělí se na **dvojice**.
2.  Pokud je lichý, vytvoří se **jedna trojice** a zbytek jsou **dvojice**.
*Tím je zajištěno, že žádné dítě nezůstane samo a zároveň lze z balíčků o velikosti 2 a 3 vždy poskládat cílovou skupinu o 8 lidech (např. 3+3+2 nebo 2+2+2+2).*

### Krok B: Třídění podle zemí (Queuing)
Všechny vytvořené balíčky se seřadí do front podle země původu. Vznikne tak několik front (např. fronta Česko, fronta Německo, fronta Polsko...).

### Krok C: Strategické prokládání (Round Robin)
Vytvoří se jeden hlavní seznam balíčků k rozřazení metodou "kolem dokola":
* Vezme se první balíček z fronty Země 1, pak první ze Země 2, pak ze Země 3...
* Jakmile se vystřídají všechny země, cyklus se opakuje s druhými balíčky v pořadí.
* **Výsledek:** Seznam, kde za sebou jdou balíčky z různých zemí v pravidelném rytmu.

### Krok D: Plnění cílových skupin (Placement)
Algoritmus prochází proložený seznam balíčků a umisťuje je do cílových skupin (SCx_Gxx) podle těchto pravidel:
1.  **Priorita plnění:** Balíček se přednostně dává do skupiny, která má aktuálně nejméně dětí.
2.  **Kontrola duplicity původu:** Do jedné cílové skupiny nesmí být umístěny dva balíčky, které pocházejí ze **stejné původní vstupní skupiny** (i kdyby to byla stejná země).
3.  **Fallback:** Pokud by pravidlo č. 2 znemožnilo dokončení algoritmu (v závěrečné fázi), pravidlo se uvolní a balíček se umístí do první volné skupiny s kapacitou.

### Krok E: Přiřazení vedoucích
Po rozmíchání všech dětí do cílových skupin se provede přiřazení vedoucích:
1.  **Cíl:** Každá cílová skupina (`SCx_Gxx`) musí mít právě jednoho vedoucího.
2.  **Párování:** Vedoucí je prioritně přidělen do skupiny, kde se nachází alespoň jeden balíček z jeho vlastní původní výpravy (`OriginalGroup`).
3.  **Záloha (Off-duty):** Pokud má výprava více než jednoho vedoucího, alespoň jeden z nich může zůstat "mimo rozřazení" (nebude mu přidělena skupina), pokud je celkový počet vedoucích v subcampu dostatečný pro pokrytí všech skupin.
4.  **Kód vedoucího:** Vedoucí dostane kód ve formátu `SCx_Gxx_X` (např. `SC1_G01_X`).
5.  **Vyhledávání:** Veřejné vyhledávání musí podle kódu `_X` správně identifikovat subcamp a skupinu vedoucího.

## 4. Technologický stack
* **Jazyk:** PHP 8.3 (Framework Laravel)
* **Knihovny:** `PHPOffice/PhpSpreadsheet` (pro práci s Excel/CSV)
* **Databáze:** PostgreSQL
* **Infrastruktura:** Docker kontejner (Dockerfile)
* **Hosting:** Railway.app