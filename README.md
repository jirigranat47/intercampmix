# 🏕️ Intercamp Mixer - Strategické Rozřazování

Aplikace pro automatické a spravedlivé rozřazování účastníků do mezinárodních skupin v rámci subcampů.

## 🚀 Hlavní Funkce
- **Mezinárodní míchání**: Algoritmus prioritizuje unikátnost národností a skupin v rámci cílových 8-členných týmů.
- **Import z Excelu**: Podpora nahrávání (.xlsx) s automatickou detekcí subcampů a importem údajů vedoucích.
- **Multi-language (CZ/EN)**: Automatická detekce jazyka prohlížeče s možností manuálního přepnutí.
- **Zabezpečení (Tokeny)**: Přístup chráněn pomocí unikátních tokenů uložených v `localStorage`. Podpora rolí (Admin/Viewer).
- **Vyhledávání**: Rychlé vyhledání cílové skupiny podle kódu účastníka (dostupné pouze pro autorizované uživatele).
- **Manuální přesun**: Možnost administrátora ručně přesunout účastníka do specifické cílové skupiny pro doladění výsledků míchání.
- **Formát kódů**: `S{subcamp}-{skupina}-{pořadí/X}` (např. `S1-17-7` pro účastníka nebo `S1-17-X` pro vedoucího).

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

### 🔐 Zabezpečení a Přístup
Aplikace nepoužívá klasická hesla, ale systém přístupových tokenů.

#### 1. Root Token (Hlavní Admin)
Nejvyšší oprávnění je definováno v `.env` pomocí proměnné `ADMIN_ROOT_TOKEN`. 
- **Odkaz pro přihlášení**: `http://localhost:8000/auth/{VAS_ROOT_TOKEN}`
- Root admin může generovat další tokeny pro ostatní uživatele v sekci "Správa Tokenů".

#### 2. Role uživatelů
- **Admin**: Přístup k importu, míchání, exportu a prohlížení dat.
- **Viewer**: Přístup pouze k vyhledávání a prohlížení databáze (vhodné pro vedoucí subcampů).

#### 3. Jak vytvořit přístupový odkaz
1. Přihlaste se jako Root Admin.
2. Přejděte do sekce **🔑 Správa Tokenů**.
3. Zadejte popis a zvolte roli.
4. Klikněte na "Vygenerovat".
5. Zkopírujte vygenerovaný odkaz a pošlete ho uživateli.

Po kliknutí na odkaz se token uloží do `localStorage` prohlížeče a přístup je trvalý, dokud se uživatel neodhlásí.

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

---

## 🛠️ Algoritmus a Formát Dat

### Rozřazovací Kódy
Aplikace generuje unikátní označení pro každého účastníka ve formátu `S{subcamp}-{skupina}-{index}`.

- **S**: Prefix pro Subcamp.
- **{subcamp}**: ID subcampu (1 až 4).
- **-**: Oddělovač (pomlčka).
- **{skupina}**: Dvoumístné číslo mixované skupiny (např. `01`, `17`).
- **{index}**: Pořadové číslo dítěte v rámci skupiny (`1` až `8`) nebo písmeno `X` pro vedoucího výpravy.

**Příklad:** `S1-17-7` (Subcamp 1, skupina 17, 7. dítě) | `S1-17-X` (Vedoucí skupiny S1-17).

---

## 📜 Historie změn
- **04.05.2026**: Aktualizace logiky přesunu: Přesunutá osoba nyní automaticky získá správný registrační kód odpovídající cílové skupině. Systém dynamicky spočítá, jaké číslo má následovat (např. po nejvyšším členovi 7 přiřadí 8). Pokud se přesouvá vedoucí do skupiny bez vedoucího, získá kód "X".
- **03.05.2026**: Změna funkce z "Manuální prohození" na "Manuální přesun". Umožňuje rychlejší výběr účastníka a jeho přesunutí přímo do zadané cílové skupiny.
