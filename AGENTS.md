# AGENTS.md

> Instrukcja operacyjna dla agentów AI pracujących w tym repozytorium.
> Przeczytaj ją przed każdą zmianą. Nie zastępuje `README.md` — uzupełnia go o zasady pracy, architekturę i kryteria oceny.
>
> **Repozytorium:** `tapala/WDPAI-proj`
> **Kurs:** Projektowanie aplikacji internetowych 26/27
> **Wersja dokumentu:** 2026-11-19
> **Aktualny etap:** Laboratorium 3 ukończone — interfejs oparty na Figmie: tokeny, powłoka desktop/mobile, katalog z filtrami, karty sal, szczegóły, stany puste i błędów, QA dostępności. Brakuje bazy, logowania, rezerwacji i JavaScriptu.

---

## 0. Najważniejsze zasady

1. **Backend musi być obiektowy.** Projekt z backendem strukturalnym otrzymuje 2.0, a pozostałe kryteria nie są punktowane. Klasy, enkapsulacja, typy, zależności przez konstruktor i jasny podział odpowiedzialności są warunkiem koniecznym.
2. **Nie udawaj funkcji.** Jeśli coś nie działa, nie jest podłączone do bazy albo jest tylko makietą, napisz to wprost. Nie opisuj w README funkcji, których nie ma.
3. **Najpierw przeczytaj kod, potem zmieniaj.** Sprawdź `git status`, przeczytaj powiązane pliki i potwierdź aktualną implementację. Nie zakładaj, że opis z dokumentacji odpowiada kodowi.
4. **Małe, sprawdzalne zmiany.** Jedno zadanie = jeden logiczny commit. Nie łącz refaktoryzacji, nowej funkcji i zmiany stylów w jednym dużym diffie.
5. **Weryfikuj, zamiast wierzyć.** Uruchom `php -l`, testy i odpowiednie żądania HTTP. Nie pisz „testy przeszły”, jeśli ich nie uruchomiono.
6. **Nie commit secrets.** `.env`, hasła, tokeny, klucze i dane osobowe nie trafiają do repozytorium. W repo jest tylko `.env.example`.
7. **Nie dodawaj frameworka ani dużej biblioteki bez zgody.** Composer służy tu przede wszystkim do autoloadingu, PHPUnit i PHPMailera.
8. **Zachowaj zmiany użytkownika.** Nie cofaj i nie nadpisuj pracy, której nie rozumiesz. Przy konflikcie zapytaj.

---

## 1. Źródła prawdy

