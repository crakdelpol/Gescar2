# CHANGELOG FRONTEND – Gescar2

Registro delle modifiche all'interfaccia utente (template Twig, Bootstrap, JS).

---

## [2026-03-14] – Intervento front-end agente gescar-frontend-agent

### Controller modificati

#### `src/Controller/AnagraficaController.php`
- **Aggiunti import** `VetturaRepository` e `NotificaRepository`.
- **Metodo `show()`**: ora inietta `VetturaRepository` e `NotificaRepository` per passare al template `anagrafica/show.html.twig` le variabili `vetture` (veicoli intestati all'anagrafica, ordinati per targa) e `notifiche` (storico notifiche del cliente, ordinate per data DESC).

---

### Template Twig modificati

#### `templates/anagrafica/show.html.twig` ← **Riscrittura completa**
- Aggiunta intestazione pagina con titolo Bootstrap, badge tipo cliente (privato/azienda) e bottoni azione (Lista, Modifica, Nuova notifica).
- Dati anagrafici presentati in due card Bootstrap: "Dati personali" e "Dati fiscali / sede" con layout `dl.row`.
- **NUOVO**: sezione *Veicoli intestati* con tabella Bootstrap `table-hover`, semaforo scadenza revisione (badge danger/warning/success), icona veicoli esenti.
- **NUOVO**: sezione *Storico notifiche* con tabella Bootstrap completa di badge colorati per tipo scadenza ed esito (coerenti con `notifica/index.html.twig`).
- Bottone "Elimina" integrato in fondo alla pagina con separatore `<hr>`.

#### `templates/anagrafica/index.html.twig` ← **Riscrittura completa**
- Intestazione pagina con bottone "Nuovo cliente".
- **NUOVO**: banner feedback ricerca attiva con parametro `?q=`, contatore risultati e bottone "Cancella ricerca".
- Tabella `table-dark` thead, `table-hover`, con link cliente, badge tipo, link `tel:`, bottoni icona show/edit.
- Messaggio empty state localizzato in italiano.

#### `templates/anagrafica/new.html.twig`
- Intestazione pagina Bootstrap con bottone "Lista clienti".
- Form racchiuso in card Bootstrap.

#### `templates/anagrafica/edit.html.twig`
- Intestazione pagina Bootstrap con bottoni "Dettaglio" e "Lista".
- Form racchiuso in card Bootstrap.
- Bottone "Elimina" spostato sotto separatore `<hr>`.

#### `templates/anagrafica/_form.html.twig` ← **Riscrittura completa**
- `form_start` con attributi `novalidate` e classe `needs-validation` per validazione Bootstrap 5.
- Campi organizzati in griglia Bootstrap `row g-3` con layout a colonne responsive.
- Attributi `form-control`/`form-select` su tutti i widget.
- Campi CF: `maxlength=16`, `pattern`, `title` descrittivo.
- Campi P.IVA: `maxlength=11`, `pattern` solo cifre.
- Bottoni "Salva" (primary) e "Annulla" (outline-secondary) con icone.
- **JS inline**: validazione Bootstrap `was-validated` su submit; uppercase automatico sul campo codice fiscale.

#### `templates/anagrafica/_delete_form.html.twig`
- Bottone `btn-danger btn-sm` con icona `bi-trash`.
- Messaggio `confirm()` localizzato in italiano.

#### `templates/vettura/show.html.twig` ← **Riscrittura completa**
- Intestazione con targa in evidenza, marca/modello, badge "Esente revisione".
- Card "Dati veicolo" con DL row.
- Card "Scadenze" con semaforo badge (danger/warning/info) per revisione e impianto GPL/metano.
- Card intestatario con link ad anagrafica e link `tel:`.
- Nota veicolo in alert Bootstrap.
- Bottone "Elimina" in fondo con `<hr>`.

#### `templates/vettura/index.html.twig` ← **Riscrittura completa**
- Intestazione con bottone "Nuovo veicolo".
- Tabella `table-dark` thead, colonne: Targa, Tipo, Marca/Modello, Carburante, Intestatario (link ad anagrafica), Scad. Revisione (semaforo badge).
- Bottoni icona show/edit.
- Empty state localizzato.

#### `templates/vettura/new.html.twig`
- Intestazione pagina Bootstrap con bottone "Lista veicoli".
- Form racchiuso in card Bootstrap.

#### `templates/vettura/edit.html.twig`
- Intestazione pagina Bootstrap con bottoni "Dettaglio" e "Lista".
- Form racchiuso in card Bootstrap.
- Bottone "Elimina" spostato sotto `<hr>`.

#### `templates/vettura/_form.html.twig` ← **Riscrittura completa**
- `form_start` con `needs-validation`.
- Griglia Bootstrap `row g-3` responsive, attributi `form-control`/`form-select`.
- Corretti nomi campo: `tipo` (non `tipoVettura`), rimosso `esenteRevisione` (non presente in `VetturaType`).
- Bottoni "Salva"/"Aggiorna" e "Annulla" con icone.
- **JS inline**: validazione Bootstrap su submit; uppercase automatico su targa e numero telaio.

#### `templates/vettura/_delete_form.html.twig`
- Bottone `btn-danger btn-sm` con icona `bi-trash`.
- Messaggio `confirm()` localizzato in italiano.

---

### Note e criteri di successo

| Criterio | Stato |
|---|---|
| Classi Bootstrap deprecate nei template (`mr-*`, `ml-*`, `float-left`) | ✅ Nessuna trovata |
| Form con validazione visiva Bootstrap (`needs-validation`, `was-validated`) | ✅ Applicato ad anagrafica e vettura |
| Ricerca navbar con `?q=` collegata al controller | ✅ Già funzionante in `AnagraficaController::index`, aggiunto banner feedback in `index.html.twig` |
| `anagrafica/show.html.twig` mostra veicoli e notifiche | ✅ Completato con dati passati dal controller |
| Conferme JS prima di eliminazione | ✅ Tutti i `_delete_form` con `confirm()` localizzati |
| Bottoni azione con classi Bootstrap corrette | ✅ Applicato a tutti i template toccati |
