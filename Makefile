.PHONY: up down remove lint fix css seed smoke

up:
	@test -f .env || { echo "Скопируйте .env.example в .env"; exit 1; }
	docker compose up -d --wait
	docker compose exec -w /var/www/html -e COMPOSER_ALLOW_SUPERUSER=1 php composer install

down:
	docker compose down

remove:
	docker compose down -v

css:
	docker build -t abelohost-sass docker/sass
	docker run --rm -u "$$(id -u):$$(id -g)" -v "$(CURDIR):/work" -w /work abelohost-sass --no-source-map scss/main.scss public/css/main.css

lint:
	docker compose exec -w /var/www/html php vendor/bin/php-cs-fixer fix --dry-run --diff

fix:
	docker compose exec -w /var/www/html php vendor/bin/php-cs-fixer fix

seed:
	docker compose exec -w /var/www/html php php bin/seed.php

smoke:
	@curl -sf -o /dev/null -w '%{http_code}' http://localhost:8080/ | grep -qx 200
	@curl -sf -o /dev/null -w '%{http_code}' http://localhost:8080/category/1 | grep -qx 200
	@curl -sf -o /dev/null -w '%{http_code}' 'http://localhost:8080/category/1?sort=views&page=2' | grep -qx 200
	@curl -sf -o /dev/null -w '%{http_code}' http://localhost:8080/post/1 | grep -qx 200
	@curl -s -o /dev/null -w '%{http_code}' 'http://localhost:8080/category/1?page=99' | grep -qx 404
	@curl -s -o /dev/null -w '%{http_code}' http://localhost:8080/category/0 | grep -qx 404
	@curl -s -o /dev/null -w '%{http_code}' http://localhost:8080/post/999999 | grep -qx 404
