# Setup e Installazione

## Prerequisiti

- PHP 8.2+
- Composer 2.x
- Docker Desktop
- Git
- Symfony CLI (opzionale)

---

## Setup con Docker (consigliato)

### 1. Clona il repository

```bash
git clone https://github.com/CarloGagliolo/Gescar2.git
cd Gescar2
```

### 2. Avvia i container Docker

```bash
docker compose up -d
```

I container avviati sono:

| Container | Porta | Scopo |
|---|---|---|
| `gescar_db` (MySQL 8.0) | `3306` | Database applicazione |
| `gescar_pma` (phpMyAdmin) | `8080` | GUI database → http://localhost:8080 |
| `gescar_mail` (Mailpit) | `8025` | Catch-all email test → http://localhost:8025 |

### 3. Installa le dipendenze PHP

```bash
composer install
```

### 4. Configura l'ambiente

```bash
cp .env.local.dist .env.local
```

Apri `.env.local` e imposta i valori reali per il tuo ambiente. In particolare:
- Genera `APP_SECRET`: `php -r "echo bin2hex(random_bytes(16));"`
- Imposta `DATABASE_URL` con le credenziali che hai scelto in `docker-compose.yml`
- Imposta `MAILER_DSN` (in sviluppo punta a Mailpit, in produzione al server SMTP reale)

> ⚠️ `.env.local` è escluso da `.gitignore` — non committarlo mai. Contiene le credenziali reali.

### 5. Crea il database ed esegui le migrazioni

Attendi ~10 secondi che MySQL sia pronto, poi:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 6. Crea il primo utente admin

```bash
php bin/console security:hash-password
```

Inserisci la password desiderata, copia l'hash generato, poi inserisci il record nel DB tramite phpMyAdmin o shell MySQL usando le credenziali definite nel tuo `.env.local`.

> ⚠️ Usa una password robusta (min. 12 caratteri, mista maiuscole/minuscole/numeri/simboli).
> Non riutilizzare la password di altri account.

### 7. (Opzionale) Carica dati di test

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

> ⚠️ Le fixtures svuotano il DB — ricrea l'utente admin dopo.
> Non eseguire mai le fixtures in produzione.

### 8. Avvia il server

```bash
symfony server:start
# oppure
php -S localhost:8000 -t public/
```

Apri http://localhost:8000 e accedi con le credenziali create al passo 6.

---

## Credenziali Docker (sviluppo locale)

Le credenziali Docker di default sono definite in `docker-compose.yml`.

> ⚠️ **Le credenziali di default sono solo per sviluppo locale.**
> In produzione usare credenziali forti e uniche, mai condivise con l'ambiente di sviluppo.
> Non committare mai le credenziali reali nel repository.

---

## Comandi utili (Makefile)

```bash
make up           # avvia Docker
make down         # ferma Docker
make migrate      # esegui migrazioni
make fixtures     # carica dati di test
make db-reset     # drop → create → migrate → fixtures
make cache        # svuota cache Symfony
make routes       # mostra tutte le route
make db-shell     # shell MySQL
make open-pma     # apre phpMyAdmin (Windows)
```
