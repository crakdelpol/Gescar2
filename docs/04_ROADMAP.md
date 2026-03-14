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

### ✅ [STACK-02] PHP 7.x → 8.4
**Stato:** ✅ Completato — PHP 8.2 in esecuzione (target 8.4, compatibile).
Attributi PHP 8 (`#[ORM\...]`) già usati in tutte le Entity. Polyfill rimossi in `composer.json`.

---

### ✅ [STACK-03] Symfony 3.x/4.x → 7.4 LTS
**Stato:** ✅ Completato — Symfony **7.3** in esecuzione (v7.3.11). Nuovo progetto creato da zero, tutte le Entity riscritte con attributi PHP 8.

**Prossimo step:** aggiornare il constraint da `7.3.*` a `7.4.*` in `composer.json` quando Symfony 7.4 sarà disponibile.

---

### ✅ [STACK-04] FOSUserBundle → Symfony Security nativo
**Stato:** ✅ Completato — `src/Entity/User.php` implementa `UserInterface` + `PasswordAuthenticatedUserInterface`.
Migration `Version20260310003_User_Create.php` presente. `SecurityController` + `security.yaml` configurati.
Ruoli: `ROLE_ADMIN` (default) e `ROLE_SUPER_ADMIN`.

---

### ✅ [STACK-05] Bootstrap 3.x/4.x → 5.3.8
**Stato:** ✅ Completato — Bootstrap 5.3 via CDN in tutti i template. Zero jQuery. Classi aggiornate (`me-*`, `ms-*`, `data-bs-*`). Tutti i template riescritti (vedi `docs/CHANGELOG_FRONTEND.md`).

---



### [DB-01] Convertire `Scad_Pat` da MyISAM a InnoDB
**Problema:** La tabella legacy `Scad_Pat` usa MyISAM, non supporta FK né transazioni.  
**Azione:**
```sql
ALTER TABLE Scad_Pat ENGINE=InnoDB;
```
Poi valutare migrazione dati verso `patente` + `anagrafica` ed eliminazione della tabella.

---

### ✅ [DB-02] Rimuovere UNIQUE su `assicurazione.vettura_id`
**Stato:** ✅ Completato — Migration `Version20260310002_Assicurazione_Storico.php` applicata.
Campi aggiunti: `attiva`, `data_inizio`, `compagnia`, `numero_polizza`, `created_at`. Relazione rimane OneToOne a livello applicativo.

---

### ✅ [DB-03] Rimuovere UNIQUE su `bollo.vettura_id` e aggiungere campi mancanti
**Stato:** ✅ Completato — Migration `Version20260310001_Bollo_StoricoPagamento.php` applicata.
Campi aggiunti: `attiva`, `importo`, `super_bollo`, `pagato`, `data_pagamento`, `created_at`. Relazione rimane OneToOne a livello applicativo.

---

### ✅ [DB-04] Creare tabella `notifica`
**Stato:** ✅ Completato — Migration `Version20260310004_Notifica_Create.php` presente. Entity `Notifica`, `NotificaController`, `NotificaService`, `NotificaInvioService` (email + WhatsApp) tutti implementati.

---

### ✅ [AUTH-01] Migrare da FOSUserBundle a Symfony Security
**Stato:** ✅ Completato — vedi [STACK-04].

---

## Priorità Media 🟡

### ✅ [FEAT-01] Dashboard scadenze imminenti
**Stato:** ✅ Completato — `DashboardController` + `ScadenzaService::getSommarioDashboard()` implementati. KPI card con badge semaforo (danger/warning/info/success). Bootstrap Tabs per le viste per tipo di scadenza.

---

### [FEAT-02] Campo `email` dedicato in `anagrafica`
**Problema:** La colonna `email` esiste ma è spesso vuota o mal usata.  
**Azione:** 
- Aggiungere validazione email nel Form Symfony
- Pulire i dati esistenti (script di migrazione one-shot)

---

### ✅ [FEAT-03] Campo `esente_revisione` su `vettura`
**Stato:** ✅ Completato — `Vettura::$esenteRevisione` (bool, default false) presente nell'entity. Migration `Version20260310005_Vettura_Indici.php` include il campo. Badge "Esente revisione" mostrato in `vettura/show.html.twig`.

---

### ✅ [FEAT-04] Campo scadenza impianto GPL/metano su `vettura`
**Stato:** ✅ Parzialmente completato — `Vettura::$dataScadenzaImpianto` (DateTime nullable) presente nell'entity, mostrato come badge semaforo in `vettura/show.html.twig`. Il campo `carburante` gestisce il tipo di alimentazione. Campo rinominato da `data_scadenza_bombole` a `data_scadenza_impianto`.
**Pendente:** aggiungere `tipo_alimentazione` come ENUM separato dal campo `carburante` (attualmente stringa libera).

---

### [FEAT-05] Normalizzare `tipo_vettura`
**Problema:** Valori disomogenei nel DB (es. "rimonrchio", valori NULL, valori misti).  
**Azione:** Script di pulizia + aggiungere validazione ENUM nel Form.

---

## Priorità Bassa 🟢

### ✅ [UX-01] Ricerca globale navbar
**Stato:** ✅ Completato — `AnagraficaController::index()` legge `?q=` e usa `AnagraficaRepository::searchGlobale()` (cerca per cognome/nome e targa veicolo). Template mostra banner feedback con contatore risultati e bottone "Cancella ricerca".
**Pendente:** estendere la ricerca a numero patente.

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

### ✅ [TECH-02] Aggiungere indici sulle date di scadenza
**Stato:** ✅ Parzialmente completato — Migration `Version20260310005_Vettura_Indici.php` aggiunge `idx_scad_revisione` su `vettura.data_scadenza_revisione` e `idx_scad_patente` su `patente.data_scadenza_patente`.
**Pendente:** indice su `assicurazione.data_scadenza_assicurazione` (da aggiungere in futura migration).

---

## Changelog

| Data | Versione | Descrizione |
|---|---|---|
| 2025-12 | 0.1 | Setup iniziale progetto Symfony, migrazione dati da sistema legacy |
| 2026-03 | 0.2 | Analisi DB, identificazione problemi strutturali |
| 2026-03 | 0.3 | Correzione: tabella `bollo` già presente con ~468 record |
| 2026-03 | 0.4 | Definizione versioni target stack tecnologico, aggiunta roadmap migrazione |
| 2026-03-10 | 0.5 | Scritte tutte le migration (000–005): schema iniziale, bollo, assicurazione, user, notifica, indici+esente_revisione |
| 2026-03-10 | 0.5 | Implementate entity `User` e `Notifica`. Configurata autenticazione Symfony Security nativa. |
| 2026-03-11 | 0.6 | `NotificaService` e `NotificaController` implementati. `ScadenzaService` con semaforo stati. Dashboard con KPI. |
| 2026-03-12 | 0.7 | `Vettura::$esenteRevisione` aggiunto. `AnagraficaController::show()` arricchito con vetture e storico notifiche. |
| 2026-03-14 | 0.8 | Riscrittura completa template Bootstrap 5 (anagrafica, vettura). Ricerca navbar `?q=` collegata. `NotificaInvioService` (email + WhatsApp) aggiunto. Documentazione aggiornata (roadmap, bundle versions, ADR). |
