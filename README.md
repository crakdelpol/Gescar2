# Gescar – Gestionale Centro Revisioni

Web application gestionale per centri revisioni auto. Permette di tracciare e gestire scadenze di revisioni, assicurazioni, patenti e bollo per clienti privati e aziende.

---

## Stack Tecnologico

| Layer | Tecnologia | Versione |
|---|---|---|
| Backend | PHP | 8.4+ |
| Framework | Symfony | 7.4 LTS |
| Database | MySQL | 8.4 LTS |
| Frontend | Bootstrap | 5.3.x |
| Auth | Symfony Security | nativo |

---

## Requisiti

- PHP 8.2+ (target: 8.4)
- Composer 2.x
- MySQL 8.x
- Symfony CLI (opzionale, consigliato)

---

## Installazione

### 1. Clona il repository

```bash
git clone https://github.com/TUO_USERNAME/gescar.git
cd gescar
```

### 2. Installa le dipendenze PHP

```bash
composer install
```

### 3. Configura l'ambiente

Crea il file `.env.local` (non versionato) con le tue credenziali:

```env
APP_ENV=dev
APP_SECRET=genera_una_stringa_casuale_di_32_caratteri

DATABASE_URL="mysql://gescar_user:LA_TUA_PASSWORD@127.0.0.1:3306/gescar?serverVersion=8.4&charset=utf8mb4"
```

> ⚠️ **Non committare mai `.env.local`** — contiene le credenziali reali.

### 4. Crea il database e applica le migrazioni

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. (Opzionale) Carica dati di test

```bash
php bin/console doctrine:fixtures:load
```

> ⚠️ Le fixtures usano dati anonimi (Faker). Non includono mai dati di produzione.

### 6. Avvia il server di sviluppo

```bash
symfony server:start
# oppure
php -S localhost:8000 -t public/
```

---

## Struttura del Progetto

```
gescar/
├── config/
│   ├── packages/
│   │   ├── doctrine.yaml
│   │   ├── security.yaml          ← autenticazione Symfony nativa
│   │   └── ...
├── migrations/                    ← migrazioni database Doctrine
├── public/                        ← document root (CSS, JS, immagini)
├── src/
│   ├── Controller/
│   │   ├── AnagraficaController.php
│   │   ├── VetturaController.php
│   │   ├── PatenteController.php
│   │   ├── AssicurazioneController.php
│   │   ├── BolloController.php
│   │   ├── ScadenzeController.php
│   │   ├── DashboardController.php
│   │   └── SecurityController.php
│   ├── Entity/
│   │   ├── Anagrafica.php
│   │   ├── Vettura.php
│   │   ├── Patente.php
│   │   ├── Assicurazione.php
│   │   ├── Bollo.php
│   │   ├── Notifica.php
│   │   └── User.php
│   ├── Repository/
│   ├── Form/
│   ├── Service/
│   │   ├── ScadenzaService.php    ← calcolo stati scadenze
│   │   └── NotificaService.php    ← tracciamento avvisi
│   └── Security/
├── templates/
│   ├── base.html.twig
│   ├── dashboard/
│   ├── security/
│   └── ...
└── .env                           ← placeholder (no segreti reali)
```

---

## Funzionalità

- **Anagrafica clienti** — privati e aziende
- **Gestione veicoli** — con scadenza revisione, assicurazione, bollo
- **Patenti** — tracciamento scadenza per categoria
- **Dashboard scadenze** — colori semaforo (rosso/arancione/giallo/verde)
- **Registro notifiche** — tracciamento chiamate e avvisi inviati ai clienti
- **Autenticazione** — login con Symfony Security (ruoli ADMIN / SUPER_ADMIN)

---

## Convenzioni di Sviluppo

- Entità PHP: `PascalCase` → tabelle DB: `snake_case`
- Route: `app_{entità}_{azione}` (es. `app_vettura_index`)
- Logica di query: nei Repository, non nei Controller
- Logica di business: nei Service
- Attributi PHP 8 (`#[ORM\...]`) — niente annotazioni legacy
- Template Twig con Bootstrap 5.3 — niente jQuery

Vedi `docs/05_CODING_CONVENTIONS.md` per dettagli completi.

---

## Sicurezza

- Non committare mai dati personali reali (il DB contiene >7000 anagrafiche di produzione)
- Usare `.env.local` per le credenziali (escluso da `.gitignore`)
- Le password utenti sono hashate con bcrypt
- Ruoli: `ROLE_SUPER_ADMIN` (accesso completo) e `ROLE_ADMIN` (operatore)

---

## Roadmap

Vedi `docs/04_ROADMAP.md` per la lista completa dei task tecnici.

### Prossimi step prioritari
1. Migrazione MySQL 5.7 → 8.4 LTS
2. Completamento Entity `User` e sistema login nativo
3. Entity `Notifica` + interfaccia registro avvisi
4. Dashboard scadenze con filtri e colori semaforo
5. Upgrade Bootstrap 4 → 5.3 (rimozione jQuery)

---

## Licenza

Proprietaria – uso interno. Vedi il proprietario del progetto per dettagli.
