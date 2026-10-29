# Roomly — System Rezerwacji Sal i Stanowisk

Roomly to aplikacja webowa do przeglądania, rezerwowania i zarządzania salami oraz stanowiskami pracy. Użytkownik może wyszukiwać pomieszczenia, filtrować je według lokalizacji, pojemności i wyposażenia, sprawdzać dostępność oraz tworzyć rezerwacje. Administrator zarządza użytkownikami, lokalizacjami, pomieszczeniami, wyposażeniem i rezerwacjami.

Projekt semestralny z przedmiotu **Projektowanie aplikacji internetowych 26/27**. Aplikacja jest rozwijana w PHP 8.3 z PostgreSQL, w architekturze MVC bez frameworka PHP. Środowisko uruchamiane jest przez Docker Compose.

## Główne funkcje

- Rejestracja, logowanie, wylogowanie i zarządzanie profilem.
- Przeglądanie sal i stanowisk w formie kart.
- Wyszukiwanie po nazwie oraz filtrowanie według lokalizacji, pojemności, wyposażenia i dostępności.
- Szczegóły pomieszczenia: galeria, opis, lokalizacja, pojemność, wyposażenie i kalendarz dostępności.
- Tworzenie rezerwacji z ochroną przed nakładającymi się terminami.
- Przeglądanie własnych rezerwacji, ich statusów i historii.
- Anulowanie własnych rezerwacji zgodnie z zasadami systemu.
- Panel administratora: użytkownicy, lokalizacje, pomieszczenia, wyposażenie, rezerwacje i statystyki.
- Powiadomienia e-mail o rezerwacji, anulowaniu i zbliżającym się terminie.
- Wybór języka oraz jasnego/ciemnego motywu.

## Role

- **Użytkownik** — przeglądanie pomieszczeń, rezerwacje, własny profil i ustawienia.
- **Administrator** — wszystkie uprawnienia użytkownika oraz zarządzanie zasobami i rezerwacjami.

## Projekt interfejsu

