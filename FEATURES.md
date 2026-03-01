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
- **Strategické umisťování:** Plnění cílových 10členných skupin s hlídáním unikátnosti původní skupiny (Krok D).
- **Fallback mechanizmus:** Pokud nelze dodržet unikátnost skupiny na konci subcampu, systém automaticky upřednostní naplnění kapacity.

### 3. Modul Exportu a Prohlížení
- **CSV Export:** Stahování kompletního výsledku ve formátu CSV (středníkový oddělovač, UTF-8 BOM pro Excel).
- **DB Inspector:** Interní webový prohlížeč pro kontrolu obsahu tabulek `OriginalGroup` a `Participant` přímo v administraci.

### 4. Infrastruktura
- **Laravel 8 & PHP 8.2**
- **Docker Compose (Sail):** Pro lokální vývoj.
- **Railway.app:** Konfigurace pro produkční nasazení (Nixpacks + PostgreSQL).

---

## 📝 Historie změn (Changelog)

### [2026-03-01] - Aktuální stav
- **PŘIDÁNO:** Realizován kompletní míchací algoritmus (Krok A-D).
- **PŘIDÁNO:** Implementován export do CSV se semicolon (;) oddělovačem.
- **PŘIDÁNO:** Vytvořena stránka "Prohlížeč databáze" pro adminy.
- **PŘIDÁNO:** Konfigurace pro nasazení na Railway.app (`nixpacks.toml`).
- **OPRAVENO:** Detekce subcampů z Excelu pomocí regulárních výrazů.
- **ZMĚNA:** Povýšení PHP na 8.2 z důvodu požadavků Railway Railpacku.
- **ZMĚNA:** Přechod z `master` větve na `main`.

---
*(Tento soubor bude doplňován při každé další funkční změně.)*
