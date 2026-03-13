# Plán vylepšení a změn

Tento dokument shrnuje plánované změny pro aplikaci Intercamp Mixer.

## 1. Lokalizace a Jazyková podpora (CZ/EN)
- **Automatická detekce:** Aplikace rozpozná jazyk prohlížeče. Pokud je prohlížeč v češtině, nastaví se čeština. Pro všechny ostatní jazyky se automaticky zvolí angličtina.
- **Manuální přepínání:** V patičce stránky budou odkazy pro ruční změnu jazyka.
- **Persistence:** Ruční volba jazyka se uloží do `localStorage` a bude mít přednost před automatickou detekcí.
- **Automatisace překladů:** V `.agents/rules` bude vytvořeno pravidlo zajišťující, že jakýkoliv nový nebo upravený text bude okamžitě přeložen do obou jazyků.

## 2. Barevné režimy (Light, Dark, Pink)
- **Režimy:** Přidáme **Dark mode** a speciální **Pink mode** (aktuální stav je brán jako Light mode).
- **Detekce:** Aplikace se pokusí načíst preferovaný režim ze systému (PC/mobil).
- **Přepínání:** Ovládací prvek bude vhodně umístěn tak, aby byl snadno ovladatelný i z mobilních zařízení.
- **Persistence:** Ruční volba režimu se uloží do `localStorage` a bude mít přednost před systémovým nastavením.

## 3. Rozšíření Importu a Vyhledávání
- **Načítání údajů vedoucího:** Při importu z Excelu se z řádku načtou i sloupce:
    - **B:** Jméno
    - **C:** Příjmení
    - **E:** Telefonní číslo
- **Zobrazení:** Tyto údaje se zobrazí na stránce výsledků hledání (po zadání kódu).
- **Interaktivita:** Telefonní číslo bude klikatelný odkaz (`tel:`), aby bylo možné z mobilu přímo zahájit volání.
