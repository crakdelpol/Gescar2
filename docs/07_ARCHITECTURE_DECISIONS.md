# Architecture Decision Records – Gescar2

> Registro delle principali scelte architetturali del progetto.
> Aggiornato al: **2026-03-14**

---

## ADR-001 — Symfony Security nativo invece di FOSUserBundle

**Stato:** ✅ Implementato
**Data:** 2026-03

**Contesto:**
Il sistema legacy usava FOSUserBundle per la gestione degli utenti. FOSUserBundle è stato abbandonato ufficialmente e risulta incompatibile con Symfony 6+.

**Decisione:**
Adottare il sistema di autenticazione nativo di Symfony Security, creando una Entity `User` che implementa `UserInterface` e `PasswordAuthenticatedUserInterface`.

**Conseguenze:**
- Zero dipendenze esterne per la gestione auth
- Hash password con `symfony/password-hasher` (bcrypt, cost 13)
- Ruoli gerarchici: `ROLE_SUPER_ADMIN` → `ROLE_ADMIN` → `ROLE_USER`
- Gli hash bcrypt esistenti (da FOSUserBundle) sono riusabili
- Configurazione in `config/packages/security.yaml`

**File chiave:** `src/Entity/User.php`, `config/packages/security.yaml`, `src/Controller/SecurityController.php`

---

## ADR-002 — Bootstrap 5.3 via CDN (zero build step)

**Stato:** ✅ Implementato
**Data:** 2026-03

**Contesto:**
Il sistema legacy usava Bootstrap 3/4 con jQuery. Bootstrap 5 rimuove la dipendenza da jQuery e introduce utilità CSS moderne. Tuttavia, aggiungere un build step (Webpack Encore, Vite) aumenta la complessità di sviluppo e manutenzione per un'applicazione gestionale a uso interno.

**Decisione:**
Usare Bootstrap 5.3 e Bootstrap Icons 1.11 direttamente da CDN (jsDelivr) senza nessun tool di build (`npm`, Webpack Encore, ecc.).

**Conseguenze:**
- Setup frontend immediato, nessun `package.json`
- Zero jQuery: interazioni UI con `data-bs-*` attributi e JS vanilla inline
- Versioni CDN non bloccate in lock file — verificare periodicamente
- Custom CSS limitato a `/public/css/custom.css`
- Non adatto se si aggiungessero molte dipendenze JS custom

---

## ADR-003 — Doctrine ORM 3.x con PHP Attributes

**Stato:** ✅ Implementato
**Data:** 2026-03

**Contesto:**
Doctrine ORM 2.x usava annotazioni PHP (`@ORM\Entity`). PHP 8 introduce gli attributi nativi (`#[ORM\Entity]`), più performanti e tipizzati. Doctrine ORM 3.x richiede gli attributi e rimuove il supporto alle annotazioni legacy.

