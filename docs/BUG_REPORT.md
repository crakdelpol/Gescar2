# BUG_REPORT.md — Gescar2

> Generato dall'agente bug hunter in data **2026-03-14**
> Stack: PHP 8.2+ / Symfony 7.3 / Doctrine ORM 3.x

---

## Bug Critici / Errori Runtime

### BUG-01 — `Vettura::setMarca()` tipo non nullable causa TypeError
- **File:** `src/Entity/Vettura.php` riga 102
- **Descrizione:** Il setter `setMarca(string $marca)` aveva un type hint non nullable, ma la proprietà `$marca` è `?string` e il form `VetturaType` imposta `required => false`. Se l'utente lascia il campo "Marca" vuoto, Symfony chiama `setMarca(null)` e PHP lancia un `TypeError` bloccando il salvataggio del form.
- **Impatto:** Crash durante la creazione/modifica di una vettura senza marca.
- **Fix:** Firma cambiata in `setMarca(?string $marca)`.
- **Stato:** ✅ Risolto

---

### BUG-02 — `Bollo` entity mancante campo `createdAt` (Entity ↔ DB out of sync)
- **File:** `src/Entity/Bollo.php`
- **Migration:** `Version20260310001_Bollo_StoricoPagamento.php`
- **Descrizione:** La migration 001 aggiunge la colonna `created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` alla tabella `bollo`, ma l'entity PHP non aveva il campo mappato. Questo causa un mismatch di schema Doctrine e può causare errori su `doctrine:schema:validate` o comportamenti inattesi con form Symfony (campo non esposto).
- **Fix:** Aggiunto campo `$createdAt`, getter/setter e costruttore che inizializza `new \DateTime()`.
- **Stato:** ✅ Risolto

---

### BUG-03 — `Assicurazione` entity mancante campo `createdAt` (Entity ↔ DB out of sync)
- **File:** `src/Entity/Assicurazione.php`
- **Migration:** `Version20260310002_Assicurazione_Storico.php`
- **Descrizione:** Come BUG-02, la migration 002 aggiunge `created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` alla tabella `assicurazione` ma l'entity non aveva il campo mappato.
- **Fix:** Aggiunto campo `$createdAt`, getter/setter e costruttore.
- **Stato:** ✅ Risolto

---

## Bug Logici / Comportamenti Errati

### BUG-04 — `VetturaRepository::findRevisioniInRange` non esclude veicoli esenti
- **File:** `src/Repository/VetturaRepository.php` riga 27
- **Descrizione:** Il metodo usato da `ScadenzaService::getSommarioDashboard()` non filtrava i veicoli con `esenteRevisione = true`. Il risultato era che la dashboard includeva nei contatori "revisioni scadute" e "revisioni nei prossimi 30 giorni" anche veicoli storici/esenti, gonfiando falsamente i KPI.
- **Impatto:** Dashboard mostra KPI errati per centri con veicoli esenti (rimorchi, storici, ecc.).
- **Fix:** Aggiunto `->andWhere('v.esenteRevisione = false')` al query builder.
- **Stato:** ✅ Risolto

---

### BUG-05 — `BolloType` mostra ID database come etichetta veicolo
- **File:** `src/Form/BolloType.php` riga 28
- **Descrizione:** Il form per la creazione/modifica di un bollo usava `'choice_label' => 'id'` per il campo veicolo. L'operatore vedeva una lista di numeri ID invece di targhe/marche, rendendo impossibile identificare il veicolo corretto.
- **Impatto:** UX gravemente compromessa — bug operativo.
- **Fix:** `choice_label` cambiato in closure: `fn(Vettura $v) => ($v->getTarga() ?? '—') . ' ' . $v->getMarca() . ' ' . $v->getModello()`.
- **Stato:** ✅ Risolto

---

### BUG-06 — `AnagraficaType`: validazione `cognome` solo client-side (NotBlank mancante)
- **File:** `src/Form/AnagraficaType.php` riga 21
- **Descrizione:** Il campo `cognome` aveva `'required' => true` che aggiunge solo l'attributo HTML5 `required`. Senza un constraint `NotBlank`, la validazione server-side era assente. Un'API call diretta (es. con curl) o un browser con JS disabilitato poteva creare un'anagrafica senza cognome.
- **Impatto:** Violazione di business rule — il cognome è il campo identificativo primario.
- **Fix:** Aggiunto `new NotBlank(['message' => 'Il cognome è obbligatorio'])`.
- **Stato:** ✅ Risolto

---

## Code Quality / Anti-pattern

