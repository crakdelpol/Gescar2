# Sviluppi Futuri – Gescar2

> Documento di pianificazione tecnica per i prossimi cicli di sviluppo.
> Aggiornato al: **2026-03-14**
>
> ⚠️ **REGOLA DI SVILUPPO:** Ogni punto va implementato un passo alla volta,
> con revisione e approvazione esplicita dell'utente prima di procedere al successivo.
> Nessun commit o push autonomo da parte dell'agente AI (vedi `CONTEXT.md`).

---

## 1. Ricerca e Correzione Malfunzionamenti

### Cosa fare prima di ogni altro sviluppo
Prima di aggiungere nuove funzionalità, è buona pratica fare un giro sistematico di verifica su tutto il codice esistente. I punti più a rischio in Gescar2 sono:

**A — Ricerca globale navbar (verificare)**
La ricerca con `?q=` è collegata ma va testata su casi limite: query vuota, caratteri speciali, nomi con apostrofo (es. "Dell'Orto"), targhe con spazi. La query `searchGlobale()` va letta e verificata che usi `LIKE` con escape corretto e non sia vulnerabile a injection (Doctrine DQL protegge, ma va confermato).

**B — Relazioni OneToOne senza vincolo DB**
`Assicurazione` e `Bollo` hanno la relazione `OneToOne` solo a livello applicativo (il vincolo UNIQUE è stato rimosso per supportare lo storico). Rischio: se il codice non gestisce correttamente il campo `attiva`, è possibile avere più record attivi per lo stesso veicolo senza che il DB lo segnali. Da verificare nei controller di creazione e modifica.

**C — DataFixtures non aggiornate**
Le fixtures attuali non creano `User` né `Notifica`. Se si eseguono in ambiente di test o staging, l'app parte senza utenti → errore di login. Da correggere prima di fare test automatizzati.

**D — Template con dati null non gestiti**
Nelle pagine `show` di vettura/anagrafica, campi nullable come `dataScadenzaImpianto`, `email`, `codiceFiscale` potrebbero generare errori Twig se non gestiti con `is not null` o filtro `default`. Da verificare con un giro manuale su record reali incompleti.

**Come procedere (passo per passo):**
1. Leggere `AnagraficaRepository::searchGlobale()` e verificare la query DQL
2. Leggere `BolloController` e `AssicurazioneController` per la logica del flag `attiva`
3. Aggiornare le DataFixtures con User e Notifica
4. Fare un giro manuale su template con record reali incompleti

---

## 2. Login più Robusta e Sicura

### Stato attuale
Il login è funzionante: CSRF token attivo (`enable_csrf: true`), bcrypt cost 13, provider Doctrine. Mancano però protezioni contro attacchi brute-force e una gestione più robusta della sessione.

### Miglioramenti da implementare

**A — Rate limiting sul login** *(priorità alta)*
Symfony 6.2+ include il componente `RateLimiter` nativo. Si può limitare i tentativi di login per IP e/o per email senza dipendenze esterne.

```yaml
# config/packages/security.yaml — da aggiungere al firewall main
login_throttling:
    max_attempts: 5       # tentativi massimi
    interval: '15 minutes'
```
Un singolo blocco YAML, nessun codice PHP aggiuntivo.

**B — Upgrade algoritmo password da bcrypt a sodium** *(priorità media)*
bcrypt (cost 13) è solido ma `sodium` (libsodium, disponibile in PHP 8.2) è più moderno e resistente ad attacchi GPU. Symfony aggiorna gli hash automaticamente al prossimo login senza toccare il DB.

```yaml
# security.yaml
password_hashers:
    App\Entity\User:
        algorithm: sodium
        migrate_from:
            - bcrypt
```

**C — Sessione con cookie HttpOnly e Secure** *(priorità alta in produzione)*
Verificare che `session.cookie_secure` sia `true` in produzione e `session.cookie_httponly` sia attivo (default in PHP, ma va confermato nel config Symfony).

```yaml
# config/packages/framework.yaml
framework:
    session:
        cookie_secure: auto   # true in HTTPS, false in HTTP (dev)
        cookie_httponly: true
        cookie_samesite: lax
```

**D — Comando console per creare utenti admin** *(priorità media)*
Attualmente il primo utente va inserito con SQL manuale. Creare `src/Command/CreateAdminCommand.php` che fa: hash password → INSERT user → output conferma.

