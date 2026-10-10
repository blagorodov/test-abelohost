.PHONY: up down lint

up:
	docker compose up -d
	docker compose exec -w /var/www/html -e COMPOSER_ALLOW_SUPERUSER=1 php composer install
	sass --no-source-map scss/main.scss public/css/main.css

down:
	docker compose down

lint:
	docker compose exec -w /var/www/html php vendor/bin/php-cs-fixer fix
