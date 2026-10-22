#!/usr/bin/env sh
set -eu
[ -f .env ] || { echo 'Missing .env'; exit 1; }
# shellcheck disable=SC1091
set -a; . ./.env; set +a
case "$MYSQL_EXPORTER_PASSWORD" in *"'"*|*"\"*|*";"*) echo 'Use alphanumeric/symbol password without quotes/backslash/semicolon for exporter'; exit 1;; esac
docker compose exec -T -e MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql mysql -uroot <<SQL
CREATE USER IF NOT EXISTS 'exporter'@'%' IDENTIFIED BY '${MYSQL_EXPORTER_PASSWORD}';
ALTER USER 'exporter'@'%' IDENTIFIED BY '${MYSQL_EXPORTER_PASSWORD}';
GRANT PROCESS, REPLICATION CLIENT, SELECT ON *.* TO 'exporter'@'%';
FLUSH PRIVILEGES;
SQL
echo 'Exporter account ready. Run docker compose restart mysql-exporter.'
