# Gescar – Roadmap e TODO Tecnici

## Migrazione Stack Tecnologico 🔵

> Eseguire nell'ordine indicato. Ogni step è prerequisito del successivo.

### [STACK-01] MySQL 5.7 → 8.4 LTS
**Perché:** MySQL 5.7 è fuori supporto ufficiale da ottobre 2023, EOL previsto aprile 2026.  
**Impatto:** Solo infrastruttura, zero modifiche al codice applicativo.  
**Attenzione prima di migrare:**
- Rimuovere l'uso di `mysql_native_password` se presente nei DSN
- Verificare che tutte le tabelle usino InnoDB (MyISAM non supportato in 8.4)
- Convertire `Scad_Pat` da MyISAM a InnoDB prima della migrazione
- Fare un dump completo di backup prima di procedere

```bash
# Verifica engine tabelle
SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'gescar';
```

---

### [STACK-02] PHP 7.x → 8.4
**Perché:** PHP 7.x è fuori supporto. PHP 8.4 è richiesto da Symfony 7.4+.  
**Principali breaking change da gestire:**
- Controllo tipi più strict: rivedere funzioni che usano `null` implicitamente
- `str_contains()`, `array_is_list()` e altre funzioni ora native (rimuovere eventuali polyfill)
- Attributi PHP 8 (`#[...]`) al posto delle annotazioni Doctrine (`@ORM\...`) — già previsto nelle convention

```bash
# Tool utile per analizzare compatibilità
composer require --dev rector/rector
```

---

### [STACK-03] Symfony 3.x/4.x → 7.4 LTS
**Perché:** Symfony 7.4 LTS è supportato con bugfix fino a novembre 2026 e security fix fino a novembre 2027.  
**Strategia consigliata:** non aggiornare in-place, ma creare un **nuovo progetto Symfony 7.4** e migrare il codice progressivamente entità per entità.  
**Passi principali:**
1. Creare nuovo progetto: `composer create-project symfony/skeleton gescar-new`
2. Installare dipendenze: `orm`, `twig`, `form`, `validator`, `security`, `mailer`
3. Ricreare le Entity con attributi PHP 8 (non annotazioni)
4. Migrare i Controller uno alla volta
5. Migrare i template Twig (sintassi compatibile, pochi aggiustamenti)
6. Sostituire FOSUserBundle con Security nativo (vedi STACK-04)

---

### [STACK-04] FOSUserBundle → Symfony Security nativo
**Perché:** FOSUserBundle è abbandonato e incompatibile con Symfony 6+.  
**Azione:**
1. Creare Entity `User` nativa
2. Configurare `security.yaml` con provider Doctrine
3. Migrare i 3 utenti esistenti (le password bcrypt sono riusabili)
4. Implementare login form con `SecurityController`

```php
#[ORM\Entity]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private string $password;
    // ...
}
```

---

### [STACK-05] Bootstrap 3.x/4.x → 5.3.8
**Perché:** Bootstrap 5 rimuove la dipendenza da jQuery, introduce CSS custom properties e un sistema utility più moderno.  
**Principali breaking change rispetto a Bootstrap 4:**
- `mr-*` / `ml-*` → `me-*` / `ms-*` (margin end/start)
- `float-left` / `float-right` → `float-start` / `float-end`
- `text-left` / `text-right` → `text-start` / `text-end`
- jQuery non più incluso — rimuovere dipendenze jQuery dal JS custom
- Gutter nelle griglie cambiato: `no-gutters` → `g-0`

**Tool utile:** https://upgrade-guide.bootstrap.com (migrazione automatica classi)

---



### [DB-01] Convertire `Scad_Pat` da MyISAM a InnoDB
**Problema:** La tabella legacy `Scad_Pat` usa MyISAM, non supporta FK né transazioni.  
**Azione:**
```sql
ALTER TABLE Scad_Pat ENGINE=InnoDB;
```
Poi valutare migrazione dati verso `patente` + `anagrafica` ed eliminazione della tabella.

---

### [DB-02] Rimuovere UNIQUE su `assicurazione.vettura_id`
**Problema:** Impedisce lo storico dei rinnovi assicurativi.  
**Azione:**
```sql
ALTER TABLE assicurazione
  DROP INDEX UNIQ_8C972D79FC739189,
  ADD COLUMN attiva TINYINT(1) DEFAULT 1,
  ADD COLUMN data_inizio DATE NULL,
  ADD COLUMN compagnia VARCHAR(100) NULL,
  ADD COLUMN numero_polizza VARCHAR(100) NULL;
```
Aggiornare le Entity Doctrine e i Form Symfony di conseguenza.

---

