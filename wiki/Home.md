# Gescar – Wiki

Benvenuto nella documentazione tecnica del gestionale **Gescar** per il Centro Revisioni Charlot.

---

## Indice

| Pagina | Descrizione |
|---|---|
| [Setup e Installazione](Setup-e-Installazione) | Come avviare il progetto in locale con Docker |
| [Architettura](Architettura) | Stack, struttura cartelle, Entity map, relazioni DB |
| [Controller e Route](Controller-e-Route) | Mappa completa route → controller → template |
| [Convenzioni di Sviluppo](Convenzioni-di-Sviluppo) | Naming, ORM, Bootstrap, Service vs Repository |
| [Gestione Scadenze](Gestione-Scadenze) | Logica semaforo, soglie, ScadenzaService |
| [Roadmap](Roadmap) | Task aperti prioritizzati |

---

## Il Progetto in breve

Gescar è una web application per la gestione delle scadenze di:
- **Revisioni veicoli** (periodicità legge italiana: 4 anni + ogni 2)
- **Assicurazioni RC Auto** (annuale, una per vettura)
- **Bollo auto** (annuale per mese di immatricolazione)
- **Patenti** (variabile per categoria e età del titolare)

Il database di produzione contiene circa **7.000 anagrafiche** e **4.500 veicoli**.

> ⚠️ Non committare mai il dump di produzione o dati personali reali.

---

## Stack rapido

| Layer | Tecnologia | Versione |
|---|---|---|
| Framework | Symfony | 7.3 |
| Database | MySQL | 8.0 (Docker) / 5.7 (prod) |
| Frontend | Bootstrap | 5.3 CDN |
| Auth | Symfony Security | nativo |
| Email/WhatsApp | Symfony Mailer + NotificaInvioService | 7.3 |

Repository: [github.com/CarloGagliolo/Gescar2](https://github.com/CarloGagliolo/Gescar2)