| Priorytet | Źródło | Co z niego bierzemy |
|---|---|---|
| 1 | [Lab 15 — kryteria ewaluacji](https://ii.pk.edu.pl/~awidlak/content/39) | 36 kryteriów oceny, punktacja, wymagana zawartość README, warunek obiektowego backendu |
| 2 | [Strona przedmiotu](https://ii.pk.edu.pl/~awidlak/subject/16) | Lista laboratoriów, materiały, kolejność realizacji |
| 3 | [Lab 4 — vibe coding i AGENTS.md](https://ii.pk.edu.pl/~awidlak/content/40) | Zasady bezpiecznej pracy z agentem AI, oczekiwana rola tego pliku |
| 4 | [Repo prowadzącego `wdpai-2027`](https://github.com/AdrianWii/wdpai-2027/tree/main) | Wzorzec startowy, przykład AGENTS.md, konwencje kursowe |
| 5 | Ten plik, `README.md` i aktualny kod | Konkretne decyzje projektowe i stan implementacji |

**Zasada rozstrzygania konfliktów:** wymagania z Lab 15 i polecenie użytkownika mają pierwszeństwo przed aktualnym kodem. Aktualny kod ma pierwszeństwo przed ogólnymi przyzwyczajeniami. Repo prowadzącego jest wzorcem pomocniczym, nie kopią do bezkrytycznego powielenia — nasz temat i nasze encje mogą być inne.

Jeśli wymaganie jest niejednoznaczne, **nie wymyślaj tematu projektu ani reguł biznesowych**. Wybierz rozwiązanie zgodne z istniejącym kodem, a jeśli wybór istotnie zmienia produkt, zapytaj użytkownika.

---

## 2. Projekt Roomly — cel, zakres i model domenowy

**Nazwa:** Roomly — System Rezerwacji Sal i Stanowisk.
**Cel:** aplikacja webowa do przeglądania, rezerwowania i zarządzania salami oraz stanowiskami pracy. Użytkownik wyszukuje pomieszczenia, filtruje je, sprawdza wyposażenie i dostępność, a następnie rezerwuje wybrany termin. Administrator zarządza użytkownikami, lokalizacjami, pomieszczeniami, wyposażeniem i rezerwacjami.
**Zakres:** kompletna, działająca aplikacja z bazą danych, a nie makieta interfejsu.

### 2.1. Role i uprawnienia

- `USER` — rejestracja, logowanie, przeglądanie i filtrowanie pomieszczeń, sprawdzanie dostępności, tworzenie rezerwacji, przegląd własnych rezerwacji, anulowanie własnych rezerwacji, edycja profilu, zmiana hasła, preferencje powiadomień, język i motyw.
- `ADMIN` — wszystkie uprawnienia `USER` oraz zarządzanie użytkownikami, lokalizacjami, pomieszczeniami, wyposażeniem i rezerwacjami; podgląd statystyk i wszystkich rezerwacji.
- Rola jest zapisana w bazie, a rejestracja publiczna zawsze tworzy `USER`. Rola nie może pochodzić z pola formularza.
- Uprawnienia są sprawdzane po stronie backendu przy każdym chronionym żądaniu. Ukrycie przycisku w HTML nie jest zabezpieczeniem.

### 2.2. Główne przepływy

1. **Konto:** rejestracja → logowanie → sesja → profil → zmiana hasła → wylogowanie.
2. **Przeglądanie:** lista pomieszczeń → wyszukiwanie i filtry (lokalizacja, pojemność, wyposażenie, dostępność) → szczegóły pomieszczenia → galeria i kalendarz dostępności.
3. **Rezerwacja:** wybór pomieszczenia i terminu → sprawdzenie dostępności → zapis rezerwacji w transakcji → potwierdzenie e-mail → widok „Moje rezerwacje”.
4. **Anulowanie:** użytkownik anuluje własną przyszłą rezerwację zgodnie z konfigurowalnym cutoffem; administrator może anulować dowolną rezerwację.
5. **Panel administratora:** statystyki → najbliższe rezerwacje → CRUD pomieszczeń, lokalizacji i wyposażenia → zarządzanie użytkownikami i rezerwacjami.
6. **Powiadomienia:** e-mail po utworzeniu i anulowaniu rezerwacji oraz przypomnienie wysyłane przez CRON.

### 2.3. Docelowy model domenowy

| Encja | Rola | Kluczowe pola |
|---|---|---|
| `users` | konta i role | `id`, `name`, `email` (unikalny, bez względu na wielkość liter), `password_hash`, `role`, `is_blocked`, `created_at`, `updated_at` |
| `user_settings` | preferencje użytkownika | `user_id` PK/FK, `language`, `theme`, `email_notifications`, `created_at`, `updated_at` |
| `locations` | budynki, piętra lub strefy | `id`, `name`, `address`, `description`, `is_active`, `created_at`, `updated_at` |
| `rooms` | sale i stanowiska | `id`, `location_id` FK, `name`, `description`, `capacity`, `kind` (`room`/`workstation`), `is_active`, `created_at`, `updated_at` |
| `equipment` | słownik wyposażenia | `id`, `name`, `description`, `is_active` |
| `room_equipment` | wiele-do-wielu | `room_id`, `equipment_id`, `quantity`, PK złożony |
| `room_photos` | galeria pomieszczenia | `id`, `room_id` FK, `path`, `alt_text`, `sort_order`, `created_at` |
| `reservations` | rezerwacje | `id`, `user_id` FK, `room_id` FK, `starts_at`, `ends_at`, `purpose`, `status` (`confirmed`/`cancelled`), `created_at`, `updated_at`, `cancelled_at` |
| `reservation_reminders` | idempotencja przypomnień | `id`, `reservation_id` FK, `reminder_type`, `sent_at`, unikalne `(reservation_id, reminder_type)` |

Wszystkie daty rezerwacji przechowuj jako `TIMESTAMPTZ`, a w UI pokazuj w strefie czasowej użytkownika. `starts_at` i `ends_at` oznaczają przedział `[starts_at, ends_at)`.

### 2.4. Reguły biznesowe i invariants

- **Zakaz nakładających się rezerwacji:** ten sam `room_id` nie może mieć dwóch aktywnych rezerwacji, których przedziały czasu się nakładają. Weryfikacja w aplikacji ma być przyjazna dla użytkownika, ale ostateczną gwarancją jest baza:
  - rozszerzenie `btree_gist`,
  - constraint `EXCLUDE USING gist (room_id WITH =, tstzrange(starts_at, ends_at, '[)') WITH &&) WHERE (status <> 'cancelled')`,
  - zapis w transakcji; naruszenie constraintu tłumaczone na kontrolowane `409 Conflict`, nie `500`.
- `ends_at` musi być późniejsze niż `starts_at`; rezerwacja nie może zaczynać się w przeszłości. Maksymalny czas rezerwacji i cutoff anulowania trzymaj w konfiguracji, nie rozsiewaj po kodzie.
- Aktywna rezerwacja to `status = 'confirmed'`. Anulowana rezerwacja nie blokuje terminu, ale pozostaje w historii.
- Użytkownik może anulować tylko własną rezerwację; administrator może anulować każdą. Sprawdzaj to po stronie serwera, nie tylko przez widoczność przycisku.
- Pomieszczenia, lokalizacje i wyposażenie usuwaj jako soft delete (`is_active = false`) albo przez `ON DELETE RESTRICT`, jeśli mają historię. Nie kasuj historii rezerwacji przez `CASCADE`.
- E-mail wysyłaj po commicie rezerwacji. Błąd SMTP nie może cofać poprawnie zapisanej rezerwacji; zapisz błąd w logu i pozwól ponowić wysyłkę.
- CRON wysyła przypomnienia idempotentnie: `reservation_reminders` z unikalnym `(reservation_id, reminder_type)` lub równoważny mechanizm.
- Wszystkie listy i szczegóły respektują role i właściciela zasobu. Nie ufaj `user_id`, `role` ani `room_id` przesłanym z formularza bez weryfikacji.

### 2.5. Interfejs i Figma

Projekt graficzny: [Figma — Roomly](https://www.figma.com/design/gJzJ7034mSXKZHaxMkkB1H).

Figma jest **głównym źródłem prawdy** dla wyglądu: kolorystyki, typografii, układu, proporcji kart i paneli oraz nawigacji. Nie projektuj aplikacji od nowa i nie zastępuj przygotowanych widoków własnym, uproszczonym interfejsem. Jeśli nie masz dostępu do Figmy lub brakuje konkretnego widoku, poproś użytkownika o eksport/zrzuty zamiast zgadywać.

### 2.6. Aktualny etap

Aktualny stan (2026-11-19):

- temat i model domenowy Roomly są opisane w `Projekt.txt` oraz w tym pliku;
- istnieje konfiguracja Docker Compose z usługami `server`, `php`, `db`, `ollama`, `mailpit`, `pgadmin-wdpai`;
- działa front controller `public/index.php`, router `App\Routing`, `AppController`, `View`, `Translator` oraz strona główna z layoutem i przełączaniem PL/EN;
- `composer.json` z mapowaniem PSR-4 `App\` → `src/` i wygenerowany autoloader są gotowe;
- interfejs z Figmy: tokeny i komponenty w `public/css/style.css`, sidebar + topbar na desktopie, dolna nawigacja na mobile, katalog `/rooms` z wyszukiwaniem i filtrami, karty sal, szczegóły `/rooms/{id}`, stany puste i błędy `400`/`404`/`500`;
- testy jednostkowe routingu, filtra sal, repozytorium w pamięci i odmiany PL/EN (PHPUnit, 38 testów);
- README zawiera zrzuty ekranu, architekturę, routing, raport WCAG i Security Bingo;
- brakuje schematu bazy i danych SQL, uwierzytelniania, rezerwacji, JavaScriptu/Fetch, panelu administratora, uploadu zdjęć, e-maili/CRON, motywu ciemnego i wdrożenia.

To nie jest jeszcze działająca aplikacja domenowa. Agent ma ją rozwijać zgodnie z roadmapą, nie opisywać jako ukończoną.

---

## 3. Zakres i kryteria sukcesu

Projekt obejmuje **36 kryteriów**. Za każde można dostać 1 pkt (działa i można zweryfikować), 0,5 pkt (częściowe lub niestabilne) albo 0 pkt (brak). Wynik procentowy: `punkty / 36 × 100%`.

| Punkty | Wynik | Ocena |
|---|---|---|
| 34–36 | 94–100% | 5.0 |
| 29,5–33,5 | 81–93% | 4.5 |
| 26–29 | 71–80% | 4.0 |
| 22–25,5 | 61–70% | 3.5 |
| 18,5–21,5 | 51–60% | 3.0 |
| 0–18 | poniżej 51% | 2.0 |

**Projekt uznajemy za gotowy do demo, gdy:**

- klonuje się na czystym środowisku, `.env` powstaje z `.env.example`, a `docker compose up --build` uruchamia aplikację;
- baza ma schemat i bezpieczne dane demonstracyjne, a import działa na pustej bazie;
- działa główny proces biznesowy i można go pokazać na dwóch kontach z różnymi rolami;
- backend jest obiektowy, a kontrolery nie zawierają całego SQL, HTML i logiki biznesowej jednocześnie;
- działają logowanie, sesja, role, CSRF, wylogowanie i serwerowa autoryzacja;
- co najmniej jedno uzasadnione żądanie GET i POST przez Fetch API aktualizuje interfejs bez przeładowania;
- interfejs jest responsywny, ma dark/light mode, sensowny kontrast i obsługę klawiaturą;
- interfejs działa po polsku i angielsku, a teksty pochodzą z mechanizmu tłumaczeń;
- testy jednostkowe uruchamiają się jednym poleceniem i przechodzą;
- README zawiera wszystkie wymagane sekcje, ERD w Mermaid, zrzuty ekranu, raport WCAG, Security Bingo, opis CRON/e-mail i link do wdrożenia;
- aplikacja jest wdrożona publicznie albo przynajmniej ma gotową konfigurację produkcyjną i opisany deploy.

---

## 4. Stack technologiczny

| Warstwa | Technologia | Zasady |
|---|---|---|
| Backend | PHP 8.3+ | `declare(strict_types=1)` w każdym pliku, typy parametrów i zwracanych wartości |
| Autoloading | Composer, PSR-4 | Jedno `require_once vendor/autoload.php` w punkcie wejścia |
| Serwer HTTP | Nginx | Document root to `public/`, cały ruch do `public/index.php` |
| PHP runtime | PHP-FPM | Kontener `php`, rozszerzenia `pdo_pgsql`, `pgsql`, `gd`, `zip`, `opcache` |
| Baza danych | PostgreSQL 16+ | Tylko PostgreSQL; zapytania przez PDO, prepared statements |
| Konteneryzacja | Docker, Docker Compose | `docker compose up --build` jako jedyny wspierany sposób startu |
| Frontend | HTML, CSS, JavaScript bez frameworka | Bez React/Vue/Angular i bez build-stepa, chyba że użytkownik wyrazi zgodę |
| Testy | PHPUnit | Testy jednostkowe logiki backendu, uruchamiane w kontenerze `php` |
| E-mail | PHPMailer + SMTP | Lokalnie Mailpit `mailpit:1025`, panel `http://localhost:8025` |
| AI lokalne | Ollama | Opcjonalne, nie może być warunkiem działania aplikacji |
| Baza w przeglądarce | pgAdmin | Opcjonalne narzędzie developerskie, nie część aplikacji |

**Ograniczenia:**

- Nie dodawaj frameworka PHP (Laravel, Symfony itp.) ani frontendowego.
- Nie dodawaj biblioteki, jeśli wystarczy kilka linii własnego kodu; nowa zależność wymaga uzasadnienia i zgody.
- Nie zmieniaj bazy na SQLite/MySQL „tymczasowo”. Projekt jest oceniany pod PostgreSQL.
- Ollama i pgAdmin są opcjonalne. `docker compose up` nie może się wywracać tylko dlatego, że maszyna nie ma GPU lub nie pobrano modelu.
- Mailpit służy tylko do developmentu; nie jest produkcyjnym serwerem poczty.

---

## 5. Docelowa struktura katalogów

```text
.
├── AGENTS.md
├── README.md
├── composer.json
├── composer.lock
├── .env.example
├── .gitignore
├── docker-compose.yaml
├── compose.production.yaml        # opcjonalnie, na Lab 14
├── Caddyfile                      # opcjonalnie, na Lab 14
├── config/
│   └── config.php                 # konfiguracja aplikacji czytająca env
├── database/
│   ├── 01-schema.sql              # tabele, ograniczenia, indeksy, widoki/funkcje
│   ├── 02-seed.sql                # bezpieczne dane demo
│   └── 03-demo.sql                # opcjonalne dodatkowe dane
├── docker/
│   ├── nginx/
│   │   ├── Dockerfile
│   │   └── nginx.conf
│   ├── php/
│   │   └── Dockerfile
│   └── db/
│       └── Dockerfile             # opcjonalnie; można użyć image: postgres
├── public/                        # jedyny publiczny katalog (document root)
│   ├── index.php                  # front controller
│   ├── css/
│   │   ├── style.css              # tokeny, layout, komponenty
│   │   └── ...                    # style konkretnych widoków
│   ├── js/
│   │   ├── theme.js
│   │   ├── validation.js
│   │   └── ...                    # moduły funkcji
│   ├── uploads/                   # dedykowany katalog uploadów, bez wykonywania PHP
│   └── views/
│       ├── layout.html.php        # wspólny dokument HTML
│       ├── partials/              # header, nav, footer, komunikaty
│       ├── home.html.php
│       ├── auth/
│       │   ├── login.html.php
│       │   └── register.html.php
│       ├── errors/
│       │   └── error.html.php     # wspólny widok błędów HTTP
│       └── ...                    # widoki domenowe
├── src/
│   ├── Routing.php                # App\Routing
│   ├── Database.php               # App\Database, fabryka PDO
│   ├── Controller/                # App\Controller
│   ├── Model/                     # App\Model
│   ├── Repository/                # App\Repository
│   ├── Service/                   # App\Service
│   ├── Security/                  # App\Security (sesja, CSRF, role)
│   └── Support/                   # App\Support (walidacja, tłumaczenia)
├── tests/
│   ├── bootstrap.php
│   └── Unit/
├── translations/
│   ├── pl.php
│   └── en.php
├── storage/
│   ├── logs/
│   └── cache/
└── bin/
    └── cron.php                   # zadanie CRON
```

**Zasady struktury:**

- `public/` jest document rootem Nginx. Nie umieszczaj tam logiki aplikacji, konfiguracji ani sekretów.
- `src/` nie może być dostępne bezpośrednio przez URL.
- Katalogi warstw w `src/` odpowiadają nazwom segmentów namespace (`src/Controller` dla `App\Controller`) — PSR-4 rozróżnia wielkość liter na Linuksie.
- Pliki widoków mają nazwy `kebab-case.html.php`, np. `room-details.html.php`.
- Klasy: `PascalCase.php`; metody i zmienne: `camelCase`; tabele i kolumny: `snake_case`; stałe: `UPPER_SNAKE_CASE`.
- `storage/` i `public/uploads/` nie mogą zawierać plików wykonywalnych; logi i uploady nie trafiają do Gita.
- Nie edytuj `vendor/` ręcznie. Nie commituj `vendor/`, `.env`, logów, cache ani uploadów użytkowników.

---

## 6. Architektura i przepływ żądania

```text
przeglądarka
  → Nginx
  → public/index.php (front controller)
  → App\Routing
  → kontroler (App\Controller)
  → serwis (App\Service) / repozytorium (App\Repository)
  → PDO → PostgreSQL
  → model (App\Model)
  → widok HTML albo odpowiedź JSON
```

### Warstwy i odpowiedzialności

| Element | Odpowiada za | Nie może |
|---|---|---|
| `public/index.php` | bootstrap, autoload, sesja, konfiguracja, złożenie zależności, rejestracja tras, dispatch, obsługa 404 | zawierać logiki biznesowej, SQL, HTML widoków |
| `App\Routing` | dopasowanie metody HTTP i wzorca ścieżki, przekazanie parametrów do akcji | zawierać logiki biznesowej, SQL, HTML |
| Controller | walidacja wejścia, autoryzacja, wywołanie serwisu/repozytorium, wybór widoku lub JSON | zawierać całego SQL, całego HTML, długich obliczeń domenowych |
| Service | logika biznesowa, transakcje, e-mail, upload, tłumaczenia, polityki | znać szczegółów HTTP i HTML |
| Repository | SQL, prepared statements, mapowanie wierszy na modele | renderować HTML, ustawiać nagłówków HTTP, zawierać logiki prezentacji |
| Model | dane domenowe, typy, proste zachowania | znać PDO, SQL, HTML, sesję |
| View | semantyczny HTML, pętle i warunki prezentacji, escapowanie | wykonywać SQL, decyzji biznesowych, logiki routingu |
| API Controller | kontrakt JSON, kody HTTP, walidacja wejścia, autoryzacja | renderować widoku HTML, zwracać `password_hash` |

### Front controller i router

- `public/index.php` to **jedyny** punkt wejścia. Ładuje `vendor/autoload.php`, konfiguruje sesję, tworzy zależności i rejestruje trasy.
- Trasy są **białą listą** w kodzie. Nie przyjmuj nazwy kontrolera ani metody z URL.
- Router obsługuje co najmniej `GET`, `POST`, `PATCH`, `DELETE` (zgodnie z Lab 11). Dla formularzy wystarczy GET/POST, ale API może potrzebować PATCH/DELETE.
- Wzorce: trasa statyczna, parametr ścieżki `{id}` walidowany jako liczba, co najmniej jeden własny wzorzec regex (np. `{slug:[a-z0-9-]+}` albo `{year:\d{4}}`) oraz użyteczny query parameter (np. `?q=`, `?status=`, `?page=`).
- Błędny format parametru → **400 Bad Request**. Poprawny format, ale brak zasobu → **404 Not Found**. Brak logowania → **401**; brak uprawnień → **403**.
- Query parametry waliduj i normalizuj; nie wstawiaj ich bezpośrednio do SQL ani HTML. Używaj ich do realnej funkcji (filtrowanie, wyszukiwanie, paginacja), nie do ozdoby.
- Nie dodawaj logiki biznesowej do routera.

### Kontrolery i zależności

- Kontrolery dziedziczą po `App\Controller\AppController` i są `final`, chyba że istnieje uzasadniony powód.
- Wspólne operacje HTTP (render, JSON, redirect, CSRF, requireLogin, requireRole) trzymaj w `AppController` albo wydziel do małych serwisów, gdy klasa zaczyna puchnąć.
- Zależności przekazuj przez konstruktor. Nie twórz `new Database()` w każdym kontrolerze i repozytorium; połączenie i repozytoria składaj w jednym miejscu (front controller lub prosty kontener).
- Kontroler ma być cienki: odbierz dane, zwaliduj, sprawdź uprawnienia, wywołaj przypadek użycia, przygotuj dane widoku.
- Po udanym POST stosuj **Post/Redirect/Get** i kod 303.
- Nie mieszaj odpowiedzi HTML i JSON w jednej akcji.

### Modele, repozytoria, serwisy

- Model to obiekt domenowy z typami; nie zawiera SQL ani HTML.
- Repozytorium przyjmuje `PDO` przez konstruktor i wykonuje wyłącznie SQL. Każde zapytanie z danymi użytkownika używa prepared statements.
- Serwis zawiera logikę, która nie pasuje do modelu ani repozytorium: polityki haseł, transakcje, wysyłkę e-mail, upload, tłumaczenia, obliczenia.
- Nie kopiuj tego samego zapytania, walidacji ani dużego fragmentu HTML w kilku miejscach. Wydziel metodę, serwis, partial lub komponent.
- Jeżeli operacja zapisuje kilka powiązanych rekordów, użyj transakcji. Jeśli schemat ma `updated_at`, rozważ trigger lub jawne ustawianie w repozytorium — jedno spójne rozwiązanie.

### Baza danych

- Tabele w liczbie mnogiej, `snake_case`: `users`, `projects`, `categories`.
- Klucz główny: `BIGINT GENERATED ALWAYS AS IDENTITY` lub `BIGSERIAL`; klucze obce `BIGINT`.
- Daty: `TIMESTAMPTZ` z domyślnym `CURRENT_TIMESTAMP`.
- Wymagania: `NOT NULL`, `UNIQUE`, `CHECK`, klucze obce, indeksy na kolumnach filtrowanych/sortowanych.
- Relacje i `ON DELETE` dobieraj świadomie: `CASCADE` dla danych zależnych, `RESTRICT` dla słowników, `SET NULL` dla opcjonalnych powiązań. Uzasadnij wybór w README.
- Co najmniej jedno zapytanie z `JOIN` do powiązanych tabel.
- Co najmniej jeden mechanizm: widok, trigger, funkcja PostgreSQL albo transakcja — wynikający z realnego scenariusza, nie dodany „dla punktu”.
- Schemat i dane demo trzymaj w `database/*.sql`. Import ma działać na pustej bazie i nie zawierać prawdziwych danych osobowych ani sekretów.
- Nie publikuj portu PostgreSQL w konfiguracji produkcyjnej.

### Widoki i layout

- Semantyczny HTML: `header`, `nav`, `main`, `section`, `article`, `footer`, `form`, `label`, `button`.
- Wspólny layout w `public/views/layout.html.php`; powtarzające się fragmenty w `partials/`.
- Formularze: `label` powiązany z polem, odpowiedni `type`, `autocomplete`, `required`, czytelne komunikaty błędów, `aria-describedby`/`role="alert"` tam, gdzie to potrzebne.
- Każdą wartość pochodzącą od użytkownika lub z bazy escapuj w kontekście HTML:
  `<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>`.
- Nie buduj HTML przez sklejanie stringów z danymi użytkownika. W JS używaj `textContent`/DOM API, nie `innerHTML` z niezaufanymi danymi.
- Linki i przyciski muszą mieć czytelne nazwy; ikony dekoracyjne `aria-hidden="true"`.

### API i Fetch

- Endpointy API zwracają `application/json; charset=utf-8` i sensowne kody HTTP.
- Spójny kontrakt: `{"data": ...}` dla sukcesu, `{"error": "..."}` dla błędu. Klucze JSON w `camelCase`.
- Nie zwracaj `password_hash`, danych sesji ani wewnętrznych wyjątków.
- GET nie zmienia stanu. POST/PATCH/DELETE wymagają CSRF (np. nagłówek `X-CSRF-Token`) i autoryzacji.
- Frontend obsługuje stan ładowania, pusty wynik, sukces, błąd walidacji, błąd sieci i błąd serwera. Nie zostawiaj interfejsu w stanie „wieczne ładowanie”.
- Co najmniej jedno uzasadnione żądanie GET i jedno POST przez `fetch()` bez przeładowania strony; UI aktualizuje się na podstawie JSON.

---

## 7. Konwencje implementacji

### PHP

- `<?php` + `declare(strict_types=1);` w każdym pliku PHP.
- Typy parametrów, zwracanych wartości i właściwości. Preferuj constructor property promotion i `readonly`, gdy pasuje.
- Klasy `final` domyślnie; `abstract` tylko dla klas bazowych.
- Nazwy klas `PascalCase`, metody `camelCase`, stałe `UPPER_SNAKE_CASE`.
- Nazwy techniczne po angielsku; teksty interfejsu przez tłumaczenia.
- Nie używaj `var_dump`, `print_r`, `die`, `exit` w kodzie produkcyjnym poza kontrolowanym redirect/JSON/error handlerem.
- Nie tłumij błędów operatorem `@`. Łap konkretne wyjątki, loguj szczegóły, użytkownikowi pokazuj bezpieczny komunikat.
- Nie zostawiaj zakomentowanego kodu, nieużywanych importów ani „tymczasowych” plików.
- Komentarze wyjaśniają **dlaczego**, nie powtarzają treści kodu.

### HTML

- Język dokumentu zgodny z aktywnym tłumaczeniem: `<html lang="pl">` / `<html lang="en">`.
- Poprawna hierarchia nagłówków (`h1` → `h2` → `h3`), jeden główny `h1` na widok.
- Formularze zawsze z etykietami; przyciski z `type`; obrazy z `alt` (puste `alt=""` tylko dla dekoracji).
- Nie używaj tabel do layoutu; tabele tylko do danych tabelarycznych.
- Nie powielaj całego dokumentu HTML w każdym widoku — używaj layoutu i partiali.

### CSS

- Jeden system tokenów w `public/css/style.css`: `--color-bg`, `--color-surface`, `--color-text`, `--color-primary`, `--color-border`, `--color-focus`, `--shadow-soft` itd.
- Motywy przez `html[data-theme="light"]` / `html[data-theme="dark"]`, bez duplikowania całych arkuszy.
- Mobile-first, elastyczne layouty (Flexbox/Grid), uzasadnione media queries. Co najmniej 5 istotnych widoków musi działać na telefonie, tablecie i desktopie.
- Brak poziomego przewijania i nachodzenia elementów. Sprawdź długie nazwy, tabele, formularze i nawigację.
- Nie używaj przypadkowych stylów inline. Style przenoś do arkuszy.
- Fokus zawsze widoczny (`:focus-visible`), kontrast tekstu co najmniej 4.5:1 dla normalnego tekstu.
- Uwzględnij `prefers-reduced-motion`; nie dodawaj długich animacji zmiany motywu.
- Nie przekazuj informacji wyłącznie kolorem (błąd, sukces, stan aktywny).

### JavaScript

- Kod w `public/js/`, podzielony na funkcje lub moduły; bez jednego wielkiego skryptu.
- Walidacja klienta jest dodatkiem, nie zastępuje walidacji serwera.
- Obsługuj zdarzenia przez `addEventListener`, nie atrybuty `onclick` w HTML.
- Używaj `fetch()` z obsługą statusów HTTP, `try/catch`, stanu ładowania i błędów.
- Nie wstawiaj danych z API przez `innerHTML`; używaj `textContent` i tworzenia elementów DOM.
- Skrypty ładowane z `defer`; skrypt motywu w `<head>` musi wykonać się przed CSS, aby uniknąć FOUC.

### SQL

- Nazwy tabel/kolumn `snake_case`; klucze obce `{entity}_id`.
- Zawsze parametryzuj wartości. Nie sklejaj SQL z danymi użytkownika.
- `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`, `PDO::ATTR_EMULATE_PREPARES => false`, `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`.
- Nie ujawniaj użytkownikowi treści wyjątku PDO (host, nazwa bazy, fragment SQL).
- Migracje/skrypty numeruj i utrzymuj w spójnej kolejności. Nie modyfikuj wstecznie już wykonanego skryptu produkcyjnego bez migracji.

### Tłumaczenia

- Wszystkie teksty interfejsu, komunikaty walidacji i błędy pobieraj z `translations/pl.php` i `translations/en.php` przez translator/serwis.
- Nie rozsiewaj tekstów po kontrolerach i widokach; nie hardcode'uj polskich napisów w JS, jeśli mają być tłumaczone.
- Zmiana języka ma obejmować także walidację i komunikaty błędów.
- Zapamiętaj wybór języka (sesja/cookie), ustawiaj `<html lang>`, formatuj daty i liczby zgodnie z językiem.
- Klucze tłumaczeń opisowe, np. `auth.login.invalid_credentials`, `validation.required`.

### Git

- `main` ma być zawsze działającą wersją.
- Pracuj na małych gałęziach funkcji, scalaj do `main` po weryfikacji.
- Commity opisują jedną logiczną zmianę; używaj prefiksów `feat:`, `fix:`, `docs:`, `test:`, `refactor:`, `chore:`.
- Nie rób jednego zbiorczego commita z całym projektem.
- Przed commitem sprawdź `git status` i `git diff`; nie commituj plików tymczasowych, logów, `.env`, `vendor/`, uploadów.
- Nie przepisuj historii ani nie force-pushuj bez wyraźnej zgody.

---

## 8. Bezpieczeństwo — obowiązkowa checklista

Każda funkcja przed zakończeniem musi przejść tę listę:

- [ ] Hasła: `password_hash($password, PASSWORD_DEFAULT)` przy rejestracji, `password_verify()` przy logowaniu. Nigdy MD5/SHA1/plain text.
- [ ] SQL: wyłącznie prepared statements; żadnej konkatenacji danych użytkownika.
- [ ] Walidacja: wszystkie reguły powtórzone po stronie serwera, także dla pól ukrytych i API.
- [ ] Wyjście: `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` w HTML; `json_encode` dla JSON; brak `innerHTML` z niezaufanymi danymi.
- [ ] Autoryzacja: sprawdzana na serwerze przy każdym chronionym żądaniu, nie tylko przez ukrycie przycisku.
- [ ] Role: co najmniej `USER` i `ADMIN`, zapisane w bazie, różne możliwości, osobne konta demo.
- [ ] Sesja: `session_set_cookie_params` przed startem, `HttpOnly`, `SameSite=Lax`, `Secure` na HTTPS, `session_regenerate_id(true)` po logowaniu.
- [ ] CSRF: token dla każdego formularza zmieniającego stan i dla mutujących żądań API; porównanie `hash_equals`.
- [ ] Wylogowanie: POST + CSRF, wyczyszczenie `$_SESSION`, usunięcie cookie, `session_destroy()`.
- [ ] Upload: limit rozmiaru, `finfo_file`/MIME, dozwolone rozszerzenia, losowa nazwa, katalog poza kodem lub dedykowany `public/uploads/` bez wykonywania PHP; w bazie tylko ścieżka/URL.
- [ ] Sekrety: tylko `.env`/environment, `.env` w `.gitignore`, `.env.example` bez prawdziwych wartości.
- [ ] Błędy: użytkownik dostaje ogólny komunikat; szczegóły trafiają do logów, nie do HTML/JSON.
- [ ] Nagłówki: rozważ `Content-Security-Policy`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`; nie osłabiaj ich bez powodu.
- [ ] Rate limiting / blokada konta: mile widziane, ale nie zamiast podstawowej walidacji i haseł.

**Security Bingo:** w `README.md` ma powstać tabela z dowodem dla każdego zaznaczonego pola: opis, plik/linia lub zrzut, sposób weryfikacji. Nie wystarczy napisać „zrobione”.

---

## 9. Testy i weryfikacja

### Testy jednostkowe

- PHPUnit w `tests/`, autoload przez Composer.
- Testuj istotną logikę backendu, np. walidację, politykę haseł, tłumaczenia, obliczenia domenowe, dopasowanie routera, mapowanie modeli.
- Każdy ważny przypadek: poprawny i błędny. Testy mają być izolowane, powtarzalne i niezależne od kolejności.
- Testy jednostkowe nie powinny wymagać działającej bazy; jeśli potrzebujesz bazy, wydziel testy integracyjne i opisz je osobno.
- Polecenie uruchomienia musi być w README i w tym pliku.

### Weryfikacja ręczna po zmianie

- [ ] `php -l` dla każdego zmienionego pliku PHP.
- [ ] `composer dump-autoload`, jeśli zmieniałeś namespace, klasę lub mapowanie PSR-4.
- [ ] Poprawna trasa działa, a nieistniejąca daje kontrolowane 404.
- [ ] Błędny parametr ścieżki daje 400, nie 500.
- [ ] Formularz: poprawny, błędny, brak CSRF, puste pola, za długie wartości.
- [ ] Logowanie: poprawne, błędne hasło, nieistniejący e-mail, konto zablokowane (jeśli jest).
- [ ] Uprawnienia: użytkownik bez roli nie wejdzie na trasę admina (403), niezalogowany dostaje 401/przekierowanie.
- [ ] API: 200/201, 204, 400, 401, 403, 404, 422; JSON poprawny; brak `password_hash`.
- [ ] Motyw: zapis w `localStorage`, pierwszy wybór z `prefers-color-scheme`, brak FOUC, klawiatura.
- [ ] Responsywność: telefon/tablet/desktop, brak poziomego scrolla.
- [ ] WCAG: klawiatura, widoczny fokus, etykiety, `alt`, kontrast.
- [ ] E-mail: wiadomość widoczna w Mailpit, błąd SMTP obsłużony.
- [ ] CRON: uruchomienie ręczne, log, powtórne uruchomienie nie psuje danych.
- [ ] `docker compose logs` bez nowych błędów i warningów.

### Komendy

```bash
# Start
docker compose up --build -d

# Status i logi
docker compose ps
docker compose logs -f server php db

# Składnia PHP
docker compose exec -T php php -l src/Routing.php

# Composer
docker compose run --rm php composer install
docker compose exec php composer dump-autoload

# Testy
docker compose exec php vendor/bin/phpunit

# Baza
docker compose exec db psql -U "$POSTGRES_USER" -d "$POSTGRES_DB"
```

Na Windows PowerShell, jeśli przekazujesz plik do `psql`:

```powershell
Get-Content database/01-schema.sql -Raw |
  docker compose exec -T db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB"'
```

Nie używaj `docker compose down -v` bez potwierdzenia — usuwa wolumen bazy i dane demo.

---

## 10. Praca z agentem AI

### Przed zmianą

1. Sprawdź `git status` i `git diff`; upewnij się, że nie nadpiszesz pracy użytkownika.
2. Przeczytaj pliki bezpośrednio związane z zadaniem: trasę, kontroler, serwis/repozytorium, widok, test, README.
3. Ustal, które kryterium z Lab 15 lub które laboratorium realizujesz.
4. Jeśli brakuje kontekstu, zadaj jedno konkretne pytanie zamiast zgadywać.

### Plan i zmiana

1. Przedstaw plan 3–6 kroków i listę plików.
2. Wprowadź najmniejszą spójną zmianę realizującą wymaganie.
3. Zachowaj istniejące trasy i zachowanie, chyba że zadanie wprost mówi inaczej.
4. Nie dodawaj nowych zależności bez uzasadnienia.
5. Nie zmieniaj plików niezwiązanych z zadaniem.

### Po zmianie

1. Uruchom `php -l`, testy i odpowiednie żądania HTTP.
2. Sprawdź przypadek poprawny, błędny i brak zasobu.
3. Zaktualizuj README, jeśli zmieniła się funkcja, trasa, schemat bazy, konfiguracja lub sposób uruchomienia.
4. Zaktualizuj ten plik, jeśli zmieniła się architektura lub konwencje.
5. Pokaż `git diff` i podsumuj: co zmieniono, dlaczego, co zweryfikowano, jakie ryzyka pozostały.

### Czego agent nie robi bez zgody

- Nie zmienia przyjętego tematu projektu (Roomly) ani nie wymyśla reguł biznesowych.
- Nie commituje i nie wypycha zmian — commit i push wykonuje wyłącznie użytkownik; po każdej ukończonej jednostce pracy agent tylko zgłasza, że zmiana jest gotowa do commita.
- Nie przepisuje historii Git (rebase, amend, force push) bez wyraźnego polecenia użytkownika i uzasadnienia.
- Nie usuwa ani nie nadpisuje zmian użytkownika.
- Nie wykonuje destrukcyjnych poleceń (`down -v`, `rm -rf`, `git reset --hard`, force push).
- Nie commituje sekretów ani danych osobowych.
- Nie dodaje frameworka, ORM-a, biblioteki frontendowej ani płatnej usługi.
- Nie zmienia konfiguracji produkcyjnej bez wyraźnego zadania.
- Nie twierdzi, że testy przeszły, jeśli ich nie uruchomiono.

---

## 11. Roadmapa laboratoriów

| Lab | Temat | Efekt w projekcie | Powiązane kryteria |
|---|---|---|---|
| 1 | Git, Docker, IDE | Działający Nginx + PHP-FPM, `.env.example`, `.gitignore`, `README.md`, weryfikacja `docker compose config` | 2, 7, 35 |
| 2 | MVC, routing, kontrolery, widoki, Composer | Front controller, router z trasami `/`, `/rooms`, `/rooms/{id}`, `AppController`, layout i widoki, PSR-4 | 3, 4, 5, 9 |
| 3 | CSS | Tokeny, layout, karty pomieszczeń, panel administratora, responsywność | 16, 17 |
| 4 | Vibe coding, AI, Ollama | Ten `AGENTS.md`, bezpieczna praca z agentem, opcjonalna Ollama | 7, 29 |
| 5 | Theme data, dark/light, WCAG | `data-theme`, `localStorage`, anty-FOUC, kontrast, widoczny fokus | 18, 19 |
| 6 | Formularze, HTTP, walidacja | Formularze logowania i rezerwacji, walidacja serwera, PRG, komunikaty błędów | 9, 13, 20 |
| 7 | PostgreSQL, ERD, PDO | Schemat Roomly (`users`, `rooms`, `reservations` itd.), `btree_gist`, `Database`, prepared statements, ERD | 2, 6, 10, 11, 12 |
| 8 | Modele, repozytoria, CRUD | `RoomRepository`, `ReservationRepository`, CRUD pomieszczeń, lokalizacji i rezerwacji | 3, 5, 10, 26 |
| 9 | Auth i sesja | Rejestracja, logowanie, role `USER`/`ADMIN`, `requireLogin`, wylogowanie | 20, 21, 24 |
| 10 | JavaScript, DOM, walidacja | Filtry pomieszczeń, walidacja terminu po stronie klienta, dostępność | 14, 19 |
| 11 | Fetch API, JSON | API dostępności i rezerwacji, GET/POST, stany UI, akcje administratora | 15, 22, 23 |
| 12 | Bezpieczeństwo | CSRF, XSS, SQLi, sesje, autoryzacja właściciela rezerwacji, Security Bingo | 27 |
| 13 | Upload, Mailpit, CRON, streaming, mapy, `.env` | Galeria sal (upload), e-maile o rezerwacji, CRON przypomnień, konfiguracja env | 32, 33, 34, 35 |
| 14 | Testy, WCAG, tłumaczenia, dokumentacja, deploy | PHPUnit, raport WCAG, PL/EN, README, eksport SQL, wdrożenie | 1, 12, 19, 30, 31, 36 |
| 15 | Ewaluacja | Demo rezerwacji na kontach `USER` i `ADMIN`, checklista 36 kryteriów, dowody w README | wszystkie |

---

## 12. Checklista 36 kryteriów

Statusy uzupełniaj po realizacji. „Dowód” to miejsce, w którym oceniający może to zobaczyć — nie deklaracja w tym pliku.

| # | Kryterium | Dowód / gdzie |
|---|---|---|
| 1 | README: problem, funkcje, uruchomienie, wymagania, dane demo, screenshoty, przepływ, architektura, ERD, testy, deploy | `README.md` — wszystkie sekcje |
| 2 | Docker: powtarzalny `up --build`, PostgreSQL, wolumen, opis portów i zmiennych | `docker-compose.yaml`, `README.md`, test na czystym klonie |
| 3 | Architektura: MVC lub osobny frontend/backend, diagram warstw, cienki kontroler | `README.md` + `src/`, `public/` |
| 4 | Routing: statyczna trasa, regex, path param z walidacją, query param, 400/404 | `public/index.php`, `src/Routing.php`, testy HTTP |
| 5 | Obiektowy backend: klasy, enkapsulacja, typy, interfejsy, DI | `src/Controller`, `src/Service`, `src/Repository`, `src/Model` |
| 6 | ERD w Mermaid: tabele, PK, FK, atrybuty, krotności, zgodność ze schematem | `README.md` — blok `mermaid erDiagram` |
| 7 | Git: małe commity, czytelne komunikaty, merge do `main` | `git log --oneline --graph` |
| 8 | Realizacja tematu: główny proces end-to-end (wyszukanie sali → rezerwacja → anulowanie), nie makieta | demo + `README.md` |
| 9 | HTML: semantyczny, wspólne partiale, formularze z etykietami i błędami | `public/views/` |
| 10 | PostgreSQL: realne połączenie, konfiguracja poza kodem, parametryzacja, mapowanie | `src/Database.php`, `src/Repository/`, `.env.example` |
| 11 | Złożoność bazy: kilka powiązanych encji, klucze, ograniczenia, normalizacja, typy | `database/01-schema.sql`, ERD |
| 12 | Eksport `.sql`: schemat + bezpieczne dane demo, import na pustej bazie | `database/*.sql`, README |
| 13 | PHP: wspierana wersja, typy, obsługa błędów, brak warningów | `php -l`, logi, code review |
| 14 | JavaScript: walidacja, zdarzenia, DOM, podział na funkcje/moduły | `public/js/` |
| 15 | Fetch API: GET + POST bez przeładowania, JSON, statusy, loading/sukces/błąd | `public/js/`, kontroler API, demo |
| 16 | Design: spójna typografia, paleta, odstępy, komponenty, CSS poza inline | `public/css/` |
| 17 | Responsywność: min. 5 widoków na telefon/tablet/desktop, brak scrolla poziomego | screenshoty + testy manualne |
| 18 | Dark/light: `data-theme`, `localStorage`, `prefers-color-scheme`, anty-FOUC | `layout.html.php`, `theme.js`, `style.css` |
| 19 | WCAG: klawiatura, fokus, `alt`, etykiety, kontrast, raport z datą i wynikiem | `README.md` — raport WCAG + zrzut |
| 20 | Logowanie: dane z bazy, `password_verify`, bezpieczny komunikat, przekierowanie | `SecurityController`, `UserRepository` |
| 21 | Sesja: utrzymanie między podstronami, regeneracja ID, bezpieczne cookie, brak zaufania do klienta | `public/index.php`, `AppController` |
| 22 | Uprawnienia: serwerowa autoryzacja przed każdą operacją, 401/403 | kontrolery, testy HTTP |
| 23 | Role: min. dwie sensowne role (`USER`, `ADMIN`), różne możliwości, osobne konta demo | `users.role`, seed, demo |
| 24 | Wylogowanie: unieważnienie sesji i cookie, brak dostępu po „Wstecz” | `logout()`, test manualny |
| 25 | Widok/trigger/funkcja/transakcja: realny scenariusz, w eksporcie, pokazany na demo | `EXCLUDE` constraint rezerwacji, transakcja w `ReservationService`, `database/*.sql` |
| 26 | JOIN + FK: zapytanie łączące tabele, przemyślane `ON DELETE`/`ON UPDATE` | `ReservationRepository` (rezerwacje + sale + użytkownicy), schemat, README |
| 27 | Security Bingo: hash, prepared statements, walidacja, escapowanie, autoryzacja, CSRF, upload | `README.md` — tabela dowodów |
| 28 | Brak replikacji: wspólne metody, serwisy, partiale, brak kopii SQL/walidacji/HTML | code review |
| 29 | Czystość kodu: nazwy, krótkie metody, spójny format, brak dead code/debug | code review |
| 30 | Tłumaczenia: PL/EN, teksty z katalogu, walidacja i błędy także | `translations/`, `Translator` |
| 31 | Testy jednostkowe: uruchamialne, izolowane, poprawny i błędny przypadek, polecenie w README | `tests/`, `phpunit.xml`, README |
| 32 | CRON: uzasadnione zadanie cykliczne, harmonogram, log, idempotencja, opis testowania | `bin/cron.php`, `reservation_reminders`, `README.md` |
| 33 | PHPMailer/SMTP: użyteczna wiadomość, konfiguracja z env, obsługa błędów, Mailpit lokalnie | `MailService` (rezerwacja, anulowanie, przypomnienie), `README.md` |
| 34 | Upload: rozmiar, MIME, rozszerzenie, unikalna nazwa, ścieżka w bazie, bezpieczny katalog | `UploadService`, galeria `room_photos`, `public/uploads/`, schemat |
| 35 | `.env`: sekrety poza kodem, `.env.example`, produkcja przez environment/secrets | `.env.example`, `.gitignore`, README |
| 36 | Deploy: publiczny adres, produkcja z `main`, baza/uploady/zmienne, link i opis | `README.md`, `compose.production.yaml` |

---

## 13. Kontrakt README.md

README jest dokumentacją dla człowieka i musi wystarczyć do samodzielnego uruchomienia i oceny. Minimalna struktura:

```markdown
# Nazwa projektu

## Autorzy i zakres odpowiedzialności
## Opis problemu i główne funkcje
## Zrzuty ekranu (co najmniej 5 mobilnych i 5 webowych)
## Architektura
## Diagram ERD bazy danych (Mermaid)
## Routing / endpointy
## Uruchomienie przez Docker
## Zmienne środowiskowe
## Import bazy i dane demonstracyjne
## Testy
## Raport WCAG
## Security Bingo
## Zadanie CRON i wysyłka e-mail
## Deploy
```

Dodatkowo warto mieć:

- tabelę tras: metoda, ścieżka, kontroler/akcja, dostęp (public/auth/admin), opis;
- dane kont demo (bez prawdziwych haseł produkcyjnych);
- opis głównego przepływu krok po kroku;
- znane ograniczenia i pomysły na rozwój;
- informację, gdzie są uploady i logi;
- wersję PHP, PostgreSQL i sposób sprawdzenia środowiska.

**Zasady:**

- README opisuje **rzeczywisty** projekt, nie plan.
- Zrzuty ekranu pokazują realne dane i działające funkcje.
- ERD w Mermaid musi być zgodny z `database/*.sql`.
- Link do wdrożenia i dane demo muszą działać w dniu oddania.
- Nie kopiuj całego AGENTS.md do README; README jest dla ludzi, AGENTS dla agentów.

---

## 14. Znane luki w obecnym repozytorium

To lista startowa. Nie traktuj jej jako wymówki — to zadania do wykonania w pierwszej kolejności.

| Problem | Dlaczego ważne | Docelowo |
|---|---|---|
| `pg-data` jest zadeklarowany, ale nie podłączony do `db` | Dane bazy nie są trwałe | Zamontować `pg-data:/var/lib/postgresql/data` |
| Init SQL montowany z `./docker/db`, gdzie jest Dockerfile | PostgreSQL nie wykona Dockerfile'a jako SQL | Przenieść skrypty do `database/` i montować `./database:/docker-entrypoint-initdb.d:ro` |
| Brak healthchecka `db` i `depends_on` | PHP może startować przed bazą | Dodać healthcheck i `depends_on: condition: service_healthy` |
| Kontener `php` nie dostaje zmiennych `DB_*` | Aplikacja nie ma z czego zbudować połączenia PDO | Przekazać `DB_*` w sekcji `environment` usługi `php` i czytać je w `src/Database.php` |
| Brak schematu bazy i danych SQL | Kryteria 10–12 niezrealizowane | Dodać `database/01-schema.sql`, `database/02-seed.sql` i repozytorium PDO (Lab 7) |
| Brak uwierzytelniania i ról | Kryteria 20–24 niezrealizowane | Dodać logowanie, sesję, `USER`/`ADMIN` i autoryzację (Lab 9) |
| Brak JavaScriptu, Fetch API i motywu ciemnego | Kryteria 14, 15, 18 | Dodać `theme.js`, walidację klienta, endpointy JSON i Fetch (Lab 10–12) |
| Brak rezerwacji i panelu administratora | Główny proces biznesowy i kryteria 8, 25–26 | Zbudować moduł rezerwacji po bazie i logowaniu (Lab 7–12) |
| Szczegóły sali bez formularza rezerwacji i galerii | Kryteria 8, 34 | Panel rezerwacji po module rezerwacji; upload zdjęć w Lab 13 |
| Brak e-maili i CRON | Kryteria 32–33 | `MailService` i `bin/cron.php` (Lab 13) |
| Brak wdrożenia | Kryterium 36 | `compose.production.yaml` i publiczny deploy (Lab 14) |

Po naprawie danej luki usuń ją z tej tabeli albo oznacz jako zrobioną w commitcie.

---

## 15. Środowisko i komendy

### Adresy lokalne

| Usługa | Adres | Uwagi |
|---|---|---|
| Aplikacja | `http://localhost:8080` | `APP_PORT` z `.env` |
| Mailpit (panel) | `http://localhost:8025` | `MAILPIT_PORT` |
| pgAdmin | `http://localhost:5050` | `PGADMIN_PORT` |
| PostgreSQL | `localhost:5433` | `POSTGRES_PORT`; wewnątrz Dockera host `db`, port `5432` |
| Ollama | `http://localhost:11434` | opcjonalnie |

### Wymagane zmienne środowiskowe

Wzór `.env.example` (wartości demo, nie produkcyjne):

```dotenv
APP_PORT=8080

POSTGRES_DB=db
POSTGRES_USER=docker
POSTGRES_PASSWORD=change-me
POSTGRES_PORT=5433

DB_HOST=db
DB_PORT=5432
# Wartości DB_* muszą odpowiadać POSTGRES_* powyżej.
DB_NAME=db
DB_USER=docker
DB_PASSWORD=change-me

PGADMIN_DEFAULT_EMAIL=admin@example.test
PGADMIN_DEFAULT_PASSWORD=change-me
PGADMIN_PORT=5050

MAILPIT_PORT=8025
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM=noreply@example.test
MAIL_FROM_NAME=WDPAI
```

Zasady:

- `.env` jest ignorowany przez Git; `.env.example` jest commitowany.
- W kodzie PHP czytaj konfigurację z environment, nie z literałów w kontrolerach.
- Na produkcji użyj innych, silnych haseł i `APP_ENV=production`.
- Nie wstawiaj prawdziwych danych osobowych do seeda.

### Typowy cykl pracy

```bash
# 1. Punkt kontrolny
git status
git diff

# 2. Start środowiska
docker compose up --build -d

# 3. Praca
#    ... edycja plików ...

# 4. Weryfikacja
docker compose exec -T php php -l src/Routing.php
docker compose exec php composer dump-autoload
docker compose exec php vendor/bin/phpunit
docker compose logs --tail=100 server php db

# 5. Commit
git add <zmienione-pliki>
git commit -m "feat: add project routing"
```

### Diagnostyka

```bash
docker compose ps
docker compose logs -f php
docker compose exec php sh
docker compose exec php php -m
docker compose exec php composer --version
docker compose exec db psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -c "\dt"
```

Jeśli `vendor/` nie istnieje:

```bash
docker compose run --rm php composer install
```

Jeśli po zmianie `composer.json` klasy nie są znajdowane:

```bash
docker compose exec php composer dump-autoload
```

Jeśli baza nie startuje:

```bash
docker compose logs db
docker compose exec db pg_isready -U "$POSTGRES_USER" -d "$POSTGRES_DB"
```

---

## 16. Definition of Done dla zadania

Zadanie jest skończone, gdy:

- [ ] Realizuje konkretne wymaganie lub kryterium z Lab 15.
- [ ] Kod jest obiektowy, typowany i zgodny z podziałem warstw.
- [ ] Dane wejściowe są walidowane po stronie serwera.
- [ ] Wyjście jest escapowane w odpowiednim kontekście.
- [ ] Operacje zmieniające stan mają CSRF i autoryzację.
- [ ] Teksty interfejsu są w tłumaczeniach PL/EN.
- [ ] Widok działa responsywnie i w obu motywach.
- [ ] Są testy lub opisana weryfikacja manualna dla logiki, której nie da się łatwo przetestować jednostkowo.
- [ ] `php -l` i testy przechodzą; wykonane żądania HTTP dają oczekiwane kody.
- [ ] README/AGENTS zaktualizowane, jeśli zmieniła się funkcja, trasa, schemat lub konfiguracja.
- [ ] Brak sekretów, debugowania, dead code i nieużywanych plików.
- [ ] `git diff` jest mały i zrozumiały; zmiany użytkownika nie zostały nadpisane.
- [ ] Podsumowanie dla użytkownika zawiera: co zmieniono, jak zweryfikowano, co pozostaje.

---

## 17. Zakazane praktyki

- Backend strukturalny: funkcje globalne zamiast klas, brak enkapsulacji, „klasy” będące workami na funkcje.
- SQL w kontrolerze, HTML w kontrolerze, logika biznesowa w widoku.
- Sklejanie SQL z danymi użytkownika.
- Przechowywanie haseł jako plain text, MD5, SHA1 lub własna „sól”.
- Porównywanie hasła przez ponowne hashowanie zamiast `password_verify()`.
- Ukrywanie przycisku jako jedyna forma autoryzacji.
- Przyjmowanie roli, właściciela lub ceny z pola formularza bez weryfikacji serwera.
- `innerHTML` z danymi z API/użytkownika.
- Brak CSRF w formularzach zmieniających stan.
- `session_start()` w losowych miejscach bez konfiguracji cookie.
- `die()`/`var_dump()`/stack trace w odpowiedzi dla użytkownika.
- Sekrety w kodzie, README, commitach lub promptach.
- Commitowanie `vendor/`, `.env`, logów, cache, uploadów.
- Edycja `vendor/` ręcznie.
- Dodawanie frameworka/ORM-a/biblioteki frontendowej bez zgody.
- Jeden gigantyczny commit „cały projekt”.
- Zmiana niepowiązanych plików i „przy okazji” refaktoryzacja.
- Usuwanie lub cofanie zmian użytkownika bez pytania.
- Twierdzenie, że funkcja działa, bez uruchomienia jej.
- Opisywanie w README funkcji, których nie ma.
- `docker compose down -v` bez ostrzeżenia i zgody.

---

## 18. Aktualizacja tego pliku

Ten plik jest żywym dokumentem. Aktualizuj go, gdy:

- zmienia się architektura, stack lub struktura katalogów;
- pojawia się nowa konwencja, której agent powinien przestrzegać;
- zmienia się sposób uruchamiania, testowania lub wdrażania;
- zmienia się przyjęty model domenowy lub reguły biznesowe Roomly;
- zmieniają się wymagania prowadzącego.

Nie kopiuj do niego całego README ani całych laboratoriów. Trzymaj tu **operacyjne zasady**, a szczegóły produktu w `README.md`.

**Ostatnia aktualizacja:** 2026-11-19
