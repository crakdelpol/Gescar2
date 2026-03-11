# Gescar – Schema Database

## Stato Attuale delle Tabelle

### `anagrafica`
Tabella centrale dei clienti. Contiene sia privati che aziende.

```sql
CREATE TABLE anagrafica (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nome        VARCHAR(50),
  cognome     VARCHAR(50),
  tipo        VARCHAR(20),   -- 'privato' | 'azienda'
  indirizzo   VARCHAR(50),
  cap         VARCHAR(10),
  citta       VARCHAR(50),
  provincia   VARCHAR(5),
  email       VARCHAR(100),  -- campo spesso vuoto, usato anche per note
  telefono1   VARCHAR(50),
  telefono2   VARCHAR(50),
  note        TEXT,
  referente   VARCHAR(100)   -- usato per aziende
) ENGINE=InnoDB CHARSET=utf8_unicode_ci;
```

**Problemi noti:**
- Campo `email` spesso vuoto o usato in modo non strutturato
- Nessuna separazione formale tra privati e aziende (solo campo `tipo`)
- Dati "sporchi": email scritte nel campo telefono, note nei campi sbagliati

---

### `vettura`
Veicoli associati a un intestatario tramite FK.

```sql
CREATE TABLE vettura (
  id                       INT AUTO_INCREMENT PRIMARY KEY,
  intestatario_id          INT,            -- FK -> anagrafica.id
  targa                    VARCHAR(50),
  numero_telaio            VARCHAR(100),
  tipo_vettura             VARCHAR(20),    -- 'autovettura' | 'autocarro' | 'rimorchio'
  marca                    VARCHAR(50),
  modello                  VARCHAR(100),
  data_ultima_revisione    DATE,
  data_scadenza_revisione  DATE,
  note                     VARCHAR(255),
  FOREIGN KEY (intestatario_id) REFERENCES anagrafica(id)
) ENGINE=InnoDB CHARSET=utf8_unicode_ci;
```

**Problemi noti:**
- `data_ultima_revisione` spesso NULL anche per veicoli attivi
- Il campo `modello` contiene a volte note o date (dati sporchi)
- `tipo_vettura` non normalizzato (valori liberi, typo: "rimonrchio")

---

### `patente`
Patente di guida associata a un intestatario (1:1).

```sql
CREATE TABLE patente (
  id                    INT AUTO_INCREMENT PRIMARY KEY,
  intestatario_id       INT UNIQUE,       -- FK -> anagrafica.id (1:1)
  numero_patente        VARCHAR(50),
  categoria_patente     JSON,             -- es. ["B"], ["C","E"]
  data_scadenza_patente DATE,
  note                  VARCHAR(255),
  FOREIGN KEY (intestatario_id) REFERENCES anagrafica(id)
) ENGINE=InnoDB CHARSET=utf8_unicode_ci;
```

**Problemi noti:**
- Relazione 1:1 forzata con UNIQUE KEY: impossibile gestire rinnovi con cambio numero patente

---

### `assicurazione`
Assicurazione associata a un veicolo (1:1 attuale).

```sql
CREATE TABLE assicurazione (
  id                           INT AUTO_INCREMENT PRIMARY KEY,
  vettura_id                   INT UNIQUE,   -- FK -> vettura.id (1:1, PROBLEMA)
  data_scadenza_assicurazione  DATE NOT NULL,
  note                         VARCHAR(255),
  FOREIGN KEY (vettura_id) REFERENCES vettura(id)
) ENGINE=InnoDB CHARSET=utf8_unicode_ci;
```

**Problema critico:** La UNIQUE su `vettura_id` impedisce di salvare lo storico dei rinnovi.

---

### `Scad_Pat` (LEGACY – da dismettere)
Tabella vecchia con i dati di patenti gestiti prima della migrazione a Symfony.

```sql
CREATE TABLE Scad_Pat (
  ID_Prog     INT AUTO_INCREMENT PRIMARY KEY,
  Cognome     VARCHAR(50),
  Nome        VARCHAR(50),
  Res_Citta   VARCHAR(50),
  Res_Indir   VARCHAR(50),
  Tel1        VARCHAR(50),
  Tel2        VARCHAR(50),
  Scad_Pat    DATETIME,
  Cat_Pat     VARCHAR(50),
  Num_Pat     VARCHAR(50),
  Note        LONGTEXT,
  Pag_Registro VARCHAR(50),
  Avvisato    TINYINT(1),
  Rinnovato   TINYINT(1)
) ENGINE=MyISAM CHARSET=utf8;   -- ⚠️ MyISAM: no FK, no transazioni
```

