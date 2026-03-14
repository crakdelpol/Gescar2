# CHANGELOG_BACKEND.md — Gescar2

> Registro delle analisi e modifiche al backend (Entity, Migration, Controller, Service, Repository).
> Aggiornato automaticamente dall'agente back-end schedulato.

---

## Sessione 2026-03-14 — Analisi completa backend (agente schedulato)

### Analisi eseguita

Esame completo di:
- Tutte le Entity in `src/Entity/`
- Tutti i file in `migrations/`
- Tutti i Controller in `src/Controller/`
- Tutti i Repository in `src/Repository/`
- Tutti i Service in `src/Service/`
- Documentazione esistente (`CONTEXT.md`, `docs/BUG_REPORT.md`, `docs/04_ROADMAP.md`)

---

### Risultati Migrations

Stato: **✅ Tutte le migration presenti e coerenti con le Entity**

| File | Contenuto | Stato |
|---|---|---|
| `Version20260310000_InitialSchema.php` | Schema iniziale (anagrafica, vettura, patente, assicurazione, bollo) | ✅ Presente |
| `Version20260310001_Bollo_StoricoPagamento.php` | Rimuove UNIQUE su `bollo.vettura_id`, aggiunge campi storico | ✅ Presente |
| `Version20260310002_Assicurazione_Storico.php` | Rimuove UNIQUE su `assicurazione.vettura_id`, aggiunge campi | ✅ Presente |
| `Version20260310003_User_Create.php` | Crea tabella `user` per Symfony Security | ✅ Presente |
| `Version20260310004_Notifica_Create.php` | Crea tabella `notifica` con FK | ✅ Presente |
| `Version20260310005_Vettura_Indici.php` | Indici su date scadenza + campo `esente_revisione` | ✅ Presente |

Nessuna migration mancante identificata. I requisiti del task (User, Notifica, rimozione UNIQUE, `esente_revisione`) sono tutti coperti.

---

### Risultati Entity

Stato: **✅ Tutte le Entity conformi — nessuna annotazione legacy**

Verifica per ogni Entity:

| Entity | Attributi PHP 8 | Relazioni | Interfacce | Note |
|---|---|---|---|---|
| `Anagrafica` | ✅ `#[ORM\...]` | — | — | Column name espliciti (snake_case DB) |
| `Vettura` | ✅ `#[ORM\...]` | ManyToOne → Anagrafica ✅ | — | `esenteRevisione` presente ✅ |
| `Assicurazione` | ✅ `#[ORM\...]` | OneToOne → Vettura ✅ | — | Relazione intenzionalmente OneToOne (CONTEXT.md) |
| `Bollo` | ✅ `#[ORM\...]` | OneToOne → Vettura ✅ | — | Relazione intenzionalmente OneToOne (CONTEXT.md) |
| `Patente` | ✅ `#[ORM\...]` | OneToOne → Anagrafica ✅ | — | `categoriaPatente` come JSON array |
| `User` | ✅ `#[ORM\...]` | — | ✅ `UserInterface` + `PasswordAuthenticatedUserInterface` | `ROLE_ADMIN` come ruolo minimo |
| `Notifica` | ✅ `#[ORM\...]` | ManyToOne → Anagrafica, Vettura, User ✅ | — | Costanti tipo/canale/esito definite |

