# Dokumentace projektu: Intercamp Michání Skupin

Tento dokument slouží jako přehled aktuálních funkcí aplikace a historie změn.

## 🚀 Aktuální Funkce

### 1. Administrační Modul: Import Dat
- **Nahrávání Excelu (.xlsx):** Podpora pro rozsáhlé tabulky účastníků.
- **Automatická expanze:** Skupina s $N$ dětmi se v databázi automaticky "rozbalí" na jednotlivé účastníky pro potřeby algoritmu.
- **Detekce Subcampů:** Systém automaticky parsuje sloupec se subcampy (číslo 1-4) a ukládá je k oddílům.
- **Ošetření duplicit:** Slučování dětí pod stejné číslo objednávky (`order_number`) při vícenásobném výskytu v Excelu.

### 2. Rozřazovací Algoritmus (Mixer)
- **Izolace Subcampů:** Míchání probíhá striktně v rámci jednotlivých subcampů bez křížení.
- **Deterministický přístup:** Algoritmus nepoužívá náhodu, výsledek je při stejných datech vždy stejný.
- **Dekompozice:** Rozdělení původních skupin na "balíčky" po 2 až 3 dětech (Krok A).
- **Round-Robin Interleaving:** Střídání zemí v řadě pro maximální mezinárodní diverzitu (Krok B & C).
- **Strategické umisťování:** Plnění cílových 8členných skupin dětmi s hlídáním unikátnosti původní skupiny (Krok D). Maximální velikost skupiny je striktně omezena na 8 dětí a 1 vedoucího (celkem 9 osob).
- **Unikátní ID účastníka:** Po rozřazení je každému dítěti přidělen kód ve formátu `SC{subcamp}_G{group}_{pořadí}` (např. `SC1_G01_1`), který jednoznačně identifikuje jeho pozici ve skupině.
- **Fallback mechanizmus:** Pokud nelze dodržet unikátnost skupiny nebo národnosti na konci subcampu, systém automaticky upřednostní naplnění kapacity. Pokud by mělo dojít k překročení limitu 8 dětí ve skupině, algoritmus na pozadí dynamicky vytvoří novou prázdnou cílovou skupinu.

### 3. Modul Exportu a Prohlížení
- **CSV Export:** Stahování kompletního výsledku ve formátu CSV (středníkový oddělovač, UTF-8 BOM pro Excel).
- **Statistiky úspešnosti:** Přehled kolik dětí se podařilo rozřadit do "ideálních" (Tier 1) vs "fallback" skupin přímo po spuštění.
- **DB Inspector:** Interní webový prohlížeč pro kontrolu obsahu tabulek `OriginalGroup` a ucelených vygenerovaných `Cílových Skupin` (Target Groups) přímo v administraci, včetně zobrazení přidělených vedoucích a identifikátorů výprav.

### 4. Infrastruktura
- **Laravel 8 & PHP 8.2**
- **Docker Compose (Sail):** Pro lokální vývoj.
- **Railway.app:** Konfigurace pro produkční nasazení (Nixpacks + PostgreSQL).

---

## 📝 Historie změn (Changelog)

### [2026-03-01] - Aktuální stav
- **PŘIDÁNO:** Realizován kompletní míchací algoritmus (Krok A-D).
- **PŘIDÁNO:** Stránka se statistikami národností rozdělených podle subcampů.
- **PŘIDÁNO:** Implementována "Tiered" logika pro míchání národností (prioritizace unikátnosti země v rámci cílové skupiny).
- **PŘIDÁNO:** Rozšířeny statistiky míchání o reportování Tiers (Ideální / Jen skupina / Fallback).
- **PŘIDÁNO:** Skript `analyze.php` pro hloubkovou kontrolu diverzity výsledků.
- **PŘIDÁNO:** Implementován export do CSV se semicolon (;) oddělovačem.
- **PŘIDÁNO:** Vytvořena stránka "Prohlížeč databáze" pro adminy.
- **PŘIDÁNO:** Konfigurace pro nasazení na Railway.app (`nixpacks.toml`).
- **OPRAVENO:** Chybějící PHP ovladač pro PostgreSQL na Railway (`pdo_pgsql`).
- **OPRAVENO:** Chyba `invalid value for parameter "client_encoding": "utf8mb4"` při startu Postgres.
- **OPRAVENO:** Detekce subcampů z Excelu pomocí regulárních výrazů.
- **OPRAVENO:** Varování prohlížeče o "nezabezpečeném odesílání" vynucením HTTPS v produkci.
- **ZMĚNA:** Povýšení PHP na 8.2 z důvodu požadavků Railway Railpacku.
- **ZMĚNA:** Přechod z `master` větve na `main`.

---
*(Tento soubor bude doplňován při každé další funkční změně.)*
