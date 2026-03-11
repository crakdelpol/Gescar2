# CONTEXT.md — Gescar2
> File di contesto per sessioni di sviluppo AI (Claude / Copilot).
> Aggiornato al: **2026-03-11**
> Repository: https://github.com/CarloGagliolo/Gescar2

---

## 1. Cos'è il progetto

Gestionale web per un **centro revisioni auto** (Centro Revisioni Charlot).
Permette agli operatori di tracciare e gestire le scadenze di:
- **Revisioni veicoli** (periodicità legge italiana: 4 anni + ogni 2)
- **Assicurazioni** (annuale, con storico polizze)
- **Bollo auto** (annuale per mese di immatricolazione)
- **Patenti** (variabile per categoria e età)

Il database di produzione contiene ~7.000 anagrafiche e ~4.500 veicoli (dati reali — non committare mai dump).

---

## 2. Stack tecnico attuale

| Layer | Tecnologia | Versione | Note |
|---|---|---|---|
| PHP | PHP | 8.2+ (target 8.4) | Attributi PHP 8 per ORM |
| Framework | Symfony | **7.3** | Target upgrade a 7.4 LTS |
| Database | MySQL | **5.7** (prod) → target **8.4 LTS** | Schema `gescar` |
| ORM | Doctrine ORM | 3.x | Naming strategy: underscore |
| Template | Twig 3 | — | |
| CSS/JS | **Bootstrap 5.3** CDN | + Bootstrap Icons 1.11 | **Zero jQuery** |
| Autenticazione | Symfony Security nativo | — | Sostituisce FOSUserBundle |
| Server dev | Symfony CLI / PHP built-in | — | Document root: `public/` |

### Versioni dipendenze chiave (composer.json)
```
symfony/framework-bundle  >=7.3.3
symfony/security-bundle   7.3.*
symfony/form              7.3.*
doctrine/orm              ^3.5.2
doctrine/doctrine-bundle  ^2.16.1
```

---

## 3. Struttura directory

```
Gescar2/
├── config/
│   └── packages/
│       ├── security.yaml        ← login form, bcrypt cost 13, ruoli ADMIN/SUPER_ADMIN
│       ├── doctrine.yaml        ← MySQL, naming underscore
│       └── ...
├── docs/                        ← documentazione di progetto (non codice)
│   ├── 01_PROJECT_OVERVIEW.md
│   ├── 02_DATABASE_SCHEMA.md
│   ├── 03_BUSINESS_RULES.md
│   ├── 04_ROADMAP.md
│   └── 05_CODING_CONVENTIONS.md
├── migrations/                  ← Doctrine migrations
├── public/                      ← document root
│   ├── css/custom.css           ← stili custom (Bootstrap base è da CDN)
│   └── img/                     ← logo crc_logo.png, datee_logo.png
├── src/
│   ├── Controller/
│   ├── Entity/
│   ├── Form/
│   ├── Repository/
│   └── Service/
└── templates/
```

---

## 4. Mappa Entity → Tabella DB

### Anagrafica → `anagrafica`
```
id, nome, cognome, tipo (privato|azienda),
luogo_nascita, codice_fiscale, partita_iva,
residenza, sede_legale, email,
telefono,           ← campo singolo (TODO: aggiungere telefono2)
codice_destinatario, note
```
**Relazioni:** OneToMany → Vettura (intestatario), OneToOne → Patente

### Vettura → `vettura`
```
id, targa, numero_telaio,
tipo_vettura (autovettura|autocarro|motoveicolo|rimorchio|ciclomotore),
carburante (benzina|gasolio|gpl|metano|elettrico),
marca, modello,
data_ultima_revisione, data_scadenza_revisione,
data_scadenza_impianto,   ← bombole GPL/metano
note,
intestatario_id FK → anagrafica(id)
```
**TODO Migration #5:** aggiungere `esente_revisione TINYINT(1) DEFAULT 0`

### Assicurazione → `assicurazione`
```
id, data_scadenza_assicurazione, note,
vettura_id FK → vettura(id)   [attualmente OneToOne → UNIQUE, da rimuovere per storico]
```
**TODO Migration #2:** rimuovere UNIQUE, aggiungere `attiva`, `data_inizio`, `compagnia`, `numero_polizza`