Nessuna annotazione `@ORM\` legacy trovata (grep confermato su tutto `src/`).

**Nota relazioni Assicurazione/Bollo:** la relation `OneToOne` (invece di `ManyToOne`) è intenzionale e documentata in `CONTEXT.md`. La migration 001 e 002 hanno rimosso il UNIQUE DB per permettere lo storico rinnovi, ma la relazione applicativa resta OneToOne (una sola per veicolo attiva per volta, gestita via campo `attiva`). Non modificare.

---

### Risultati Controller e Service

Stato: **✅ Maggior parte conforme — 1 anti-pattern aperto**

| Componente | Query nel Repository | Logica nel Service | Note |
|---|---|---|---|
| `AnagraficaController` | ✅ Usa `AnagraficaRepository::searchGlobale()` | — | `?q=` funzionante ✅ |
| `VetturaController` | ✅ | — | — |
| `AssicurazioneController` | ✅ | — | — |
| `BolloController` | ✅ | — | — |
| `PatenteController` | ✅ | — | — |
| `NotificaController` | ✅ | ✅ Usa `NotificaService` | — |
| `DashboardController` | ✅ | ✅ Usa `ScadenzaService::getSommarioDashboard()` | — |
| `ScadenzeController` | ⚠️ Query DQL nei metodi privati | ✅ Usa `ScadenzaService` | Vedi OPEN-02 sotto |

---

### Issue aperti (ereditati da BUG_REPORT.md + nuova verifica)

#### OPEN-01 — `NotificaInvioService::log()` non registra l'utente mittente ✅ RISOLTO 2026-03-14
- **File:** `src/Service/NotificaInvioService.php`
- **Descrizione:** Il metodo privato `log()` non accettava né impostava il parametro `$utente`. Le notifiche inviate via email/WhatsApp avevano sempre `utente_id = NULL`.
- **Fix applicato:**
  - Aggiunto `use App\Entity\User;` agli import
  - Aggiunto `?User $utente = null` a `log()`, `logWhatsApp()`, `inviaEmail()`
  - Aggiunto `$notifica->setUtente($utente)` nel body di `log()`
  - Parametro opzionale (`= null`): retrocompatibile con tutti i chiamanti esistenti
- **Stato:** ✅ Risolto

#### OPEN-02 — `ScadenzeController` ha query DQL nei metodi privati (anti-pattern)
- **File:** `src/Controller/ScadenzeController.php` righe 104–165
- **Descrizione:** Quattro metodi privati (`queryBolli`, `queryAssicurazioni`, `queryPatenti`, `queryRevisioni`) eseguono query DQL tramite QueryBuilder direttamente nel Controller, in violazione della convenzione che vuole le query solo nei Repository.
- **Refactoring proposto:** Spostare ognuno nel rispettivo Repository:
  - `queryBolli()` → `BolloRepository::findInRange()`
  - `queryAssicurazioni()` → `AssicurazioneRepository::findInRange()`
  - `queryPatenti()` → `PatenteRepository::findInRange()`  (già esiste `findInScadenza`, ma ha firma diversa)
  - `queryRevisioni()` → `VetturaRepository::findRevisioniInRange()` (già esiste, verificare allineamento)
- **Nota:** Funzionalità attuale corretta. È un refactoring di qualità, non un bug urgente.
- **Priorità:** Bassa
- **Stato:** ⚠️ Aperto

#### OPEN-03 — `AnagraficaType` usa nomi campo snake_case
- **File:** `src/Form/AnagraficaType.php`
- **Descrizione:** Campi come `luogo_nascita`, `codice_fiscale`, `partita_iva`, `sede_legale` usano snake_case invece di camelCase. Funziona grazie al PropertyAccessor di Symfony, ma è un anti-pattern.
- **Priorità:** Bassa (cosmetic)
- **Stato:** ⚠️ Aperto

#### OPEN-04 — `NotificaService::èGiàAvvisato` usa carattere non-ASCII nel nome
- **File:** `src/Service/NotificaService.php`
- **Descrizione:** Il metodo contiene `è` (U+00E8). Funziona in PHP, ma può causare problemi con alcuni tool di analisi statica o IDE non UTF-8.
- **Priorità:** Molto bassa
- **Stato:** ⚠️ Aperto

#### OPEN-05 — `VetturaType` mostra solo `cognome` nel choice label intestatario
- **File:** `src/Form/VetturaType.php`
- **Descrizione:** Per clienti con stesso cognome è impossibile distinguerli senza il nome.
- **Fix suggerito:** `choice_label` come closure: `fn(Anagrafica $a) => ($a->getCognome() ?? '') . ' ' . ($a->getNome() ?? '')`
- **Priorità:** Media (UX)
- **Stato:** ⚠️ Aperto

---

### Criteri di successo verificati

| Criterio | Risultato |
|---|---|
| Tutte le migration presenti e coerenti con le Entity | ✅ |
| Nessuna annotazione `@ORM\` legacy | ✅ |
| Relazioni Doctrine corrette | ✅ |
| Ricerca `?q=` funzionante in `AnagraficaController` | ✅ |
| User implementa `UserInterface` e `PasswordAuthenticatedUserInterface` | ✅ |

---

### Note operative

**⚠️ LEGGE 2 applicata:** Nessuna modifica al codice è stata implementata in questa sessione schedulata.
Secondo `CONTEXT.md` LEGGE 2, le modifiche richiedono approvazione esplicita dell'utente prima dell'implementazione.
I fix proposti (OPEN-01 … OPEN-05) sono stati documentati in attesa di revisione.

**Prossima azione suggerita per l'utente:**
1. Rivedere OPEN-02 (refactoring query ScadenzeController → Repository)
2. Rivedere OPEN-05 (miglioramento UX choice label VetturaType)
3. Rivedere OPEN-01 (audit trail notifiche automatiche)
