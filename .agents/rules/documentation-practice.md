# Pravidlo pro dokumentaci rozšiřování funkcí

Kdykoliv se do aplikace přidává nová funkčnost nebo se rozšiřuje stávající, musí být tato změna zdokumentována v odpovídajících `.md` souborech.

## Postup dokumentace
1.  **README.md**: Aktualizuj sekci "Hlavní Funkce" a případně přidej novou sekci do "Správa a Údržba", pokud funkce vyžaduje specifické nastavení (např. proměnné v .env).
2.  **UPGRADE_PLAN.md**: Pokud je změna součástí širšího plánu vylepšení, přidej ji do tohoto souboru s popisem, jak funguje.
3.  **FEATURES.md**: Pokud existuje soubor se seznamem vlastností, ujisti se, že je tam novinka reflektována.

## Specifika pro Zabezpečení
Při změnách v autorizaci (jako byly tokeny) musí dokumentace jasně obsahovat:
-   Název proměnné v `.env` (pokud existuje).
-   Formát autorizačního odkazu.
-   Popis rolí a jejich oprávnění.
-   Způsob persistence (např. localStorage).

Toto pravidlo zajišťuje, že projekt zůstane dlouhodobě udržitelný a srozumitelný pro ostatní správce.