```bash
php bin/console app:create-admin admin@gescar.local password --super
```

**E — Log dei tentativi di login falliti** *(priorità bassa)*
MonologBundle è già configurato. Aggiungere un `EventSubscriber` sull'evento `LoginFailureEvent` di Symfony Security per loggare IP, email tentata e timestamp.

**Come procedere (passo per passo):**
1. Aggiungere `login_throttling` in `security.yaml` (5 minuti di lavoro, zero codice PHP)
2. Aggiungere cookie settings in `framework.yaml`
3. Creare `CreateAdminCommand`
4. Valutare migrazione a sodium (dopo avere i test, per sicurezza)

---

## 3. Test Automatizzati per Evitare Regressioni

### Stato attuale
La cartella `tests/` non esiste. `symfony/phpunit-bridge` è installato ma non usato.

### Strategia consigliata: partire dai test funzionali

Per un'app Symfony CRUD, i test più utili non sono i test unitari sulle singole classi, ma i **test funzionali** che simulano richieste HTTP reali e verificano che le pagine rispondano correttamente. Sono veloci da scrivere e coprono molto codice in una volta.

**Fase 1 — Setup base** *(1 sessione di lavoro)*
```bash
composer require --dev symfony/test-pack
# installa: phpunit/phpunit, symfony/browser-kit, symfony/css-selector
```
Creare `tests/Controller/` e un primo test smoke su ogni controller (risponde 200 o redirect?).

**Fase 2 — Test funzionali prioritari** *(in ordine di importanza)*

| Test | Cosa verifica | Priorità |
|---|---|---|
| `SecurityControllerTest` | Login con credenziali corrette → redirect; login errato → errore | 🔴 |
| `AnagraficaControllerTest` | index, show, new, edit rispondono 200; ricerca `?q=` restituisce risultati | 🔴 |
| `VetturaControllerTest` | CRUD base funziona; badge semaforo presente in show | 🟡 |
| `NotificaControllerTest` | Creazione notifica registra record in DB | 🟡 |
| `ScadenzaServiceTest` | `getStato()` restituisce `scaduto` / `in_scadenza` / `ok` correttamente | 🟡 |
| `NotificaInvioServiceTest` | `normalizzaTelefono()` gestisce formati italiani | 🟢 |

**Fase 3 — Database in-memory per i test**
Configurare un secondo database SQLite in-memory per i test, così non toccano il DB di sviluppo.

```yaml
# config/packages/test/doctrine.yaml
doctrine:
    dbal:
        url: 'sqlite:///:memory:'
    orm:
        auto_schema_tool_create: true
```

**Come procedere (passo per passo):**
1. Installare `symfony/test-pack`
2. Scrivere `SecurityControllerTest` (login ok / login errato)
3. Scrivere smoke test su tutti i controller (solo status code 200/302)
4. Aggiungere `ScadenzaServiceTest` per la logica semaforo
5. Configurare GitHub Actions (o CI locale) per eseguire i test ad ogni commit

---

## 4. Sicurezza Generale e Riduzione Visibilità su Internet

### A — Headers HTTP di sicurezza *(priorità alta, 30 minuti di lavoro)*
Aggiungere headers di sicurezza standard tramite un `EventSubscriber` o direttamente nella configurazione del web server (nginx/Apache). Questi header proteggono contro XSS, clickjacking e injection di contenuti.

```
Content-Security-Policy: default-src 'self'; script-src 'self' cdn.jsdelivr.net; style-src 'self' cdn.jsdelivr.net
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), camera=(), microphone=()
```

**B — Nascondere informazioni sul server** *(priorità alta)*
- Disabilitare l'header `X-Powered-By: PHP/8.x` in `php.ini` (`expose_php = Off`)
- Disabilitare `Server: nginx/x.x` nell'header nginx
- In produzione: assicurarsi che `APP_ENV=prod` e `APP_DEBUG=false`

**C — Limitare l'accesso per IP** *(se l'app è solo per uso interno)*
Se l'app è usata solo in ufficio o via VPN, si può restringere l'accesso a IP specifici direttamente su nginx, senza toccare il codice Symfony. Soluzione più semplice ed efficace contro attacchi esterni.

```nginx
location / {
    allow 1.2.3.4;  # IP ufficio
    deny all;
}
```

**D — HTTPS obbligatorio** *(priorità alta in produzione)*
Forzare redirect HTTP → HTTPS. In Symfony:
```yaml
# config/packages/prod/security.yaml
security:
    require_https: true  # oppure gestito da nginx
```