Projekt graficzny znajduje się w [Figma — Roomly](https://www.figma.com/design/gJzJ7034mSXKZHaxMkkB1H). Jest on głównym źródłem informacji o kolorystyce, typografii, układzie, proporcjach i nawigacji. Nie zastępuj go uproszczonym, własnym interfejsem.

## Autorzy

- Kacper Wesołowski — realizacja projektu

## Status projektu

Projekt jest w fazie Laboratorium 2. Działają: front controller, router (`GET`, `POST`, `PATCH`, `DELETE`, parametry ścieżki, własne wzorce regex, `400`/`404`), warstwa widoków, tłumaczenia PL/EN, strona główna oraz katalog pomieszczeń — `/rooms` z filtrowaniem i `/rooms/{id}`. Lista sal korzysta na razie z danych w pamięci, nie z bazy. Brakuje schematu PostgreSQL, uwierzytelniania, rezerwacji, JavaScriptu i docelowego wyglądu z Figmy.

Zasady pracy i architekturę docelową opisuje [`AGENTS.md`](AGENTS.md).

## Stack technologiczny

| Warstwa | Technologia |
|---|---|
| Backend | PHP 8.3, MVC bez frameworka, Composer PSR-4 |
| Serwer HTTP | Nginx + PHP-FPM |
| Baza danych | PostgreSQL 16, PDO z prepared statements |
| Frontend | HTML5, CSS3, JavaScript, Fetch API |
| E-mail | PHPMailer + SMTP (lokalnie Mailpit) |
| Testy | PHPUnit |
| Konteneryzacja | Docker, Docker Compose |

## Wymagania

- Docker Desktop lub Docker Engine z Docker Compose v2
- Wolne porty: `8080` (aplikacja), `5433` (PostgreSQL), `8025` (Mailpit), `5050` (pgAdmin), opcjonalnie `11434` (Ollama)
- Przeglądarka internetowa

## Uruchomienie

1. Utwórz plik `.env` na podstawie `.env.example`.

   Windows PowerShell:

   ```powershell
   Copy-Item .env.example .env
   ```

   Linux/macOS:

   ```bash
   cp .env.example .env
   ```

2. Zainstaluj zależności PHP i wygeneruj autoloader Composer:

   ```bash
   docker compose run --rm --no-deps php composer install
   ```

3. Uruchom kontenery:

   ```bash
   docker compose up --build -d
   ```

4. Otwórz aplikację:

   - Aplikacja: [http://localhost:8080](http://localhost:8080)
   - Mailpit: [http://localhost:8025](http://localhost:8025)
   - pgAdmin: [http://localhost:5050](http://localhost:5050)

5. Zatrzymanie środowiska:

   ```bash
   docker compose down
   ```

Nie używaj `docker compose down -v` bez potrzeby — usuwa wolumen z danymi bazy.

## Ollama (opcjonalnie)

Domyślne `docker compose up` nie uruchamia Ollamy. Aby włączyć lokalne modele AI:

```bash
docker compose --profile ai up -d ollama
```

Ollama działa również bez GPU (wolniej). Akceleracja NVIDIA wymaga sterowników NVIDIA i NVIDIA Container Toolkit, ale nie jest potrzebna do działania Roomly.

## Struktura projektu

```text
.
├── AGENTS.md
├── README.md
├── Projekt.txt
├── composer.json
├── composer.lock
├── phpunit.xml
├── .env.example
├── .gitignore
├── docker-compose.yaml
├── docker/
│   ├── db/
│   ├── nginx/
│   └── php/
├── public/
│   ├── css/
│   ├── index.php
│   └── views/
├── src/
│   ├── Controller/
│   ├── Model/
│   ├── Repository/
│   ├── Service/
│   ├── Support/
│   └── Routing.php
├── tests/
│   ├── bootstrap.php
│   └── Unit/
└── translations/
```

## Routing

| Metoda | Ścieżka | Opis | Dostęp |
|---|---|---|---|
| GET | `/` | Strona główna | publiczny |
| GET | `/rooms` | Lista sal i stanowisk wraz z filtrami | publiczny |
| GET | `/rooms/{id}` | Szczegóły pomieszczenia; `{id}` musi być liczbą całkowitą | publiczny |

Parametry zapytania obsługiwane przez `/rooms`:

| Parametr | Wartości | Znaczenie |
|---|---|---|
| `q` | tekst do 80 znaków | Szuka w nazwie i lokalizacji, bez rozróżniania wielkości liter |
| `kind` | `room`, `workstation` | Rodzaj pomieszczenia |
| `min_capacity` | liczba 1–1000 | Minimalna liczba miejsc |
| `sort` | `name`, `capacity` | Kolejność wyników (domyślnie `name`) |
| `lang` | `pl`, `en` | Język interfejsu, zapamiętywany w sesji |

Przykład: `/rooms?q=budynek+b&kind=workstation&sort=capacity`.

Router obsługuje metody `GET`, `POST`, `PATCH` i `DELETE`, parametry ścieżki `{id}` z walidacją formatu oraz własne wzorce regex, np. `{slug:[a-z0-9-]+}`. Nieistniejąca trasa zwraca `404`, a błędny format parametru `400`.

- `/rooms/abc` → `400 Bad Request` (parametr nie jest liczbą),
- `/rooms/999` → `404 Not Found` (poprawny format, brak zasobu),
- `/rooms?kind=garage` → `400 Bad Request` (niedozwolona wartość filtra).

## Dane demonstracyjne

Katalog sal korzysta z `App\Repository\InMemoryRoomRepository` — osiem pomieszczeń zapisanych w kodzie, udostępnianych przez interfejs `RoomRepositoryInterface`. Służą one do pokazania routingu, filtrowania i widoków, **nie są warstwą bazy danych**. Schemat PostgreSQL, repozytorium PDO i dane demo w SQL pojawią się w Laboratorium 7; do tego czasu aplikacja nie zapisuje żadnych rezerwacji.

## Testy

Testy jednostkowe uruchamia się w kontenerze `php`:

```bash
docker compose exec php vendor/bin/phpunit
```

Jednorazowo, bez uruchamiania całego środowiska:

```bash
docker compose run --rm php vendor/bin/phpunit
```

Zakres: dopasowanie tras (trasa statyczna, parametry ścieżki, własny regex, `400`/`404`, metody HTTP) oraz walidacja i filtrowanie listy sal (`RoomFilter`, `InMemoryRoomRepository`). Testy nie wymagają bazy danych.

## Zmienne środowiskowe

Wszystkie wymagane zmienne znajdują się w `.env.example`. Plik `.env` jest lokalny i nie trafia do repozytorium.

Najważniejsze zmienne:

| Zmienna | Znaczenie |
|---|---|
| `APP_PORT` | Port aplikacji na komputerze gospodarza |
| `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD` | Konfiguracja kontenera PostgreSQL |
| `POSTGRES_PORT` | Port PostgreSQL na komputerze gospodarza |
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` | Połączenie PHP z PostgreSQL wewnątrz sieci Dockera; wykorzystane po dodaniu warstwy bazy (Lab 7) |
| `PGADMIN_DEFAULT_EMAIL`, `PGADMIN_DEFAULT_PASSWORD` | Konto pgAdmin |
| `PGADMIN_PORT`, `PGADMIN_CONTAINER_NAME` | Port i nazwa kontenera pgAdmin |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_FROM`, `MAIL_FROM_NAME` | Konfiguracja SMTP/Mailpit |

## Zasady developmentu

- Nazwy techniczne w kodzie zapisujemy po angielsku.
- Każdy commit opisuje jedną logiczną zmianę.
- Sekrety przechowujemy wyłącznie w `.env`.
- Przed commitem sprawdzamy `git status` i `git diff`.