### [DB-03] Rimuovere UNIQUE su `bollo.vettura_id` e aggiungere campi mancanti
**Problema:** La tabella `bollo` esiste già con ~468 record, ma la UNIQUE su `vettura_id` impedisce lo storico dei rinnovi annuali. Mancano inoltre campi per importo, super bollo e stato pagamento.  
**Azione (additiva — nessun dato esistente viene modificato):**
```sql
ALTER TABLE bollo
  DROP INDEX UNIQ_131D5E43FC739189,
  ADD COLUMN attiva TINYINT(1) DEFAULT 1,
  ADD COLUMN importo DECIMAL(8,2) NULL,
  ADD COLUMN super_bollo DECIMAL(8,2) NULL,
  ADD COLUMN pagato TINYINT(1) DEFAULT 0,
  ADD COLUMN data_pagamento DATE NULL,
  ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;
```
Aggiornare Entity `Bollo` e Form Symfony di conseguenza.  
Aggiungere indice: `ADD INDEX idx_scad_bollo (data_scadenza_bollo);`

---

### [DB-04] Creare tabella `notifica`
**Problema:** Gli avvisi sono tracciati solo con booleani (`Avvisato`) o testi liberi nelle note.  
**Azione:** Creare tabella `notifica` (vedi `02_DATABASE_SCHEMA.md`) + interfaccia di registrazione avvisi.

---

### [AUTH-01] Migrare da FOSUserBundle a Symfony Security
**Vedi [STACK-04]** — questo task è parte integrante della migrazione stack Symfony.  
Trattarlo come task separato solo se si vuole anticiparlo senza aggiornare Symfony.

---

## Priorità Media 🟡

### [FEAT-01] Dashboard scadenze imminenti
Creare una view dedicata che mostri in un colpo d'occhio:
- Revisioni in scadenza nei prossimi 30/60/90 giorni
- Assicurazioni in scadenza nei prossimi 30 giorni
- Patenti in scadenza nei prossimi 60 giorni
- Bolli in scadenza nel mese corrente

Usare colori Bootstrap (`table-danger`, `table-warning`, `table-success`) per lo stato.

---

### [FEAT-02] Campo `email` dedicato in `anagrafica`
**Problema:** La colonna `email` esiste ma è spesso vuota o mal usata.  
**Azione:** 
- Aggiungere validazione email nel Form Symfony
- Pulire i dati esistenti (script di migrazione one-shot)

---

### [FEAT-03] Campo `esente_revisione` su `vettura`
**Problema:** Veicoli con "NO REVISIONE" nelle note inquinano le liste scadenze.  
**Azione:**
```sql
ALTER TABLE vettura ADD COLUMN esente_revisione TINYINT(1) DEFAULT 0;
```
Poi aggiornare le query per escludere questi veicoli.

---

### [FEAT-04] Campo `data_scadenza_bombole` su `vettura`
**Problema:** Scadenza collaudo GPL/metano gestita solo con testo libero nelle note.  
**Azione:**
```sql
ALTER TABLE vettura
  ADD COLUMN tipo_alimentazione ENUM('benzina','diesel','gpl','metano','ibrido','elettrico') DEFAULT 'benzina',
  ADD COLUMN data_scadenza_bombole DATE NULL;
```

---

### [FEAT-05] Normalizzare `tipo_vettura`
**Problema:** Valori disomogenei nel DB (es. "rimonrchio", valori NULL, valori misti).  
**Azione:** Script di pulizia + aggiungere validazione ENUM nel Form.

---

## Priorità Bassa 🟢

### [UX-01] Ricerca globale unificata
Un campo di ricerca unico che cerchi contemporaneamente per cognome, targa e numero patente.

### [UX-02] Export CSV/Excel delle scadenze
Permettere all'operatore di esportare la lista scadenze filtrata per poi lavorarla offline.

### [UX-03] Filtri avanzati sulla lista veicoli
Filtrare per: tipo veicolo, marca, stato scadenza, città intestatario.

### [TECH-01] Aggiornare Charset a utf8mb4
```sql
ALTER DATABASE gescar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- Poi per ogni tabella:
ALTER TABLE anagrafica CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- Ripetere per tutte le tabelle
```

### [TECH-02] Aggiungere indici sulle date di scadenza
```sql
ALTER TABLE vettura ADD INDEX idx_scad_revisione (data_scadenza_revisione);
ALTER TABLE assicurazione ADD INDEX idx_scad_assicurazione (data_scadenza_assicurazione);
ALTER TABLE patente ADD INDEX idx_scad_patente (data_scadenza_patente);
```
Migliorano le performance delle query di dashboard che filtrano per data.

---

## Changelog

| Data | Versione | Descrizione |
|---|---|---|
| 2025-12 | 0.1 | Setup iniziale progetto Symfony, migrazione dati da sistema legacy |
| 2026-03 | 0.2 | Analisi DB, identificazione problemi strutturali |
| 2026-03 | 0.3 | Correzione: tabella `bollo` già presente con ~468 record |
| 2026-03 | 0.4 | Definizione versioni target stack tecnologico, aggiunta roadmap migrazione |
