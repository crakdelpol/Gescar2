# Gescar – Gestionale Centro Revisioni

Web application gestionale per centri revisioni auto. Permette di tracciare e gestire scadenze di revisioni, assicurazioni, patenti e bollo per clienti privati e aziende.

> Repository: [github.com/CarloGagliolo/Gescar2](https://github.com/CarloGagliolo/Gescar2)

---

## Stack Tecnologico

| Layer | Tecnologia | Versione |
|---|---|---|
| Backend | PHP | 8.2+ |
| Framework | Symfony | 7.3 |
| Database | MySQL | 8.0 (Docker locale) |
| ORM | Doctrine ORM | 3.x |
| Frontend | Bootstrap | 5.3 CDN |
| Icone | Bootstrap Icons | 1.11 |
| Auth | Symfony Security | nativo |
| Log | MonologBundle | ^4.0 |

---

## Requisiti

- PHP 8.2+
- Composer 2.x
- Docker Desktop (metodo consigliato)
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

Verranno avviati:
- **MySQL 8.0** su porta `3306`
- **phpMyAdmin** su `http://localhost:8080`
- **Mailpit** (email di test) su `http://localhost:8025`

### 3. Installa le dipendenze PHP

```bash
composer install
```

### 4. Configura l'ambiente

```bash
cp .env.local.dist .env.local
```

Apri `.env.local` e genera un `APP_SECRET`:
```bash
php -r "echo bin2hex(random_bytes(16));"
```

> ⚠️ **Non committare mai `.env.local`** — contiene le credenziali reali.

### 5. Crea il database e applica le migrazioni

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 6. Crea il primo utente admin

```bash
php bin/console security:hash-password
```

Poi inserisci l'utente via phpMyAdmin o da terminale:

```bash
docker exec -it gescar_db mysql -u gescar_user -pgescar_pass gescar_new_local
```

```sql
INSERT INTO user (email, roles, password, nome, cognome, is_active)
VALUES ('admin@gescar.local', '["ROLE_SUPER_ADMIN"]', 'HASH_QUI', 'Carlo', 'Admin', 1);
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

---

## Struttura del Progetto

```
Gescar2/
├── config/
│   └── packages/
│       ├── doctrine.yaml          ← MySQL, naming underscore
│       ├── security.yaml          ← autenticazione Symfony nativa
│       ├── monolog.yaml           ← logging su file
│       └── ...
├── docs/                          ← documentazione di progetto
├── migrations/                    ← migrazioni database Doctrine
├── public/                        ← document root (CSS, JS, immagini)
├── src/
│   ├── Controller/
│   ├── Entity/
│   │   ├── Anagrafica.php
│   │   ├── Vettura.php
│   │   ├── Patente.php
│   │   ├── Assicurazione.php
│   │   ├── Bollo.php
│   │   ├── Notifica.php
│   │   └── User.php
│   ├── Form/
│   ├── Repository/
│   └── Service/
│       ├── ScadenzaService.php    ← calcolo stati semaforo scadenze
│       └── NotificaService.php    ← tracciamento avvisi ai clienti
├── templates/
├── docker-compose.yml
├── Makefile                       ← shortcut comandi comuni
├── .env.local.dist                ← template credenziali (da copiare)
└── CONTEXT.md                     ← contesto per sessioni AI
```

---

## Funzionalità

- **Anagrafica clienti** — privati e aziende
- **Gestione veicoli** — con scadenza revisione, assicurazione, bollo
- **Patenti** — tracciamento scadenza per categoria
- **Scadenze** — vista semaforo (scaduto / in scadenza / in avvicinamento / ok)
- **Registro notifiche** — tracciamento chiamate e avvisi inviati ai clienti
- **Autenticazione** — login con Symfony Security (ROLE_ADMIN / ROLE_SUPER_ADMIN)

---

## Convenzioni di Sviluppo

- Entità PHP `PascalCase` → tabelle DB `snake_case`
- Route: `app_{entità}_{azione}` (es. `app_vettura_index`)
- Query: nei Repository, mai nei Controller
- Logica di business: nei Service
- Attributi PHP 8 `#[ORM\...]` — niente annotazioni `@ORM\` legacy
- Bootstrap 5.3 da CDN — **zero jQuery**

Vedi `docs/05_CODING_CONVENTIONS.md` per dettagli completi.

---

## Sicurezza

- Non committare mai dati reali (il DB di produzione contiene >7000 anagrafiche)
- Usare `.env.local` per le credenziali (escluso da `.gitignore`)
- Password utenti hashate con bcrypt (cost 13)
- Ruoli: `ROLE_SUPER_ADMIN` (accesso completo) e `ROLE_ADMIN` (operatore)

---

## Roadmap

Vedi `docs/04_ROADMAP.md` per la lista completa.

### Prossimi step
- Collegare ricerca navbar → filtro `?q=` in AnagraficaController
- Completare template `anagrafica/show.html.twig` con lista veicoli e storico notifiche
- Aggiungere `telefono2` all'entity Anagrafica
- Paginazione nelle liste (KnpPaginatorBundle o manuale)
- Upgrade MySQL 8.0 → 8.4 LTS

---

## Licenza

Proprietaria – uso interno Centro Revisioni Charlot.
