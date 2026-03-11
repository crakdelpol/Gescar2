# Roadmap

Vedi anche `docs/04_ROADMAP.md` nel repository per la versione completa.

---

## 🔴 Alta Priorità

### Ricerca Globale Navbar
- Collegare il campo di ricerca in navbar → `AnagraficaController::index` con filtro `?q=`
- `AnagraficaRepository::searchGlobale()` è già implementato (cerca per cognome/nome o targa)
- Manca solo il collegamento nel controller

### Pagina Cliente (show)
- Completare `templates/anagrafica/show.html.twig`
- Mostrare lista veicoli intestati al cliente
- Mostrare storico notifiche del cliente
- Mostrare scadenze attive (revisioni, assicurazioni, bolli, patente)

---

## 🟡 Media Priorità

### Campo telefono2
- Aggiungere `telefono2` all'entity `Anagrafica`
- Le business rules prevedono due numeri di telefono per cliente
- Richiede migration + aggiornamento form + template

### Paginazione Liste
- Le liste (`anagrafica/index`, `vettura/index`, ecc.) non hanno paginazione
- Valutare KnpPaginatorBundle o paginazione manuale con QueryBuilder

### DataFixtures Aggiornate
- Aggiungere `User` e `Notifica` alle fixtures
- Attualmente le fixtures non creano utenti (l'utente admin va inserito manualmente)

### VetturaType — Choice Label
- `VetturaType` mostra solo `cognome` nel choice label per intestatario
- Migliorare con `cognome + ' ' + nome`

---

## 🟢 Bassa Priorità / Future

### Upgrade MySQL 8.0 → 8.4 LTS
- Il DB di produzione è su MySQL 5.7
- L'ambiente Docker è su MySQL 8.0
- Target finale: MySQL 8.4 LTS

### Upgrade Symfony 7.3 → 7.4 LTS
- Attendere release stabile 7.4
- Verificare deprecazioni attuali nel log

### Comando Console Crea Admin
- Creare `src/Command/CreateAdminCommand.php`
- Permettere `php bin/console app:create-admin email password`
- Evita l'INSERT SQL manuale

### Export Scadenze PDF/Excel
- Export della lista scadenze del mese in PDF o Excel
- Utile per stampa e archiviazione

### Invio Email/SMS Automatico
- Integrazione Mailer Symfony per invio email automatiche
- Invio SMS tramite provider (es. Twilio, Vonage)
- Mailpit già configurato in Docker per test locali

### Dashboard Avanzata
- Grafici scadenze per mese (Chart.js o Symfony UX)
- Contatori per stato semaforo
- Riepilogo notifiche della settimana

---

## ✅ Completato

- ✅ Login con Symfony Security nativo (sostituisce FOSUserBundle)
- ✅ Entity User e Notifica
- ✅ Bootstrap 4 → 5.3 CDN (zero jQuery)
- ✅ Pagina scadenze con tab e badge semaforo
- ✅ CRUD completo: Anagrafica, Vettura, Assicurazione, Bollo, Patente, Notifica
- ✅ ScadenzaService e NotificaService
- ✅ Docker setup (MySQL 8.0 + phpMyAdmin + Mailpit)
- ✅ Makefile con shortcut comandi
- ✅ DataFixtures con dati realistici e date variegate
- ✅ MonologBundle configurato
