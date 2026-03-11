# Convenzioni di Sviluppo

## Naming

| Elemento | Convenzione | Esempio |
|---|---|---|
| Entity PHP | `PascalCase` | `Anagrafica`, `Vettura` |
| Tabella DB | `snake_case` | `anagrafica`, `vettura` |
| Colonna DB | `snake_case` | `data_scadenza_revisione` |
| Controller | `PascalCase` + `Controller` | `AnagraficaController` |
| Route name | `app_{entità}_{azione}` | `app_vettura_index` |
| Service | `PascalCase` + `Service` | `ScadenzaService` |
| Repository | `PascalCase` + `Repository` | `VetturaRepository` |
| Form Type | `PascalCase` + `Type` | `VetturaType` |
| Template | `snake_case` directory | `vettura/index.html.twig` |

---

## Entity e ORM

- Usare **attributi PHP 8** `#[ORM\...]` — vietate le annotazioni `@ORM\` legacy
- Mapping esplicito dei nomi colonna quando divergono da camelCase:

```php
#[ORM\Column(name: "tipo_cliente", length: 20, nullable: true)]
private ?string $tipo = null;
```

- Naming strategy Doctrine: `underscore_number_aware` (configurato in `doctrine.yaml`)
- Le FK si dichiarano con `#[ORM\JoinColumn]` esplicito

---

## Controller

Il controller fa **solo orchestrazione**:
- Riceve la request
- Chiama Repository o Service
- Passa i dati al template
- Gestisce redirect e flash messages

❌ Niente query SQL/DQL inline nei controller
❌ Niente logica di business nei controller

---

## Repository

- Tutte le query DQL/QueryBuilder stanno nel Repository
- Mai SQL raw (usare QueryBuilder o DQL)
- Metodi custom con nomi descrittivi:

```php
// ✅ Corretto
public function findRevisioniInRange(\DateTimeInterface $da, \DateTimeInterface $a): array

// ❌ Evitare
public function getStuff(): array
```

---

## Service

- Logica di business nei Service (es. calcolo stati semaforo)
- I Service vengono iniettati nei Controller via constructor injection
- Possono essere passati ai template come variabile Twig quando necessario

---

## Frontend

- **Bootstrap 5.3** da CDN — niente file locali Bootstrap
- **Bootstrap Icons 1.11** da CDN — classe `bi bi-nome`
- **Zero jQuery** — usare `data-bs-*` per componenti Bootstrap nativi
- **Zero Open Iconic** (rimosso)
- CSS custom solo in `public/css/custom.css`

---

## Template Twig

- Base layout: `templates/base.html.twig` (sidebar + navbar + flash messages)
- Ogni sezione estende `base.html.twig` con `{% extends 'base.html.twig' %}`
- Usare macro Twig per logica ripetuta (es. `badge_scadenza`)
- Flash messages: `success` → verde, `error`/`danger` → rosso

---

## Sicurezza

- **CSRF** abilitato su tutti i form DELETE e nel form login
- Hash password: **bcrypt cost 13**
- `enable_csrf: true` nel form_login di `security.yaml`
- Token CSRF nei form DELETE: `{{ csrf_token('delete' ~ entity.id) }}`

---

## Git

- Branch principale: `master`
- Commit message in inglese con prefisso convenzionale: `feat:`, `fix:`, `docs:`, `refactor:`
- Non committare mai: `.env.local`, dump SQL, `var/`, `vendor/`
