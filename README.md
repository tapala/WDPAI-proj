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

Projekt znajduje się w fazie szkieletu. Istnieje konfiguracja kontenerów Docker oraz strona startowa; routing, widoki, baza danych, uwierzytelnianie i pozostałe funkcje będą dodawane w kolejnych laboratoriach.

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

2. Uruchom kontenery:

   ```bash
   docker compose up --build -d
   ```

3. Otwórz aplikację:

   - Aplikacja: [http://localhost:8080](http://localhost:8080)
   - Mailpit: [http://localhost:8025](http://localhost:8025)
   - pgAdmin: [http://localhost:5050](http://localhost:5050)

4. Zatrzymanie środowiska:

   ```bash
   docker compose down
   ```

Nie używaj `docker compose down -v` bez potrzeby — usuwa wolumen z danymi bazy.

## Struktura projektu

```text
.
├── AGENTS.md
├── README.md
├── Projekt.txt
├── .env.example
├── .gitignore
├── docker-compose.yaml
├── docker/
│   ├── db/
│   ├── nginx/
│   └── php/
├── public/
│   ├── index.php
│   └── views/
├── src/
│   └── controllers/
└── Routing.php
```

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
