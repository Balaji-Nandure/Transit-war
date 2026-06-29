#!/bin/bash
set -e

echo "Waiting for MySQL..."

#until mysql --ssl=0 -h db -u nspuser -pnsppass -e "SELECT 1" transactiwar; do
until mysql --ssl=0 -h db -u FinalSemester -p"Chor-Chor@123" -e "SELECT 1" transactiwar; do
  sleep 2
done

echo "MySQL ready."

# Run account creation script 
if [ -f /var/www/scripts/create_test_users.php ]; then
  php /var/www/scripts/create_test_users.php || true
fi

echo "Starting Apache..."
exec apache2-foreground
