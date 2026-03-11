# Gescar – Panoramica del Progetto

## Descrizione
**Gescar** è una web application gestionale per un centro revisioni auto.  
Permette di tracciare e gestire le scadenze di revisioni, assicurazioni, patenti e bollo per una clientela composta da privati e aziende.

## Stack Tecnologico

### Versioni Attuali (legacy)

| Layer | Tecnologia | Versione Stimata | Stato |
|---|---|---|---|
| Backend | PHP | 7.x | ⛔ Fuori supporto |
| Framework | Symfony | 3.x / 4.x | ⛔ Fuori supporto |
| Database | MySQL | 5.7 | ⛔ Fuori supporto (EOL apr 2026) |
| Frontend | Bootstrap | 3.x / 4.x | ⚠️ Solo bugfix critici |
| Auth | FOSUserBundle | legacy | ⛔ Abbandonato |

### Versioni Target

| Layer | Tecnologia | Versione Target | Note |
|---|---|---|---|
| Backend | PHP | **8.4** | Richiesto da Symfony 7.4+ |
| Framework | Symfony | **7.4 LTS** | LTS fino a nov 2027 – scelta consigliata per produzione |
| Database | MySQL | **8.4 LTS** | LTS stabile, supportato fino al 2032 |
| Frontend | Bootstrap | **5.3.8** | Ultima release stabile, no jQuery |
| Auth | Symfony Security | nativo | Sostituisce FOSUserBundle |

### Ordine di Migrazione

Le dipendenze impongono un ordine preciso:

1. **MySQL 5.7 → 8.4** — indipendente dal codice, si fa prima
2. **PHP 7.x → 8.4** — necessario per Symfony 7.4
3. **Symfony → 7.4 LTS** — include sostituzione di FOSUserBundle con Security nativo
4. **Bootstrap → 5.3** — solo frontend, non blocca nulla, si fa per ultimo

> ⚠️ **Perché Symfony 7.4 e non 8.0?** Symfony 8.0 è uscito a novembre 2025 ma non è LTS: il supporto termina a luglio 2026. Per un gestionale di produzione conviene 7.4 LTS, supportato fino a novembre 2027.
>
> ⚠️ **Perché MySQL 8.4 e non 9.x?** MySQL 9.x non ha ancora una versione LTS annunciata ed è una release "Innovation". MySQL 8.4 LTS è la scelta sicura e stabile.

## Obiettivi Applicativi

1. **Gestione anagrafica clienti** – privati e aziende
2. **Gestione veicoli** – associati all'intestatario
3. **Tracciamento scadenze** per ogni veicolo:
   - Revisione (ultima + prossima)
   - Assicurazione
   - Bollo (da implementare)
4. **Tracciamento scadenza patente** per ogni cliente
5. **Sistema di notifiche/avvisi** per scadenze imminenti
6. **Storico** interventi e avvisi inviati

## Utenti del Sistema

| Ruolo | Descrizione |
|---|---|
| `ROLE_SUPER_ADMIN` | Accesso completo, gestione utenti |
| `ROLE_ADMIN` | Operatore del centro revisioni |

> Attualmente non è previsto un accesso self-service per il cliente finale.

## Nomi dei Clienti Reali (Dati di Produzione)

Il database contiene dati reali di produzione (>7000 anagrafiche, >2200 veicoli).  
**Non includere mai dati personali reali in test, seed fixtures o commit Git.**  
Usare sempre dati anonimi o faker per i test.

## Struttura Directory Prevista (Symfony 7.4)

```
gescar/
├── config/
├── src/
│   ├── Controller/
│   │   ├── AnagraficaController.php
│   │   ├── VetturaController.php
│   │   ├── PatenteController.php
│   │   ├── AssicurazioneController.php
│   │   ├── BolloController.php
│   │   └── NotificaController.php       ← da implementare
│   ├── Entity/
│   │   ├── Anagrafica.php
│   │   ├── Vettura.php
│   │   ├── Patente.php
│   │   ├── Assicurazione.php
│   │   ├── Bollo.php
│   │   └── Notifica.php                 ← da implementare
│   ├── Repository/
│   ├── Form/
│   └── Security/                        ← da creare per auth nativa Symfony
├── templates/
│   └── (Twig templates con Bootstrap 5)
└── public/
```

## Convenzioni di Nomenclatura

- **Entità PHP**: PascalCase (es. `Anagrafica`, `Vettura`)
- **Tabelle DB**: snake_case minuscolo (es. `anagrafica`, `vettura`)
- **Campi DB**: snake_case (es. `data_scadenza_revisione`)
- **Route Symfony**: `gescar_[entità]_[azione]` (es. `gescar_vettura_index`)
- **Template Twig**: `[entità]/[azione].html.twig`

## Stato Attuale del Progetto (Dicembre 2025)

### Funzionalità applicative
- [x] Anagrafica clienti (privati e aziende)
- [x] Gestione veicoli con scadenza revisione
- [x] Gestione patenti
- [x] Gestione assicurazioni (parziale – senza storico)
- [x] Gestione bollo auto (parziale – senza storico e senza importo)
- [x] Autenticazione utenti (FOSUserBundle)
- [ ] Storico assicurazioni (rimuovere UNIQUE su `assicurazione.vettura_id`)
- [ ] Storico bollo (rimuovere UNIQUE su `bollo.vettura_id`, aggiungere importo/pagato)
- [ ] Sistema notifiche strutturato
- [ ] Dashboard scadenze imminenti

### Migrazione stack tecnologico
- [ ] MySQL 5.7 → 8.4 LTS
- [ ] PHP 7.x → 8.4
- [ ] Symfony 3.x/4.x → 7.4 LTS
- [ ] FOSUserBundle → Symfony Security nativo
- [ ] Bootstrap 3.x/4.x → 5.3.8