### Bollo → `bollo`
```
id, data_scadenza_bollo, note,
vettura_id FK → vettura(id)   [attualmente OneToOne → UNIQUE, da rimuovere per storico]
```
**TODO Migration #1:** rimuovere UNIQUE, aggiungere `attiva`, `importo`, `super_bollo`, `pagato`, `data_pagamento`

### Patente → `patente`
```
id, numero_patente, categoria_patente (JSON array),
data_scadenza_patente, note,
intestatario_id FK → anagrafica(id)   [OneToOne]
```

### User → `user`  ← DA CREARE con Migration #4
```
id, email UNIQUE, roles JSON, password (bcrypt),
nome, cognome, is_active
```
Ruoli: `ROLE_ADMIN` (default), `ROLE_SUPER_ADMIN` (gerarchia)

### Notifica → `notifica`  ← DA CREARE con Migration #3
```
id,
anagrafica_id FK → anagrafica(id) ON DELETE CASCADE,
vettura_id FK → vettura(id) ON DELETE SET NULL,
tipo_scadenza (revisione|assicurazione|bollo|patente),
canale (telefono|sms|email|whatsapp),
data_invio DATETIME DEFAULT NOW(),
esito (inviata|non_risponde|rinnovato|non_interessato),
note VARCHAR(255),
utente_id FK → user(id) ON DELETE SET NULL
```

---

## 5. Mappa Route → Controller → Template

| Route name | URL | Controller | Template |
|---|---|---|---|
| `app_scadenze_home` | `/` | ScadenzeController::index | scadenze/index |
| `app_scadenze_index` | `/scadenze/{da}/{a}` | ScadenzeController::search | scadenze/index |
| `app_dashboard_index` | `/dashboard/` | DashboardController::index | dashboard/index |
| `app_login` | `/login` | SecurityController::login | security/login |
| `app_logout` | `/logout` | SecurityController::logout | — |
| `app_anagrafica_index` | `/anagrafica` | AnagraficaController | anagrafica/index |
| `app_anagrafica_show` | `/anagrafica/{id}` | AnagraficaController | anagrafica/show |
| `app_anagrafica_new` | `/anagrafica/new` | AnagraficaController | anagrafica/new |
| `app_anagrafica_edit` | `/anagrafica/{id}/edit` | AnagraficaController | anagrafica/edit |
| `app_anagrafica_delete` | `/anagrafica/{id}` POST | AnagraficaController | — |
| `app_vettura_index` | `/vettura` | VetturaController | vettura/index |
| `app_vettura_show` | `/vettura/{id}` | VetturaController | vettura/show |
| `app_assicurazione_index` | `/assicurazione` | AssicurazioneController | assicurazione/index |
| `app_assicurazione_show` | `/assicurazione/{id}` | AssicurazioneController | assicurazione/show |
| `app_bollo_index` | `/bollo` | BolloController | bollo/index |
| `app_bollo_show` | `/bollo/{id}` | BolloController | bollo/show |
| `app_patente_index` | `/patente` | PatenteController | patente/index |
| `app_patente_edit` | `/patente/{id}/edit` | PatenteController | patente/edit |
| `app_notifica_index` | `/notifica/` | NotificaController | notifica/index |
| `app_notifica_new` | `/notifica/new` | NotificaController | notifica/new |
| `app_notifica_new_per_cliente` | `/notifica/new/{id}` | NotificaController | notifica/new |
| `app_notifica_edit` | `/notifica/{id}/edit` | NotificaController | notifica/edit |
| `app_notifica_show` | `/notifica/{id}` | NotificaController | notifica/show |
| `app_notifica_delete` | `/notifica/{id}` POST | NotificaController | — |

---

## 6. Service Layer

### ScadenzaService (`src/Service/ScadenzaService.php`)
Calcola lo stato di una scadenza rispetto a oggi:
```php
$stato = $scadenzaService->calcolaStato($dataScadenza);
// ritorna: 'scaduto' | 'in_scadenza' | 'in_avvicinamento' | 'ok' | 'non_definito'

$badge = $scadenzaService->getBadge($stato);
// ritorna: 'danger' | 'warning' | 'info' | 'success' | 'secondary'

$sommario = $scadenzaService->getSommarioDashboard();
// array con revisioni_scadute, revisioni_30gg, assicurazioni_30gg, patenti_60gg, bolli_mese_corrente
```
Iniettato in: `ScadenzeController`, `DashboardController`
Passato al template come variabile `scadenzaService` per usarlo direttamente in Twig.

