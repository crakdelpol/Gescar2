# Architettura

## Stack Tecnologico

| Layer | Tecnologia | Versione | Note |
|---|---|---|---|
| PHP | PHP | 8.2+ | Target 8.4 |
| Framework | Symfony | 7.3 | Target upgrade 7.4 LTS |
| Database | MySQL | 8.0 (locale) / 5.7 (prod) | Target 8.4 LTS |
| ORM | Doctrine ORM | 3.x | Attributi PHP 8 |
| Template | Twig | 3.x | |
| CSS/JS | Bootstrap | 5.3 CDN | Zero jQuery |
| Icone | Bootstrap Icons | 1.11 | |
| Auth | Symfony Security | nativo | Sostituisce FOSUserBundle |
| Log | MonologBundle | ^4.0 | |

---

## Struttura Cartelle

```
Gescar2/
├── config/
│   └── packages/
│       ├── doctrine.yaml          ← MySQL, naming underscore
│       ├── security.yaml          ← login form, bcrypt cost 13
│       ├── monolog.yaml           ← logging su file
│       └── ...
├── docs/                          ← documentazione di progetto
├── migrations/                    ← Doctrine migrations (000→005)
├── public/                        ← document root
│   ├── css/custom.css
│   └── img/
├── src/
│   ├── Controller/
│   ├── Entity/
│   ├── Form/
│   ├── Repository/
│   └── Service/
├── templates/
├── wiki/                          ← pagine wiki (questa cartella)
├── docker-compose.yml
├── Makefile
├── .env.local.dist
└── CONTEXT.md
```

---

## Entity Map

### Anagrafica → `anagrafica`

```
id, nome, cognome, tipo_cliente (privato|azienda),
luogo_nascita, codice_fiscale, partita_iva,
residenza, sede_legale, email, telefono,
codice_destinatario, note
```

Relazioni:
- `OneToMany` → Vettura (via `intestatario_id`)
- `OneToOne` → Patente (via `intestatario_id`)

---

### Vettura → `vettura`

```
id, targa, numero_telaio,
tipo_vettura (autovettura|autocarro|motoveicolo|rimorchio|ciclomotore),
carburante (benzina|gasolio|gpl|metano|elettrico),
marca, modello,
data_ultima_revisione, data_scadenza_revisione,
data_scadenza_impianto, note,
intestatario_id FK → anagrafica(id)
```

---

### Assicurazione → `assicurazione`

```
id, vettura_id FK → vettura(id) [OneToOne],
data_scadenza_assicurazione, data_inizio,
compagnia, numero_polizza, attiva, note
```

> In Italia una vettura ha una sola RC Auto attiva — relazione OneToOne.

---

### Bollo → `bollo`

```
id, vettura_id FK → vettura(id) [OneToOne],
data_scadenza_bollo, importo, super_bollo,
pagato, data_pagamento, attiva, note
```

> In Italia una vettura ha un solo bollo annuale — relazione OneToOne.

---

### Patente → `patente`

```
id, intestatario_id FK → anagrafica(id) [OneToOne],
numero_patente, categoria_patente (JSON array),
data_scadenza_patente, note
```

---

### User → `user`

```
id, email UNIQUE, roles (JSON), password (bcrypt),
nome, cognome, is_active
```

Ruoli: `ROLE_ADMIN` (default), `ROLE_SUPER_ADMIN` (gerarchia).

---

### Notifica → `notifica`

```
id,
anagrafica_id FK → anagrafica(id) ON DELETE CASCADE,
vettura_id FK → vettura(id) ON DELETE SET NULL,
utente_id FK → user(id) ON DELETE SET NULL,
tipo_scadenza (revisione|assicurazione|bollo|patente),
canale (telefono|sms|email|whatsapp),
data_invio DATETIME,
esito (inviata|non_risponde|rinnovato|non_interessato),
note
```

---

## Diagramma Relazioni

```
anagrafica ──< vettura ──── assicurazione
     │              └────── bollo
     └── patente

notifica >── anagrafica
notifica >── vettura
notifica >── user
```
