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

Apri `.env.local` e:
- Genera `APP_SECRET`: `php -r "echo bin2hex(random_bytes(16));"`
- Verifica `DATABASE_URL` (già preconfigurata per Docker)

```env
APP_ENV=dev
APP_SECRET=<genera_qui>
DATABASE_URL="mysql://gescar_user:gescar_pass@127.0.0.1:3306/gescar_new_local?serverVersion=8.0&charset=utf8mb4"
MAILER_DSN=smtp://localhost:1025
```

> ⚠️ `.env.local` è escluso da `.gitignore` — non committarlo mai.

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

Inserisci la password desiderata, copia l'hash e inserisci l'utente:

```bash
docker exec -it gescar_db mysql -u gescar_user -pgescar_pass gescar_new_local
```

```sql
INSERT INTO user (email, roles, password, nome, cognome, is_active)
VALUES ('admin@gescar.local', '["ROLE_SUPER_ADMIN"]', 'HASH_QUI', 'Carlo', 'Admin', 1);
EXIT;
```

### 7. (Opzionale) Carica dati di test

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

> ⚠️ Le fixtures svuotano il DB — ricrea l'utente admin dopo.

### 8. Avvia il server

```bash
symfony server:start
# oppure
php -S localhost:8000 -t public/
```

Apri http://localhost:8000 e accedi con le credenziali create al passo 6.

---

## Credenziali Docker

| Parametro | Valore |
|---|---|
| Host DB | `127.0.0.1:3306` |
| Database | `gescar_new_local` |
| Utente app | `gescar_user` / `gescar_pass` |
| Utente root | `root` / `root` |

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
make db-shell     # shell MySQL come gescar_user
make open-pma     # apre phpMyAdmin (Windows)
```
