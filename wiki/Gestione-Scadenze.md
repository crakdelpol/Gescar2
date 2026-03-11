# Gestione Scadenze

## Logica Semaforo

Ogni scadenza viene classificata in 4 stati in base ai giorni rimanenti rispetto a oggi. La logica è centralizzata in `ScadenzaService`.

### Stati e Soglie

| Stato | Condizione | Badge Bootstrap | Colore |
|---|---|---|---|
| `scaduto` | giorni < 0 | `danger` | 🔴 Rosso |
| `in_scadenza` | 0 ≤ giorni ≤ 30 | `warning` | 🟡 Giallo |
| `in_avvicinamento` | 31 ≤ giorni ≤ 60 | `info` | 🔵 Azzurro |
| `ok` | giorni > 60 | `success` | 🟢 Verde |
| `non_definito` | data null | `secondary` | ⚫ Grigio |

---

## ScadenzaService

File: `src/Service/ScadenzaService.php`

### API

```php
// Calcola lo stato di una scadenza rispetto a oggi
$stato = $scadenzaService->calcolaStato($dataScadenza);
// ritorna: 'scaduto' | 'in_scadenza' | 'in_avvicinamento' | 'ok' | 'non_definito'

// Restituisce la classe Bootstrap per il badge
$badge = $scadenzaService->getBadge($stato);
// ritorna: 'danger' | 'warning' | 'info' | 'success' | 'secondary'

// Dati aggregati per la dashboard
$sommario = $scadenzaService->getSommarioDashboard();
// array con:
//   revisioni_scadute
//   revisioni_30gg
//   assicurazioni_30gg
//   patenti_60gg
//   bolli_mese_corrente
```

### Uso nel Template Twig

Il service viene passato come variabile dal controller:

```twig
{% set stato = scadenzaService.calcolaStato(vettura.dataScadenzaRevisione) %}
{% set badge = scadenzaService.getBadge(stato) %}
<span class="badge bg-{{ badge }}">{{ vettura.dataScadenzaRevisione|date('d/m/Y') }}</span>
```

---

## Pagina Scadenze (Home)

Route: `app_scadenze_home` → `/`

Mostra le scadenze nel periodo **oggi → +1 mese** (modificabile tramite il form filtro date).

Le scadenze sono organizzate in **4 tab Bootstrap**:
1. Revisioni
2. Assicurazioni
3. Bolli
4. Patenti

Ogni riga ha un pulsante 🔔 per registrare una notifica al cliente direttamente dalla pagina.

---

## Regole di Business (dalla legge italiana)

### Revisioni Veicoli
- Prima revisione: **4 anni** dall'immatricolazione
- Revisioni successive: ogni **2 anni**
- Veicoli commerciali/speciali: periodicità differente
- Campo `esente_revisione` (TODO) per veicoli esenti

### Assicurazione RC Auto
- Obbligatoria per legge
- **Una sola** polizza attiva per veicolo
- Scadenza annuale (o semestrale)

### Bollo Auto
- Annuale
- Scadenza: ultimo giorno del mese successivo al mese di immatricolazione
- **Un solo** bollo per veicolo
- Alcuni veicoli sono esenti (es. veicoli storici >30 anni, disabili)

### Patente
- Scadenza variabile per categoria:
  - Cat. B sotto i 50 anni: ogni **10 anni**
  - Cat. B sopra i 50 anni: ogni **5 anni**
  - Cat. C, D: ogni **5 anni**
- Rinnovo tramite visita medica

---

## NotificaService

File: `src/Service/NotificaService.php`

Traccia le comunicazioni inviate ai clienti per le scadenze.

```php
// Registra una nuova notifica
$notificaService->registra($anagrafica, $tipo, $canale, $esito, $note, $vettura, $utente);

// Controlla se il cliente è già stato avvisato di recente
$notificaService->èGiàAvvisato($anagrafica, $tipo, $giorni = 7); // bool

// Storico notifiche per cliente
$notificaService->getStoricoCliente($anagrafica);

// Notifiche recenti
$notificaService->getRecenti($giorni = 7);
```

### Costanti Notifica

```php
// Tipo scadenza
Notifica::TIPO_REVISIONE
Notifica::TIPO_ASSICURAZIONE
Notifica::TIPO_BOLLO
Notifica::TIPO_PATENTE

// Canale comunicazione
Notifica::CANALE_TELEFONO
Notifica::CANALE_SMS
Notifica::CANALE_EMAIL
Notifica::CANALE_WHATSAPP

// Esito
Notifica::ESITO_INVIATA
Notifica::ESITO_NON_RISPONDE
Notifica::ESITO_RINNOVATO
Notifica::ESITO_NON_INTERESSATO
```