### NotificaService (`src/Service/NotificaService.php`)
```php
$notificaService->registra($anagrafica, $tipo, $canale, $esito, $note, $vettura, $utente);
$notificaService->èGiàAvvisato($anagrafica, $tipo, $giorni = 7);  // bool
$notificaService->getStoricoCliente($anagrafica);
$notificaService->getRecenti($giorni = 7);
```

---

## 7. Repository — metodi custom

### AnagraficaRepository
- `search(string $q)` — cerca per cognome/nome (LIKE, max 50 risultati)
- `findByTelefono(string $tel)` — cerca per numero telefono (LIKE)
- `searchGlobale(string $q)` — cerca per cognome/nome **o** targa veicolo

### VetturaRepository
- `findRevisioniInRange(\DateTimeInterface $da, $a)` — veicoli con revisione nel periodo
- `findByTargaLike(string $targa)` — cerca per targa (LIKE, UPPER)

### AssicurazioneRepository
- `findInScadenza(\DateTimeInterface $entro)` — assicurazioni in scadenza da oggi a $entro

### BolloRepository
- `findInRange(\DateTimeInterface $da, $a)` — bolli nel periodo

### PatenteRepository
- `findInScadenza(\DateTimeInterface $entro)` — patenti in scadenza da oggi a $entro

### NotificaRepository
- `findByAnagrafica(Anagrafica $a)` — ordinate per data DESC
- `findByVettura(Vettura $v)` — ordinate per data DESC
- `findRecenti(int $giorni = 7)` — ultimi N giorni

---

## 8. Form Types

| Classe | Entity | Note |
|---|---|---|
| AnagraficaType | Anagrafica | Con validazione CF (16 char) e P.IVA (11 char) |
| VetturaType | Vettura | EntityType per intestatario (choice_label: cognome) |
| AssicurazioneType | Assicurazione | Da verificare campi |
| BolloType | Bollo | Da verificare campi |
| PatenteType | Patente | Da verificare campi |
| NotificaType | Notifica | ChoiceType per tipo/canale/esito, EntityType cliente+veicolo |
| ScadenzaType | — | Solo filtro date Da/A (form senza data_class) |

---

## 9. Migrations pendenti (da eseguire in ordine)

| File | Cosa fa | Priorità |
|---|---|---|
| `Version20260310004_User_Create.php` | Crea tabella `user` | 🔴 Blocca il login |
| `Version20260310003_Notifica_Create.php` | Crea tabella `notifica` | 🔴 Dipende da user |
| `Version20260310005_Vettura_Indici.php` | Indici su date + `esente_revisione` | 🟡 Performance |
| `Version20260310001_Bollo_StoricoPagamento.php` | Rimuove UNIQUE bollo + nuovi campi | 🟡 Feature |
| `Version20260310002_Assicurazione_Storico.php` | Rimuove UNIQUE assicurazione + nuovi campi | 🟡 Feature |

**Comando:** `php bin/console doctrine:migrations:migrate`

---

## 10. Stato attuale dei componenti

| Componente | Stato | Note |
|---|---|---|
| Login / Security | ✅ Configurato | `security.yaml` + `SecurityController` + `User` entity pronti. Manca solo la migration del DB |
| Dashboard | ✅ Strutturata | Template e controller pronti, dipende da migrations |
| Scadenze (home) | ✅ Funzionante | Refactoring fatto, badge semaforo, Bootstrap 5 Tabs |
| Anagrafica CRUD | ✅ Funzionante | Form validato |
| Vettura CRUD | ✅ Funzionante | Include link intestatario |
| Assicurazione CRUD | ⚠️ Funzionante | Entity ancora OneToOne (UNIQUE) — storico non supportato |
| Bollo CRUD | ⚠️ Funzionante | Entity ancora OneToOne (UNIQUE) — storico non supportato |
| Patente CRUD | ✅ Funzionante | categoriaPatente come JSON array |
| Notifiche CRUD | ✅ Strutturato | Controller + Form + Template pronti, dipende da migration |
| Ricerca globale | ⚠️ Parziale | Navbar invia GET a `/anagrafica?q=...` ma AnagraficaController::index non filtra ancora |
| DataFixtures | ⚠️ Presenti | Non aggiornate con User/Notifica |