**E — Disabilitare il Web Profiler in produzione**
Il profiler Symfony (barra di debug) è già disabilitato in `prod` per design, ma verificare che `APP_ENV` sia effettivamente `prod` sul server.

**F — Protezione CSRF già attiva**
Il token CSRF è già attivo sul form di login (`enable_csrf: true`). Verificare che tutti i form di modifica/cancellazione abbiano il token CSRF (i form Symfony lo includono automaticamente con `{{ form_start(form) }}`).

---

## 5. Report per gli Utenti

### Cosa si intende per "report"
Report scaricabili dagli operatori: lista scadenze del mese, storico notifiche per cliente, riepilogo veicoli per tipo di scadenza.

### Opzioni tecniche

**Opzione A — Export CSV** *(semplicissimo, nessuna dipendenza)*
Symfony `Response` con `Content-Type: text/csv`. Zero librerie esterne. Ideale per report da aprire in Excel.

```php
// ScadenzeController::exportCsv()
$response = new StreamedResponse(function() use ($data) {
    $handle = fopen('php://output', 'w');
    fputcsv($handle, ['Cliente', 'Targa', 'Tipo', 'Scadenza', 'Stato']);
    foreach ($data as $row) { fputcsv($handle, $row); }
    fclose($handle);
});
$response->headers->set('Content-Type', 'text/csv; charset=utf-8');
$response->headers->set('Content-Disposition', 'attachment; filename="scadenze.csv"');
```

**Opzione B — PDF con DomPDF** *(dipendenza leggera)*
```bash
composer require dompdf/dompdf
```
Genera PDF direttamente da template Twig. Ideale per report formattati da stampare o archiviare.

**Opzione C — Excel con PhpSpreadsheet** *(dipendenza più pesante)*
```bash
composer require phpoffice/phpspreadsheet
```
Genera file `.xlsx` nativi con formattazione, colori e formule.

### Report prioritari da implementare

| Report | Formato consigliato | Note |
|---|---|---|
| Scadenze del mese corrente | CSV o PDF | Filtro per tipo (revisioni / assicurazioni / ecc.) |
| Storico notifiche per cliente | PDF | Da `anagrafica/show` con bottone "Scarica PDF" |
| Veicoli senza assicurazione | CSV | Join vettura LEFT JOIN assicurazione WHERE attiva IS NULL |
| Riepilogo mensile avvisi inviati | CSV | Raggruppato per canale (telefono/email/WhatsApp) |

**Come procedere (passo per passo):**
1. Implementare export CSV scadenze del mese (nessuna dipendenza, 1-2 ore)
2. Aggiungere bottone "Esporta CSV" nella pagina scadenze
3. Valutare PDF solo se richiesto esplicitamente dall'utente finale

---

## 6. Test WhatsApp in Contesto Reale

### Stato attuale
`NotificaInvioService` normalizza i numeri di telefono e prepara i messaggi WhatsApp, ma non è chiaro quale API/gateway viene usato per l'invio effettivo.

### Opzioni per l'invio reale

**Opzione A — WhatsApp Business API (Meta)** *(soluzione ufficiale)*
Richiede un account Meta Business verificato e un numero di telefono dedicato. API REST con autenticazione Bearer token. Costo: gratuito fino a 1.000 conversazioni/mese avviate dall'utente, poi a pagamento.

**Opzione B — Twilio WhatsApp** *(più semplice da configurare)*
```bash
composer require twilio/sdk
```
Sandbox gratuita per test, poi account a pagamento (~0.005€/messaggio). Ideale per piccoli volumi come un centro revisioni.

**Opzione C — CallMeBot** *(soluzione non ufficiale, solo per test)*
API gratuita non ufficiale che usa WhatsApp Web. Non adatta alla produzione (può essere bloccata da Meta), ma utile per verificare il flusso in modo rapido.

### Passi per il test reale
1. Verificare quale provider è già configurato in `NotificaInvioService` (leggere il codice)
2. Se non c'è provider: partire con Twilio Sandbox (gratuito, configurazione 30 minuti)
3. Testare con un numero reale su dati di test (mai dati di produzione)
4. Documentare le credenziali in `.env.local` (mai in `.env` committato)

---

## 7. Deploy su Sistema Sicuro e Manutenibile

