# 🏕️ Intercamp Mixer - Strategické Rozřazování

Aplikace pro automatické a spravedlivé rozřazování účastníků do mezinárodních skupin v rámci subcampů.

## 🚀 Hlavní Funkce
- **Mezinárodní míchání**: Algoritmus prioritizuje unikátnost národností a skupin v rámci cílových 8-členných týmů.
- **Import z Excelu**: Podpora nahrávání (.xlsx) s automatickou detekcí subcampů a importem údajů vedoucích.
- **Multi-language (CZ/EN)**: Automatická detekce jazyka prohlížeče s možností manuálního přepnutí.
- **Barevné režimy**: Light, Dark a Pink mode pro pohodlné používání na desktopu i mobilu.
- **Veřejné hledání**: Rychlé vyhledání cílové skupiny podle kódu účastníka.

---

## 🛠️ Správa a Údržba Projektu

### 📦 Instalace a Spuštění
Aplikace běží na Laravelu 8 a PHP 8.2+.

#### Lokální spuštění (PHP):
1. Nainstalujte závislosti: `composer install` a `npm install`
2. Nastavte `.env` soubor
3. Spusťte server: `php artisan serve`

#### Spuštění přes Docker (Laravel Sail):
1. Spusťte kontejnery: `./vendor/bin/sail up -d`
2. Přístup přes: `http://localhost`

---

### 🗄️ Aktualizace Databáze (Upgrade)
Při přidání nových funkcí nebo změnách v databázi je nutné spustit migrace.

#### Přes přímé PHP:
```powershell
php artisan migrate
```

#### Přes Docker (Sail):
Pro volání migrací v Dockeru použijte tento příkaz (nejspolehlivější na Windows):
```powershell
docker compose exec laravel.test php artisan migrate
```
Případně přes Sail (.bat verze pro PowerShell): `vendor\bin\sail artisan migrate`

Stav migrací ověříte příkazem: `php artisan migrate:status`

---

### 🌍 Lokalizace a Texty
Texty jsou uloženy v JSON souborech v:
- `resources/lang/cs.json`
- `resources/lang/en.json`

Při přidání nového textu do kódu použijte helper `{{ __('Klíč') }}` a doplňte překlad do obou souborů.

---

### 🎨 Témata a Design
Aplikace používá Tailwind CSS přes CDN v `layout.blade.php`.
Témata jsou definována pomocí CSS proměnných v `:root`, `[data-theme='dark']` a `[data-theme='pink']`.

---

## 📈 Export a Analýza
- **CSV Export**: Dostupný v administraci po spuštění míchacího algoritmu.
- **Diverzita**: Skript `analyze.php` slouží k hloubkové kontrole kvality promíchání národností.
