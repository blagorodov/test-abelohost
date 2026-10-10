.PHONY: up down remove lint

up:
	@test -f .env || { echo "Скопируйте .env.example в .env"; exit 1; }
	docker compose up -d --wait
	sh bin/init-db.sh
	docker compose exec -w /var/www/html -e COMPOSER_ALLOW_SUPERUSER=1 php composer install
	docker build -t abelohost-sass docker/sass
	docker run --rm -u "$(id -u):$(id -g)" -v "$(CURDIR):/work" -w /work abelohost-sass --no-source-map scss/main.scss public/css/main.css

down:
	docker compose down

remove:
	docker compose down -v

lint:
	docker compose exec -w /var/www/html php vendor/bin/php-cs-fixer fix
