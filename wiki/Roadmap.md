# Roadmap

Vedi anche `docs/04_ROADMAP.md` nel repository per la versione completa con changelog.

---

## 🔴 Alta Priorità

### Campo telefono2
- Aggiungere `telefono2` all'entity `Anagrafica`
- Le business rules prevedono due numeri di telefono per cliente
- Richiede migration + aggiornamento form + template

### Paginazione Liste
- Le liste (`anagrafica/index`, `vettura/index`, ecc.) non hanno paginazione
- Valutare KnpPaginatorBundle o paginazione manuale con QueryBuilder
- Necessaria prima del go-live (DB prod: ~7.000 anagrafiche)

---

## 🟡 Media Priorità

### DataFixtures Aggiornate
- Aggiungere `User` e `Notifica` alle fixtures
- Attualmente le fixtures non creano utenti (l'utente admin va inserito manualmente)

### Indice su `assicurazione.data_scadenza_assicurazione`
- Migration da aggiungere per migliorare performance query dashboard

### VetturaType — Choice Label
- `VetturaType` mostra solo `cognome` nel choice label per intestatario
- Migliorare con `cognome + ' ' + nome`

### Comando Console Crea Admin
- Creare `src/Command/CreateAdminCommand.php`
- Permettere `php bin/console app:create-admin email password`
- Evita l'INSERT SQL manuale

---

## 🟢 Bassa Priorità / Future

### Upgrade MySQL 5.7 → 8.4 LTS (produzione)
- Il DB di produzione è su MySQL 5.7 (EOL ottobre 2023)
- L'ambiente Docker è già su MySQL 8.0
- Target finale: MySQL 8.4 LTS

### Upgrade Symfony 7.3 → 7.4 LTS
- Attendere release stabile 7.4
- Verificare deprecazioni attuali nel log

### Export Scadenze PDF/Excel
- Export della lista scadenze del mese in PDF o Excel
- Utile per stampa e archiviazione

### Dashboard Avanzata
- Grafici scadenze per mese (Chart.js o Symfony UX)
- Riepilogo notifiche della settimana

---

## ✅ Completato

- ✅ Login con Symfony Security nativo (sostituisce FOSUserBundle)
- ✅ Entity User e Notifica + tutte le migration (000–005)
- ✅ Bootstrap 4 → 5.3 CDN (zero jQuery) — tutti i template riscritti
- ✅ Dashboard KPI scadenze imminenti
- ✅ Pagina scadenze con tab e badge semaforo
- ✅ CRUD completo: Anagrafica, Vettura, Assicurazione, Bollo, Patente, Notifica
- ✅ ScadenzaService, NotificaService, **NotificaInvioService** (email + WhatsApp)
- ✅ Ricerca navbar collegata (`AnagraficaController::index` con `?q=`)
- ✅ Pagina cliente (`anagrafica/show`) con lista veicoli e storico notifiche
- ✅ Campo `esente_revisione` su Vettura
- ✅ Campo `data_scadenza_impianto` (GPL/metano) su Vettura
- ✅ Docker setup (MySQL 8.0 + phpMyAdmin + Mailpit)
- ✅ Makefile con shortcut comandi
- ✅ MonologBundle configurato
- ✅ Documentazione: `06_BUNDLE_VERSIONS.md`, `07_ARCHITECTURE_DECISIONS.md`
