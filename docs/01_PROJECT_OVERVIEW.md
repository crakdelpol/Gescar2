# Gescar – Panoramica del Progetto

## Descrizione

**Gescar** è una web application gestionale per un centro revisioni auto.
Permette agli operatori di tracciare e gestire le scadenze di revisioni, assicurazioni, patenti e bollo per una clientela composta da privati e aziende.

---

## Stack Tecnologico

| Layer | Tecnologia | Versione |
|---|---|---|
| Backend | PHP | 8.2+ |
| Framework | Symfony | 7.3 |
| Database | MySQL | 8.0 (Docker) |
| ORM | Doctrine ORM | 3.x |
| Frontend | Bootstrap | 5.3 CDN |
| Auth | Symfony Security | nativo |

---

## Obiettivi Applicativi

1. **Gestione anagrafica clienti** — privati e aziende
2. **Gestione veicoli** — associati all'intestatario, con tutti i dati di scadenza
3. **Tracciamento scadenze** per ogni veicolo: revisione, assicurazione, bollo, impianto GPL/metano
4. **Tracciamento scadenza patente** per ogni cliente
5. **Dashboard scadenze imminenti** con badge semaforo (rosso/giallo/blu/verde)
6. **Registro notifiche** — tracciamento completo degli avvisi inviati ai clienti
7. **Invio notifiche** — email e WhatsApp tramite servizio dedicato

---

## Utenti del Sistema

| Ruolo | Accesso |
|---|---|
| `ROLE_SUPER_ADMIN` | Accesso completo |
| `ROLE_ADMIN` | Operatore del centro revisioni |

Tutto il sito richiede autenticazione. Non è previsto accesso self-service per il cliente finale.

---

## Dati di Produzione

Il database contiene dati reali di produzione (~7.000 anagrafiche, ~4.500 veicoli).

> ⚠️ Non includere mai dati reali in commit, fixtures o test. Usare sempre dati anonimi.
> Non committare mai dump del database.