### Requisiti minimi per il deploy di produzione

| Requisito | Soluzione consigliata |
|---|---|
| Server Linux | VPS Ubuntu 22.04 LTS (es. Hetzner CX22, ~4€/mese) |
| Web server | Nginx + PHP-FPM 8.2 |
| Database | MySQL 8.4 LTS (upgrade da 5.7) |
| HTTPS | Certbot + Let's Encrypt (gratuito, rinnovo automatico) |
| Backup DB | Script cron giornaliero + upload su storage esterno |
| Deploy | Script bash o GitHub Actions (nessun deploy autonomo dall'agente AI) |

### Configurazione Symfony per la produzione

```bash
# Sul server, dopo ogni deploy:
APP_ENV=prod APP_DEBUG=0 composer install --no-dev --optimize-autoloader
php bin/console cache:clear --env=prod
php bin/console doctrine:migrations:migrate --no-interaction
```

### Struttura di deploy consigliata (senza zero-downtime per ora)
```
/var/www/gescar/
├── current/    ← symlink alla release attiva
├── releases/   ← ultime N release (rollback rapido)
└── shared/     ← .env.local, var/log, var/sessions (persistenti tra release)
```

In futuro si può automatizzare con **Deployer** (`deployer/deployer`) — tool PHP nativo, zero dipendenze Node.

### Cosa NON fare
- Non usare `APP_ENV=dev` in produzione (espone il profiler e i log dettagliati)
- Non usare `php bin/console server:start` in produzione (solo per sviluppo locale)
- Non mettere credenziali reali in `.env` (solo in `.env.local`, escluso da git)

---

## 8. Calendario di Manutenzione

### Manutenzione ricorrente

| Frequenza | Attività |
|---|---|
| **Settimanale** | Verificare log applicativo (`var/log/prod.log`) per errori o warning |
| **Mensile** | Eseguire `composer outdated` e valutare aggiornamenti patch (es. `7.3.11` → `7.3.12`) |
| **Mensile** | Verificare scadenza certificato SSL (Let's Encrypt si rinnova in automatico, ma va monitorato) |
| **Trimestrale** | Aggiornamento dipendenze minor (es. `doctrine/orm 3.6.2` → `3.7.x`) — con test prima |
| **Semestrale** | Revisione `docs/06_BUNDLE_VERSIONS.md` e verifica EOL delle dipendenze |
| **Annuale** | Upgrade PHP (es. 8.2 → 8.4), upgrade Symfony (es. 7.3 → 7.4 LTS), upgrade MySQL |
| **Al bisogno** | Security advisories: monitorare `symfony/security-advisories` o usare `composer audit` |

### Comandi utili per la manutenzione

```bash
# Verifica vulnerabilità note nelle dipendenze installate
composer audit

# Lista dipendenze con aggiornamenti disponibili
composer outdated

# Aggiornamento patch sicuro (non cambia versioni major/minor)
composer update --with-all-dependencies

# Verifica deprecazioni Symfony nel log
grep -i "deprecat" var/log/dev.log | tail -20
```

### Upgrade Symfony — procedura sicura
1. Leggere il CHANGELOG di Symfony per la versione target
2. Aggiornare il constraint in `composer.json` (es. `7.3.*` → `7.4.*`)
3. Eseguire `composer update symfony/*`
4. Eseguire tutti i test automatizzati
5. Fare deploy su staging, verificare manualmente
6. Deploy in produzione solo dopo approvazione

---

## Priorità Suggerita

| # | Sviluppo | Impegno stimato | Priorità |
|---|---|---|---|
| 1 | Fix malfunzionamenti esistenti (relazione attiva, template null) | 2-3 sessioni | 🔴 |
| 2 | Test automatizzati base (setup + smoke test) | 2-3 sessioni | 🔴 |
| 3 | Login throttling + cookie sicuri | 1 sessione | 🔴 |
| 4 | Security headers HTTP | 1 sessione | 🟡 |
| 5 | Export CSV scadenze | 1 sessione | 🟡 |
| 6 | Comando `app:create-admin` | 1 sessione | 🟡 |
| 7 | Deploy VPS con HTTPS | 2-3 sessioni | 🟡 |
| 8 | Test WhatsApp reale (Twilio) | 1-2 sessioni | 🟢 |
| 9 | Report PDF | 2 sessioni | 🟢 |
| 10 | Calendario manutenzione attivo | ongoing | 🟢 |
