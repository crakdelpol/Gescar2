# ─────────────────────────────────────────────────────────────────────────────
# Makefile – Gescar2 shortcuts
# Uso: make <comando>   (richiede GNU Make installato)
# Su Windows: installare Make via Chocolatey → choco install make
# ─────────────────────────────────────────────────────────────────────────────

.PHONY: help up down restart logs db-shell pma install env migrate fixtures \
        cache clear test sf

## ── Aiuto ────────────────────────────────────────────────────────────────────
help: ## Mostra questo elenco
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
	  | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'

## ── Docker ───────────────────────────────────────────────────────────────────
up: ## Avvia i container (MySQL + phpMyAdmin + Mailpit)
	docker compose up -d

down: ## Ferma e rimuove i container
	docker compose down

restart: ## Restart completo
	docker compose down && docker compose up -d

logs: ## Mostra i log in tempo reale
	docker compose logs -f

db-shell: ## Apre la shell MySQL nel container
	docker compose exec db mysql -u gescar_user -pgescar_pass gescar

db-root: ## Apre la shell MySQL come root
	docker compose exec db mysql -u root -proot gescar

## ── Setup progetto ───────────────────────────────────────────────────────────
install: ## Installa dipendenze PHP
	composer install

env: ## Crea .env.local dal template (se non esiste)
	@test -f .env.local || (cp .env.local.dist .env.local && echo ".env.local creato — personalizzalo!")

## ── Database Doctrine ────────────────────────────────────────────────────────
db-create: ## Crea il database
	php bin/console doctrine:database:create --if-not-exists

migrate: ## Esegue tutte le migration pendenti
	php bin/console doctrine:migrations:migrate --no-interaction

migrate-status: ## Mostra stato delle migration
	php bin/console doctrine:migrations:status

migrate-diff: ## Genera una nuova migration da differenze Entity/DB
	php bin/console doctrine:migrations:diff

fixtures: ## Carica i dati di test (⚠️ svuota il DB)
	php bin/console doctrine:fixtures:load --no-interaction

db-reset: ## Reset completo: drop → create → migrate → fixtures
	php bin/console doctrine:database:drop --force --if-exists
	php bin/console doctrine:database:create
	php bin/console doctrine:migrations:migrate --no-interaction
	php bin/console doctrine:fixtures:load --no-interaction

## ── Symfony ──────────────────────────────────────────────────────────────────
cache: ## Pulisce la cache Symfony
	php bin/console cache:clear

routes: ## Mostra tutte le route registrate
	php bin/console debug:router

entities: ## Mostra lo stato delle entity Doctrine
	php bin/console doctrine:schema:validate

server: ## Avvia il server di sviluppo PHP built-in
	php -S localhost:8000 -t public/

## ── Qualità codice ───────────────────────────────────────────────────────────
test: ## Esegue i test PHPUnit
	php bin/phpunit

## ── Apertura browser (Windows) ───────────────────────────────────────────────
open-app: ## Apre l'app nel browser
	start http://localhost:8000

open-pma: ## Apre phpMyAdmin nel browser
	start http://localhost:8080

open-mail: ## Apre Mailpit (email di test) nel browser
	start http://localhost:8025
