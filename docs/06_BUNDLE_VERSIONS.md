# Bundle Versions – Gescar2

> Generato automaticamente dall'agente gescar-docs-agent
> Aggiornato al: **2026-03-14**
> Sorgente dati: `composer.json` + `composer.lock`

---

## Dipendenze di Produzione (`require`)

| Pacchetto | Versione in `composer.json` | Versione installata | Note |
|---|---|---|---|
| **PHP** | `>=8.2` | 8.2+ (target 8.4) | Attributi PHP 8 obbligatori |
| `doctrine/dbal` | `^4.4.2` | **4.4.2** | DBAL 4.x — breaking change da 3.x |
| `doctrine/doctrine-bundle` | `^2.18.2` | **2.18.2** | Integrazione Symfony↔Doctrine |
| `doctrine/doctrine-migrations-bundle` | `^3.7.0` | **3.7.0** | |
| `doctrine/orm` | `^3.6.2` | **3.6.2** | ORM 3.x — breaking change da 2.x |
| `symfony/asset` | `7.3.*` | **v7.3.10** | |
| `symfony/console` | `7.3.*` | **v7.3.10** | |
| `symfony/dotenv` | `7.3.*` | **v7.3.8** | |
| `symfony/flex` | `^2.10.0` | **v2.10.0** | Plugin Composer |
| `symfony/form` | `7.3.*` | **v7.3.10** | |
| `symfony/framework-bundle` | `7.3.*` | **v7.3.11** | Kernel, DI, routing |
| `symfony/mailer` | `7.3.*` | **v7.3.10** | Usato da `NotificaInvioService` |
| `symfony/monolog-bundle` | `^4.0` | **v4.0.1** | Logging su file |
| `symfony/runtime` | `7.3.*` | **v7.3.8** | |
| `symfony/security-bundle` | `7.3.*` | **v7.3.10** | Autenticazione nativa |
| `symfony/security-csrf` | `7.3.*` | **v7.3.10** | Token CSRF su form |
| `symfony/twig-bundle` | `7.3.*` | **v7.3.10** | Template engine |
| `symfony/validator` | `7.3.*` | **v7.3.11** | Validazione entity e form |
| `symfony/yaml` | `7.3.*` | **v7.3.8** | |
| `twig/extra-bundle` | `^2.12\|^3.23` | **v3.23.0** | |
| `twig/twig` | `^2.12\|^3.23.0` | **v3.23.0** | |

---

## Dipendenze Transitive Notevoli (installate automaticamente)

| Pacchetto | Versione installata | Note |
|---|---|---|
| `doctrine/collections` | 2.6.0 | |
| `doctrine/deprecations` | 1.1.6 | |
| `doctrine/event-manager` | 2.1.1 | |
| `doctrine/inflector` | 2.1.0 | |
| `doctrine/instantiator` | 2.1.0 | |
| `doctrine/lexer` | 3.0.1 | |
| `doctrine/migrations` | 3.9.6 | Motore migrazioni |
| `doctrine/persistence` | 4.1.1 | |
| `egulias/email-validator` | 4.0.4 | Usato da Symfony Mailer |
| `monolog/monolog` | 3.10.0 | |
| `nikic/php-parser` | v5.7.0 | Dev utility |
| `psr/cache` | 3.0.0 | |
| `psr/log` | 3.0.2 | |
| `symfony/cache` | v7.3.11 | |
| `symfony/clock` | v7.3.8 | |
| `symfony/http-foundation` | v7.3.11 | |
| `symfony/http-kernel` | v7.3.10 | |
| `symfony/password-hasher` | v7.3.10 | bcrypt cost 13 |
| `symfony/routing` | v7.3.10 | |
| `symfony/security-core` | v7.3.10 | |
| `symfony/security-http` | v7.3.10 | |
| `symfony/string` | v7.3.8 | |
| `symfony/translation-contracts` | v3.6.1 | |

---

## Dipendenze di Sviluppo (`require-dev`)

| Pacchetto | Versione in `composer.json` | Versione installata | Note |
|---|---|---|---|
| `doctrine/doctrine-fixtures-bundle` | `^4.3.1` | **4.3.1** | DataFixtures per test |
| `symfony/maker-bundle` | `^1.66` | **v1.66.0** | Generatore codice (`make:entity`, ecc.) |
| `symfony/phpunit-bridge` | `7.3.*` | **v7.3.4** | PHPUnit integration |
| `symfony/stopwatch` | `7.3.*` | **v7.3.0** | Profiler |
| `symfony/web-profiler-bundle` | `7.3.*` | **v7.3.10** | Barra debug (dev only) |

---

## Frontend (CDN — non gestito da Composer)

| Libreria | Versione usata | Metodo | Note |
|---|---|---|---|
| Bootstrap CSS | **5.3.x** | CDN jsDelivr | Nessun `package.json`, zero build step |
| Bootstrap Icons | **1.11.x** | CDN jsDelivr | Classi `bi bi-*` |
| Bootstrap JS Bundle | **5.3.x** | CDN jsDelivr | Include Popper.js integrato |

> ⚠️ Le versioni CDN non sono bloccate in `composer.lock`. Verificare periodicamente che i link CDN nei template puntino a versioni stabili e non deprecate.

---

## Infrastruttura Docker

| Servizio | Image Docker | Versione | Porta host | Note |
|---|---|---|---|---|
| Database | `mysql` | **8.0** | 3306 | Sviluppo locale |
| GUI DB | `phpmyadmin` | latest | 8080 | |
| Mail catch-all | `axllent/mailpit` | latest | 8025 / 1025 | SMTP test |

> ⚠️ MySQL 5.7 (produzione) è **EOL da ottobre 2023**. Il target è MySQL 8.4 LTS. Docker locale usa già 8.0. Pianificare upgrade produzione.

---

## Note su Versioni e Aggiornamenti

### Symfony 7.3 → 7.4 LTS
Tutti i pacchetti `symfony/*` sono allineati sulla **7.3** (`v7.3.8`–`v7.3.11`). Symfony 7.4 è la versione LTS: aggiornamento consigliato quando disponibile per estendere il supporto security fino a novembre 2027.

### Doctrine ORM 3.x
Versione installata **3.6.2**. Doctrine ORM 3.x ha breaking change rispetto a 2.x (lazy loading, tipi PHP nativi, namespace `UnitOfWork`). L'attuale codebase è già scritta per ORM 3.x.

### Doctrine DBAL 4.x
Versione installata **4.4.2**. DBAL 4.x ha rimosso diversi metodi legacy (`fetchColumn`, `executeUpdate`, ecc.). La codebase usa DBAL 4 correttamente attraverso le API Doctrine ORM.

### Polyfill rimossi
`composer.json` dichiara `replace` per tutti i polyfill `symfony/polyfill-php72` → `php82`, riducendo le dipendenze installate su PHP 8.2.
