# Gescar – Regole di Business e Logica Applicativa

## 1. Gestione Scadenze

### 1.1 Revisione Auto
- La **scadenza revisione** si calcola a partire dalla data dell'ultima revisione effettuata
- Periodicità standard per legge italiana:
  - Auto nuove: prima revisione a **4 anni** dall'immatricolazione
  - Auto successive: ogni **2 anni**
  - Autocarri, veicoli commerciali: ogni **anno**
- Il campo `data_scadenza_revisione` è quello operativo: viene inserito manualmente dall'operatore
- Se `data_scadenza_revisione` è NULL e `data_ultima_revisione` è presente, il sistema può calcolare la scadenza stimata
- Veicoli con nota "NO REVISIONE" vanno esclusi dalla lista scadenze

### 1.2 Assicurazione
- Scadenza annuale (di norma), ma può variare
- La scadenza attiva è quella con `attiva = 1` (dopo il fix dello storico)
- Se una nuova assicurazione viene inserita, quella precedente diventa `attiva = 0`
- Alcune assicurazioni richiedono un **preventivo**: tracciato nelle note

### 1.3 Bollo Auto
- Scadenza annuale nel mese dell'immatricolazione (o diverso per accordo)
- L'importo varia per cilindrata/alimentazione
- Il bollo può essere pagato **in anticipo**: `data_pagamento` può precedere `data_scadenza`
- Veicoli aziendali possono avere esenzioni: gestire con flag o nota

### 1.4 Patente
- Scadenza variabile per categoria e età del titolare:
  - Cat. B: ogni 10 anni (fino a 50 anni), poi ogni 5, poi ogni 3
  - Cat. C, D: ogni 5 anni
  - Cat. C, D professionali: ogni 2-5 anni con visita medica
- Il numero patente è opzionale ma utile per il rinnovo
- `categoria_patente` è un array JSON (es. `["B"]`, `["C","E"]`)

---

## 2. Logica degli Avvisi

### 2.1 Soglie di Allerta
Il sistema deve classificare ogni scadenza con un colore/stato:

| Stato | Condizione | Colore UI |
|---|---|---|
| **Scaduto** | `data_scadenza < OGGI` | Rosso |
| **In scadenza** | `data_scadenza` entro 30 giorni | Arancione |
| **In avvicinamento** | `data_scadenza` entro 60 giorni | Giallo |
| **Ok** | `data_scadenza` > 60 giorni | Verde |
| **Non definito** | `data_scadenza` è NULL | Grigio |

### 2.2 Canali di Avviso
I clienti vengono contattati principalmente per telefono.  
Il campo `note` nella tabella `notifica` registra l'esito.

Canali supportati (in ordine di priorità):
1. Telefono (chiamata)
2. SMS / WhatsApp
3. Email (solo se disponibile in anagrafica)

### 2.3 Stato Avviso per Scadenza
Ogni scadenza può avere uno stato di lavorazione:

| Stato | Significato |
|---|---|
| `da_contattare` | Scadenza imminente, cliente non ancora avvisato |
| `contattato` | Cliente avvisato, in attesa di risposta |
| `appuntamento` | Cliente ha fissato appuntamento |
| `rinnovato` | Scadenza rinnovata (revisione effettuata, ecc.) |
| `non_interessato` | Cliente non vuole procedere |

---

## 3. Anagrafica Clienti

### 3.1 Tipi di Cliente

| Tipo | Descrizione |
|---|---|
| `privato` | Persona fisica, con nome e cognome |
| `azienda` | Persona giuridica, con ragione sociale |

- Per le **aziende**: il campo `cognome` contiene la ragione sociale, `nome` può essere vuoto o contenere il referente
- Per i **privati**: entrambi nome e cognome sono valorizzati

### 3.2 Relazione Intestatario–Veicolo
- Un cliente può avere **più veicoli**
- Un veicolo ha **un solo intestatario** (FK obbligatoria)
- La patente è associata al cliente, non al veicolo

### 3.3 Dati di Contatto Prioritari
- `telefono1` è il numero principale, sempre valorizzato se disponibile
- `telefono2` è il numero secondario (coniuge, familiare, ecc.)
- `email` è facoltativa ma utile per comunicazioni digitali

---

## 4. Veicoli

### 4.1 Tipi di Veicolo Ammessi

```
autovettura     → auto normale
autocarro       → veicolo commerciale/furgone
motovettura     → motociclo
rimorchio       → rimorchio/carrello
altro           → tutto il resto
```

### 4.2 Veicoli Esclusi dalle Revisioni
Alcuni veicoli hanno nota "NO REVISIONE" e vanno esclusi dal calendario scadenze.  
Implementare un campo booleano `esente_revisione` sulla tabella `vettura`.

### 4.3 GPL/Metano
Alcuni veicoli hanno **bombole GPL o metano** con scadenza collaudo separata.  
Questa scadenza è attualmente annotata nelle note in forma libera.  
Da implementare come campo strutturato: `data_scadenza_bombole`.

---

## 5. Regole di Visualizzazione

### 5.1 Dashboard Principale
La dashboard deve mostrare:
1. Veicoli con revisione scaduta o in scadenza (30 giorni)
2. Assicurazioni in scadenza (30 giorni)
3. Patenti in scadenza (60 giorni)
4. Bolli in scadenza nel mese corrente

### 5.2 Ricerca
La ricerca deve funzionare per:
- Cognome/Nome cliente
- Targa veicolo
- Numero patente

### 5.3 Ordinamento Liste
- Le liste di scadenze devono essere ordinate per **data crescente** (prima le più urgenti)
- Le scadenze già gestite (`rinnovato`) vanno in fondo o in una sezione separata

---

## 6. Vincoli e Validazioni

| Campo | Validazione |
|---|---|
| `targa` | Formato italiano (es. AB123CD) o straniero; MAIUSCOLO; no spazi |
| `data_scadenza_*` | Non può essere nel passato remoto (>5 anni fa) senza conferma |
| `email` | Formato email valido se presente |
| `telefono1` | Obbligatorio se `email` è vuota |
| `categoria_patente` | Array con valori da lista: A, A1, A2, AM, B, B1, BE, C, CE, D, DE |
