# Gescar – Gestionale Centro Revisioni

Web application per la gestione di clienti, veicoli e scadenze (revisioni, assicurazioni, bolli, patenti) del Centro Revisioni Charlot.

> [github.com/CarloGagliolo/Gescar2](https://github.com/CarloGagliolo/Gescar2) · Uso interno · Proprietaria

---

## Stack

| Layer | Tecnologia | Versione |
|---|---|---|
| Framework | Symfony | 7.3 |
| Database | MySQL | 8.0 (Docker) / 5.7 (prod) |
| ORM | Doctrine ORM | 3.x |
| Frontend | Bootstrap | 5.3 CDN |
| Auth | Symfony Security | nativo |

---

## Setup rapido

```bash
git clone https://github.com/CarloGagliolo/Gescar2.git && cd Gescar2
docker compose up -d          # MySQL 8.0 · phpMyAdmin :8080 · Mailpit :8025
composer install
cp .env.local.dist .env.local  # inserire APP_SECRET e credenziali DB
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony server:start
```

Inserire il primo utente admin via phpMyAdmin o SQL diretto (vedi [wiki/Setup-e-Installazione](wiki/Setup-e-Installazione.md)).

---

## Funzionalità principali

- Anagrafica clienti con ricerca per nome/cognome e targa
- Gestione veicoli (revisione, assicurazione, bollo, impianto GPL/metano, esente revisione)
- Dashboard scadenze imminenti con badge semaforo
- Registro notifiche (telefono, SMS, email, WhatsApp) con invio automatico
- Autenticazione con ruoli `ROLE_ADMIN` / `ROLE_SUPER_ADMIN`

---

## Documentazione

| File | Contenuto |
|---|---|
| `docs/01_PROJECT_OVERVIEW.md` | Descrizione dominio e obiettivi |
| `docs/02_DATABASE_SCHEMA.md` | Schema DB e relazioni |
| `docs/03_BUSINESS_RULES.md` | Regole di business |
| `docs/04_ROADMAP.md` | Roadmap e changelog |
| `docs/05_CODING_CONVENTIONS.md` | Convenzioni di sviluppo |
| `docs/06_BUNDLE_VERSIONS.md` | Versioni dipendenze installate |
| `docs/07_ARCHITECTURE_DECISIONS.md` | ADR – scelte architetturali |
| `CONTEXT.md` | Contesto rapido per sessioni AI |
| `wiki/` | Wiki navigabile (replica GitHub Wiki) |

---

## Prossimi step

- `telefono2` su entity Anagrafica
- Paginazione liste
- Upgrade MySQL → 8.4 LTS (prod) e Symfony → 7.4 LTS
- DataFixtures con User e Notifica
- Export CSV/Excel scadenze

Vedi `docs/04_ROADMAP.md` per il dettaglio.
