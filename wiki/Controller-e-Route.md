# Controller e Route

## Mappa completa Route → Controller → Template

### Autenticazione

| Route | URL | Controller | Template |
|---|---|---|---|
| `app_login` | `/login` | SecurityController::login | security/login |
| `app_logout` | `/logout` | SecurityController::logout | — |

---

### Scadenze (home)

| Route | URL | Controller | Template |
|---|---|---|---|
| `app_scadenze_home` | `/` | ScadenzeController::index | scadenze/index |
| `app_scadenze_index` | `/scadenze/{da}/{a}` | ScadenzeController::search | scadenze/index |

La pagina principale mostra revisioni, assicurazioni, bolli e patenti nel periodo selezionato con badge semaforo colorati.

---

### Dashboard

| Route | URL | Controller | Template |
|---|---|---|---|
| `app_dashboard_index` | `/dashboard/` | DashboardController::index | dashboard/index |

---

### Anagrafica

| Route | URL | Controller | Template |
|---|---|---|---|
| `app_anagrafica_index` | `/anagrafica` | AnagraficaController::index | anagrafica/index |
| `app_anagrafica_new` | `/anagrafica/new` | AnagraficaController::new | anagrafica/new |
| `app_anagrafica_show` | `/anagrafica/{id}` | AnagraficaController::show | anagrafica/show |
| `app_anagrafica_edit` | `/anagrafica/{id}/edit` | AnagraficaController::edit | anagrafica/edit |
| `app_anagrafica_delete` | `/anagrafica/{id}` DELETE | AnagraficaController::delete | — |

> `app_anagrafica_index` supporta filtro `?q=` per ricerca globale (da collegare).

---

### Vettura

| Route | URL | Controller | Template |
|---|---|---|---|
| `app_vettura_index` | `/vettura` | VetturaController::index | vettura/index |
| `app_vettura_new` | `/vettura/new` | VetturaController::new | vettura/new |
| `app_vettura_show` | `/vettura/{id}` | VetturaController::show | vettura/show |
| `app_vettura_edit` | `/vettura/{id}/edit` | VetturaController::edit | vettura/edit |
| `app_vettura_delete` | `/vettura/{id}` DELETE | VetturaController::delete | — |

---

### Assicurazione

| Route | URL | Controller | Template |
|---|---|---|---|
| `app_assicurazione_index` | `/assicurazione` | AssicurazioneController::index | assicurazione/index |
| `app_assicurazione_new` | `/assicurazione/new` | AssicurazioneController::new | assicurazione/new |
| `app_assicurazione_show` | `/assicurazione/{id}` | AssicurazioneController::show | assicurazione/show |
| `app_assicurazione_edit` | `/assicurazione/{id}/edit` | AssicurazioneController::edit | assicurazione/edit |
| `app_assicurazione_delete` | `/assicurazione/{id}` DELETE | AssicurazioneController::delete | — |

---

### Bollo

| Route | URL | Controller | Template |
|---|---|---|---|
| `app_bollo_index` | `/bollo` | BolloController::index | bollo/index |
| `app_bollo_new` | `/bollo/new` | BolloController::new | bollo/new |
| `app_bollo_show` | `/bollo/{id}` | BolloController::show | bollo/show |
| `app_bollo_edit` | `/bollo/{id}/edit` | BolloController::edit | bollo/edit |
| `app_bollo_delete` | `/bollo/{id}` DELETE | BolloController::delete | — |

---

### Patente

| Route | URL | Controller | Template |
|---|---|---|---|
| `app_patente_index` | `/patente` | PatenteController::index | patente/index |
| `app_patente_new` | `/patente/new` | PatenteController::new | patente/new |
| `app_patente_show` | `/patente/{id}` | PatenteController::show | patente/show |
| `app_patente_edit` | `/patente/{id}/edit` | PatenteController::edit | patente/edit |
| `app_patente_delete` | `/patente/{id}` DELETE | PatenteController::delete | — |

---

### Notifiche

| Route | URL | Controller | Template |
|---|---|---|---|
| `app_notifica_index` | `/notifica/` | NotificaController::index | notifica/index |
| `app_notifica_new` | `/notifica/new` | NotificaController::new | notifica/new |
| `app_notifica_new_per_cliente` | `/notifica/new/{id}` | NotificaController::newPerCliente | notifica/new |
| `app_notifica_show` | `/notifica/{id}` | NotificaController::show | notifica/show |
| `app_notifica_edit` | `/notifica/{id}/edit` | NotificaController::edit | notifica/edit |
| `app_notifica_delete` | `/notifica/{id}` DELETE | NotificaController::delete | — |

> La route `app_notifica_new_per_cliente` pre-seleziona il cliente — usata dal pulsante 🔔 nella pagina scadenze.

---

## Access Control

```yaml
# config/packages/security.yaml
access_control:
    - { path: ^/login, roles: PUBLIC_ACCESS }
    - { path: ^/, roles: ROLE_ADMIN }
```

Tutto il sito richiede almeno `ROLE_ADMIN`. Solo `/login` è pubblico.