---

## 11. Issue note e TODO tecnici

### Bug / Inconsistenze
- `Assicurazione` e `Bollo` sono ancora `OneToOne` in PHP ma le migration rimuovono il UNIQUE nel DB. Dopo le migration, convertire in `ManyToOne` nelle Entity.
- `AnagraficaType` usa chiavi snake_case (`luogo_nascita`) ma il campo Symfony è camelCase. Funziona ma è da allineare.
- `VetturaType` mostra solo `cognome` nel choice label intestatario — sarebbe meglio `cognome + nome`.
- `AnagraficaController::index` non usa ancora `AnagraficaRepository::search()` per filtrare con `?q=`.
- `Anagrafica` ha solo `telefono` (singolo) — le business rules prevedono `telefono1` + `telefono2`.

### TODO funzionali (da docs/04_ROADMAP.md)
- [ ] Collegare ricerca navbar → `AnagraficaController::index` con filtro `?q=`
- [ ] Convertire `Assicurazione` e `Bollo` in ManyToOne dopo migrations
- [ ] Aggiungere `telefono2` all'entity Anagrafica
- [ ] Aggiungere `esente_revisione` (boolean) all'entity Vettura
- [ ] DataFixtures aggiornate con `User` e `Notifica`
- [ ] Creare primo utente admin via console command
- [ ] Completare template `anagrafica/show.html.twig` con lista veicoli e storico notifiche
- [ ] Aggiungere paginazione alle liste (KnpPaginatorBundle o manuale)

---

## 12. Convenzioni di codice (da docs/05_CODING_CONVENTIONS.md)

- **Entity**: `PascalCase` PHP → colonne DB `snake_case` (mapping ORM esplicito dove divergono)
- **Route name**: `app_{entità}_{azione}` (es. `app_vettura_index`)
- **Controller**: solo orchestrazione — logica nel Service o Repository
- **Repository**: query DQL/QB — mai SQL raw
- **Service**: logica di business (ScadenzaService, NotificaService)
- **Attributi PHP 8**: `#[ORM\...]` — no annotazioni `@ORM\`
- **Bootstrap**: versione 5.3 da CDN — no jQuery, usare `data-bs-*`
- **Icone**: Bootstrap Icons (classe `bi bi-nome`) — no Open Iconic
- **Flash messages**: `success` → verde, `error`/`danger` → rosso; gestiti in `base.html.twig`
- **CSRF**: abilitato su tutti i form DELETE e nei form login
- **Twig macro**: per logica ripetuta nei template (es. `badge_scadenza` in scadenze/index)

---

## 13. Setup locale da zero

### Con Docker (metodo consigliato)

```bash
# 1. Clone
git clone https://github.com/CarloGagliolo/Gescar2.git && cd Gescar2

# 2. Avvia container Docker (MySQL 8.0 + phpMyAdmin + Mailpit)
make up
# oppure: docker compose up -d

# 3. Dipendenze PHP
make install
# oppure: composer install

# 4. Crea .env.local dal template
make env
# oppure: cp .env.local.dist .env.local
# → Genera APP_SECRET: php -r "echo bin2hex(random_bytes(16));"
# → DATABASE_URL già preconfigurata per Docker in .env.local.dist

# 5. Database (attendi ~10s che MySQL sia pronto)
make db-create
make migrate
# oppure:
# php bin/console doctrine:database:create
# php bin/console doctrine:migrations:migrate

# 6. Primo utente admin
php bin/console security:hash-password
# poi via make db-shell o phpMyAdmin (http://localhost:8080):
# INSERT INTO user (email, roles, password, nome, cognome, is_active)
# VALUES ('admin@gescar.local', '["ROLE_SUPER_ADMIN"]', '<HASH>', 'Carlo', 'Admin', 1);

# 7. (Opzionale) Dati di test
make fixtures

