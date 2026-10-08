#!/bin/bash
set -e

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-FinalSemester}"
DB_PASS="${DB_PASS:-Chor-Chor@123}"
DB_NAME="${DB_NAME:-transactiwar}"
DB_MAX_RETRIES="${DB_MAX_RETRIES:-12}"

echo "Checking MySQL connection on ${DB_HOST}:${DB_PORT} (DB: ${DB_NAME})..."

RETRY_COUNT=0
DB_READY=0

while [ $RETRY_COUNT -lt $DB_MAX_RETRIES ]; do
  if mysql --ssl-mode=DISABLED -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1" "$DB_NAME" >/dev/null 2>&1 || \
     mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1" "$DB_NAME" >/dev/null 2>&1; then
    DB_READY=1
    echo "MySQL connection successful!"
    break
  fi
  RETRY_COUNT=$((RETRY_COUNT + 1))
  echo "MySQL not ready yet (attempt $RETRY_COUNT of $DB_MAX_RETRIES)..."
  sleep 2
done

if [ $DB_READY -eq 1 ]; then
  # Initialize schema if tables don't exist yet
  TABLE_COUNT=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}';" -s -N 2>/dev/null || echo "0")
  if [ "$TABLE_COUNT" = "0" ] && [ -f /docker-entrypoint-initdb.d/schema.sql ]; then
    echo "Empty database detected. Initializing tables from schema.sql..."
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /docker-entrypoint-initdb.d/schema.sql || true
    echo "Schema initialization completed."
  fi

  # Run account creation script
  if [ -f /var/www/scripts/create_test_users.php ]; then
    echo "Seeding test users..."
    php /var/www/scripts/create_test_users.php || true
  fi
else
  echo "WARNING: Could not connect to MySQL at ${DB_HOST}:${DB_PORT}."
  echo "Starting Apache anyway so the service remains up and reachable."
fi

echo "Starting Apache..."
exec apache2-foreground