**Decisione:**
Usare Doctrine ORM 3.x con attributi PHP 8 per tutte le Entity. Nessuna annotazione `@ORM\`.

**Conseguenze:**
- Entity più leggibili e type-safe
- Breaking change da ORM 2.x: lazy loading, `UnitOfWork` API, `Proxy` namespace
- DBAL 4.x (installato): rimossi `fetchColumn`, `executeUpdate` — usare API DBAL 4
- Naming strategy `underscore_number` per le colonne DB (camelCase PHP → snake_case SQL)

---

## ADR-004 — Relazione OneToOne per Assicurazione e Bollo (senza UNIQUE DB)

**Stato:** ✅ Implementato
**Data:** 2026-03

**Contesto:**
La relazione tra `Vettura` e `Assicurazione`/`Bollo` è semanticamente "una sola assicurazione/bollo attiva per veicolo". Tuttavia, il vincolo `UNIQUE` in DB impediva di mantenere uno storico dei rinnovi (record precedenti).

**Decisione:**
Rimuovere il vincolo `UNIQUE` dal database (migration 001 e 002), mantenendo la relazione `OneToOne` a livello applicativo. Il campo `attiva` (boolean) identifica il record corrente.

**Conseguenze:**
- Storico rinnovi possibile senza cambio di relazione ORM
- La logica applicativa (non il DB) garantisce che un solo record sia `attiva = 1` per veicolo
- ⚠️ Non convertire la relazione in `ManyToOne` — rimane `OneToOne` nel mapping Doctrine

---

## ADR-005 — Service Layer per logica di business

**Stato:** ✅ Implementato
**Data:** 2026-03

**Contesto:**
I Controller Symfony tendono ad accumolare logica di business, rendendo il codice difficile da testare e mantenere.

**Decisione:**
Separare rigorosamente le responsabilità:
- **Controller**: solo orchestrazione (riceve richiesta, chiama service/repository, restituisce response)
- **Service**: logica di business (`ScadenzaService`, `NotificaService`, `NotificaInvioService`)
- **Repository**: query DQL/QueryBuilder — mai SQL raw, mai nei Controller

**Servizi implementati:**
- `ScadenzaService`: calcola stati semaforo (`scaduto | in_scadenza | in_avvicinamento | ok`), badge Bootstrap, sommario dashboard
- `NotificaService`: registrazione avvisi, controllo duplicati (`èGiàAvvisato`), storico cliente
- `NotificaInvioService`: invio effettivo email (Symfony Mailer) e WhatsApp, normalizzazione numero telefono, registrazione automatica nel log notifiche

---

## ADR-006 — Docker per sviluppo locale (MySQL 8.0, phpMyAdmin, Mailpit)

**Stato:** ✅ Implementato
**Data:** 2026-03

**Contesto:**
Il DB di produzione è MySQL 5.7 (EOL). Lo sviluppo locale necessita di un ambiente riproducibile senza installazioni globali.

**Decisione:**
Usare Docker Compose con tre container: MySQL 8.0, phpMyAdmin, Mailpit.

**Conseguenze:**
- Ambiente di sviluppo riproducibile in un comando (`make up`)
- MySQL 8.0 in sviluppo vs 5.7 in produzione: minimo disallineamento, entrambi compatibili con le query attuali
- Mailpit cattura tutte le email SMTP senza inviarle realmente (test safe)
- Il volume `gescar_db_data` è persistente (`docker compose down` non cancella i dati)
- Per reset completo: `docker compose down -v`

**Prossimo step:** upgrade MySQL 5.7 → 8.4 LTS in produzione (vedi `[STACK-01]` in roadmap).

---

## ADR-007 — Migrazioni Doctrine con file nominati per descrizione

**Stato:** ✅ Implementato
**Data:** 2026-03-10

**Contesto:**
Le migrazioni Doctrine generate automaticamente hanno nomi come `Version20260310123456.php`, difficili da leggere e ordinare.

**Decisione:**
Nominare i file di migration con un prefisso numerico descrittivo: `Version20260310NNN_NomeDescritivo.php`.

**Conseguenze:**
- Ordine di esecuzione controllato dal numero (000, 001, ..., 005)
- Nome file leggibile senza aprire il file
- Il commento `getDescription()` in ogni migration documenta lo scopo
- ⚠️ L'ordine di esecuzione è determinato dal nome classe/file — mantenere la coerenza numerica

**Migration presenti:**

| File | Scopo |
|---|---|
| `Version20260310000_InitialSchema.php` | Schema iniziale (anagrafica, vettura, patente, assicurazione, bollo) |
| `Version20260310001_Bollo_StoricoPagamento.php` | Storico bollo: rimuove UNIQUE, aggiunge campi (attiva, importo, ecc.) |
| `Version20260310002_Assicurazione_Storico.php` | Storico assicurazione: rimuove UNIQUE, aggiunge campi |
| `Version20260310003_User_Create.php` | Crea tabella `user` per Symfony Security nativo |
| `Version20260310004_Notifica_Create.php` | Crea tabella `notifica` con FK su user, anagrafica, vettura |
| `Version20260310005_Vettura_Indici.php` | Indici su date scadenza + campo `esente_revisione` su vettura |

---

## ADR-008 — Gestione notifiche multi-canale con log centralizzato

**Stato:** ✅ Implementato
**Data:** 2026-03-14

**Contesto:**
Gli operatori del centro revisioni contattano i clienti via telefono, SMS, email e WhatsApp. Tracciare questi contatti è fondamentale per evitare duplicati e verificare l'efficacia degli avvisi.

**Decisione:**
Implementare un registro `notifica` con: anagrafica_id, vettura_id, tipo_scadenza, canale, data_invio, esito, note, utente_id. Due servizi separati: `NotificaService` (log manuale da form operatore) e `NotificaInvioService` (invio automatico con log integrato).

**Conseguenze:**
- Storico completo di tutti i contatti con i clienti
- `NotificaInvioService` usa `symfony/mailer` per email e normalizza numeri per WhatsApp
- Ogni invio automatico genera automaticamente un record in `notifica`
- L'operatore può anche registrare manualmente chiamate telefoniche tramite form

---

## ADR-009 — Nessuna paginazione (decisione temporanea)

**Stato:** ⚠️ Pendente
**Data:** 2026-03

**Contesto:**
Il DB di produzione ha ~7.000 anagrafiche e ~4.500 veicoli. Le liste senza paginazione caricano tutti i record in memoria.

**Decisione temporanea:**
Non implementare paginazione nella fase iniziale di migrazione. Le query con filtro `?q=` limitano i risultati a 50 record.

**Conseguenze:**
- Le liste complete (senza filtro) sono lente su DB di produzione
- Necessario implementare paginazione prima del go-live in produzione

**Opzioni valutate:**
- `KnpPaginatorBundle` — soluzione matura, integrazione Twig semplice
- Paginazione manuale con `LIMIT/OFFSET` nei Repository — più controllo, più codice
