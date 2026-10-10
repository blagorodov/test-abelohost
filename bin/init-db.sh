#!/bin/sh
set -eu
cd "$(dirname "$0")/.."
set -a
. ./.env
set +a

docker compose exec -T mysql mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e \
  "CREATE DATABASE IF NOT EXISTS \`$MYSQL_DATABASE\`; CREATE USER IF NOT EXISTS '$MYSQL_USER'@'localhost' IDENTIFIED BY '$MYSQL_PASSWORD'; GRANT CREATE ON \`$MYSQL_DATABASE\`.* TO '$MYSQL_USER'@'localhost'"

count=$(docker compose exec -T mysql mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -N -s -e \
  "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${MYSQL_DATABASE}' AND table_name IN ('categories','posts','post_category')")
if [ "$count" -lt 3 ]; then
  docker compose exec -T mysql mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" < sql/schema.sql
fi