**Stato:** Da migrare in `patente` + `anagrafica` e poi eliminare.

---

### `bollo`
Bollo auto associato a un veicolo. Struttura attuale (1:1 con `vettura`).

```sql
CREATE TABLE bollo (
  id                   INT AUTO_INCREMENT PRIMARY KEY,
  vettura_id           INT,            -- FK -> vettura.id
  data_scadenza_bollo  DATE,
  note                 VARCHAR(255),
  UNIQUE KEY (vettura_id),             -- ⚠️ impedisce storico rinnovi
  FOREIGN KEY (vettura_id) REFERENCES vettura(id)
) ENGINE=InnoDB CHARSET=utf8_unicode_ci;
```

**Dati presenti:** ~468 record attivi.  
**Problemi noti:**
- La UNIQUE su `vettura_id` ha lo stesso problema di `assicurazione`: impedisce di mantenere lo storico dei rinnovi annuali
- Nessun campo `importo`: alcune note contengono importi in testo libero (es. "super bollo circa 435€")
- Alcune note indicano periodicità speciali: "OGNI 4 MESI", "OGNI 12 MESI + SUPER BOLLO" — non strutturate
- Nessun campo per tracciare se il bollo è stato effettivamente pagato

---

### `fos_user`
Tabella utenti gestita da FOSUserBundle (deprecato).

---

## Schema Proposto – Miglioramenti

### Modifica `bollo` – storico e campi aggiuntivi

Stesso approccio di `assicurazione`: rimuovere la UNIQUE e aggiungere colonne utili in modo additivo.

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

> ⚠️ I 468 record esistenti non vengono toccati. Avranno `attiva = 1` di default.

---

### Modifica `assicurazione` – storico completo

Rimuovere la UNIQUE su `vettura_id` e aggiungere un flag `attiva`:

```sql
ALTER TABLE assicurazione
  DROP INDEX UNIQ_8C972D79FC739189,
  ADD COLUMN attiva TINYINT(1) DEFAULT 1,
  ADD COLUMN data_inizio DATE,
  ADD COLUMN compagnia VARCHAR(100),
  ADD COLUMN numero_polizza VARCHAR(100),
  ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;
```

---

### Nuova tabella `notifica`
Per tracciare ogni avviso inviato al cliente.

```sql
CREATE TABLE notifica (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  anagrafica_id   INT,
  vettura_id      INT,
  tipo_scadenza   ENUM('revisione','assicurazione','bollo','patente') NOT NULL,
  canale          ENUM('telefono','sms','email','whatsapp') NOT NULL,
  data_invio      DATETIME DEFAULT CURRENT_TIMESTAMP,
  esito           ENUM('inviata','non_risponde','rinnovato','non_interessato'),
  note            VARCHAR(255),
  utente_id       INT,               -- chi ha fatto la chiamata
  FOREIGN KEY (anagrafica_id) REFERENCES anagrafica(id),
  FOREIGN KEY (vettura_id) REFERENCES vettura(id)
) ENGINE=InnoDB CHARSET=utf8mb4;
```

---

## Relazioni tra Tabelle

```
anagrafica (1) ──< (N) vettura
anagrafica (1) ──< (1) patente
vettura    (1) ──< (N) assicurazione   [dopo fix UNIQUE]
vettura    (1) ──< (N) bollo           [dopo fix UNIQUE]
anagrafica (1) ──< (N) notifica
vettura    (1) ──< (N) notifica
```

---

## Regole sui Dati

- **Targa**: normalizzare sempre in MAIUSCOLO, no spazi
- **Tipo vettura**: usare solo valori da enum controllato: `autovettura`, `autocarro`, `motovettura`, `rimorchio`, `altro`
- **Engine**: tutte le tabelle devono usare **InnoDB**
- **Charset**: usare **utf8mb4** (supporta emoji e caratteri speciali completi)
- **Date**: usare sempre il tipo `DATE` o `DATETIME`, mai stringhe