# 8. Avvio server
make server
# oppure: symfony server:start
# oppure: php -S localhost:8000 -t public/
```

### Senza Docker (MySQL locale già installato)

```bash
git clone https://github.com/CarloGagliolo/Gescar2.git && cd Gescar2
composer install
cp .env.local.dist .env.local
# Modifica .env.local con DATABASE_URL corretto per la tua installazione MySQL
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony server:start
```

---

## 14. File chiave da leggere prima di intervenire

| File | Perché |
|---|---|
| `docs/03_BUSINESS_RULES.md` | Regole scadenze, soglie allerta, stati avvisi |
| `docs/02_DATABASE_SCHEMA.md` | Schema DB completo con relazioni |
| `docs/04_ROADMAP.md` | Lista task tecnici prioritizzati |
| `src/Service/ScadenzaService.php` | Costanti stati e badge Bootstrap |
| `src/Entity/Notifica.php` | Costanti tipo/canale/esito |
| `config/packages/security.yaml` | Configurazione autenticazione |
| `migrations/` | Tutte le migration pendenti |

---

## 15. Ambiente Docker (sviluppo locale)

### Infrastruttura

Il file `docker-compose.yml` definisce tre container:

| Container | Image | Porta host | Scopo |
|---|---|---|---|
| `gescar_db` | `mysql:8.0` | `3306` | Database MySQL |
| `gescar_pma` | `phpmyadmin:latest` | `8080` | GUI database |
| `gescar_mail` | `axllent/mailpit:latest` | `8025` (web) / `1025` (smtp) | Catch-all email test |

### Credenziali DB

| Parametro | Valore |
|---|---|
| Root user | `root` |
| Root password | `root` |
| App user | `gescar_user` |
| App password | `gescar_pass` |
| Database name | `gescar_new_local` |
| Porta | `3306` |

### DATABASE_URL per Docker

```env
DATABASE_URL="mysql://gescar_user:gescar_pass@127.0.0.1:3306/gescar_new_local?serverVersion=8.0&charset=utf8mb4"
```

Impostare in `.env.local` (copiare da `.env.local.dist`).

### Quick start con Docker

```bash
# 1. Avvia tutti i container
docker compose up -d

# 2. Attendi ~10 secondi che MySQL sia pronto, poi:
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 3. Carica dati di test (opzionale)
php bin/console doctrine:fixtures:load --no-interaction

# 4. Crea primo utente admin (SQL manuale)
php bin/console security:hash-password
# poi inserisci via phpMyAdmin o db-shell:
# INSERT INTO user (email, roles, password, nome, cognome, is_active)
# VALUES ('admin@gescar.local', '["ROLE_SUPER_ADMIN"]', '<HASH>', 'Carlo', 'Admin', 1);

# 5. Avvia server Symfony
symfony server:start
# oppure: php -S localhost:8000 -t public/
```

### Makefile — comandi rapidi

```bash
make up           # docker compose up -d
make down         # docker compose down
make restart      # down + up
make logs         # docker compose logs -f
make db-shell     # mysql shell come gescar_user
make db-root      # mysql shell come root
make install      # composer install
make env          # crea .env.local dal template (se non esiste)
make db-create    # php bin/console doctrine:database:create
make migrate      # php bin/console doctrine:migrations:migrate
make migrate-diff # genera nuova migration da diff Entity/DB
make fixtures     # carica DataFixtures (⚠️ svuota il DB)
make db-reset     # drop → create → migrate → fixtures
make cache        # php bin/console cache:clear
make routes       # php bin/console debug:router
make entities     # php bin/console doctrine:schema:validate
make server       # php -S localhost:8000 -t public/
make test         # php bin/phpunit
make open-app     # apre http://localhost:8000 (Windows)
make open-pma     # apre http://localhost:8080 (phpMyAdmin)
make open-mail    # apre http://localhost:8025 (Mailpit)
```

### Note importanti

- Il volume `gescar_db_data` è **persistente**: i dati sopravvivono a `docker compose down`. Usare `docker compose down -v` per distruggerlo.
- Il MySQL container usa `--default-authentication-plugin=mysql_native_password` per compatibilità con il driver PDO di PHP.
- Mailpit cattura tutte le email inviate via SMTP su porta 1025. Non recapita email reali.
- `.env.local` non viene mai committato (è in `.gitignore`). Usare `.env.local.dist` come template.