### BUG-07 — `NotificaController::index` dipendenza iniettata mai utilizzata
- **File:** `src/Controller/NotificaController.php` riga 32
- **Descrizione:** Il parametro `NotificaRepository $repo` era iniettato nell'action `index()` ma mai utilizzato (il metodo usa `$this->notificaService->getRecenti()`). La dipendenza inutilizzata crea confusione, appesantisce il container e può generare warning con analisi statica.
- **Fix:** Rimossa la dipendenza `NotificaRepository $repo` dalla firma del metodo e dall'import `use`.
- **Stato:** ✅ Risolto

---

### BUG-08 — `ScadenzeController` usa `strtotime('+1 months')` inaffidabile
- **File:** `src/Controller/ScadenzeController.php` riga 32
- **Descrizione:** `date('Y-m-d', strtotime('+1 months'))` si comporta in modo inatteso per date di fine mese (es. 31 gennaio + 1 mese = 3 marzo su PHP, invece di 28 febbraio). Questo causava una finestra di ricerca errata sulla homepage delle scadenze.
- **Impatto:** Scadenze perdute / finestra di ricerca fuori range per utenti che usano l'app a fine mese.
- **Fix:** Sostituito con `(new \DateTime())->modify('+1 month')->format('Y-m-d')`.
- **Stato:** ✅ Risolto

---

## Bug Aperti (da risolvere in iterazioni successive)

### OPEN-01 — `NotificaInvioService::log()` non registra l'utente mittente
- **File:** `src/Service/NotificaInvioService.php` riga 179
- **Descrizione:** Il metodo privato `log()` non accetta né imposta il parametro `$utente`. Le notifiche inviate tramite email o WhatsApp automatico non registrano chi ha effettuato l'invio (`utente_id = NULL`). Al contrario, `NotificaService::registra()` accetta il parametro utente.
- **Impatto:** Audit trail incompleto per le notifiche inviate automaticamente.
- **Stato:** ⚠️ Aperto

### OPEN-02 — `ScadenzeController` ha query DQL private (anti-pattern repository)
- **File:** `src/Controller/ScadenzeController.php` righe 104–165
- **Descrizione:** Quattro metodi privati (`queryBolli`, `queryAssicurazioni`, `queryPatenti`, `queryRevisioni`) contengono DQL nel Controller, in violazione della convenzione "logica query solo nei Repository". Duplica parzialmente la logica già presente in `BolloRepository`, `AssicurazioneRepository`, ecc.
- **Impatto:** Difficile manutenzione, rischio di disallineamento con i metodi repository.
- **Stato:** ⚠️ Aperto (refactoring necessario)

### OPEN-03 — `AnagraficaType` usa nomi campo snake_case invece di camelCase
- **File:** `src/Form/AnagraficaType.php` righe 28–36
- **Descrizione:** I campi `luogo_nascita`, `codice_fiscale`, `partita_iva`, `sede_legale` usano snake_case. Funziona grazie al PropertyAccessor di Symfony che normalizza automaticamente, ma è un anti-pattern non conforme alle convenzioni Symfony.
- **Stato:** ⚠️ Aperto (code quality — bassa priorità)

### OPEN-04 — `NotificaService::èGiàAvvisato` usa carattere non-ASCII nel nome metodo
- **File:** `src/Service/NotificaService.php` riga 53
- **Descrizione:** Il nome del metodo contiene `è` (U+00E8). Funziona in PHP 7+, ma può causare problemi con alcuni IDE, strumenti di analisi statica o sistemi di encoding non UTF-8.
- **Stato:** ⚠️ Aperto (code quality)

### OPEN-05 — `VetturaType` mostra solo `cognome` nell'etichetta intestatario
- **File:** `src/Form/VetturaType.php` riga 69
- **Descrizione:** Il campo `intestatario` usa `'choice_label' => 'cognome'`. Per clienti con stesso cognome è impossibile distinguerli senza vedere anche il nome.
- **Stato:** ⚠️ Aperto (UX — già segnalato in CONTEXT.md)

---

## Test Creati

| File | Service testato | Test case |
|---|---|---|
| `tests/Service/ScadenzaServiceTest.php` | `ScadenzaService` | 13 test: calcolaStato (7 casi), getBadge (6 casi), getSommarioDashboard (2 casi) |
| `tests/Service/NotificaServiceTest.php` | `NotificaService` | 7 test: registra (2 casi), getStoricoCliente, getRecenti (2 casi), èGiàAvvisato (2 casi) |

**Comando per eseguire i test:**
```bash
php bin/phpunit
# oppure:
make test
```

---

## Riepilogo

| Categoria | Trovati | Risolti | Aperti |
|---|---|---|---|
| Bug critici runtime | 3 | 3 | 0 |
| Bug logici | 2 | 2 | 0 |
| Code quality / anti-pattern | 3 | 2 | 5 |
| **Totale** | **8** | **7** | **5** |

> **Bug critici aperti: 0** ✅
