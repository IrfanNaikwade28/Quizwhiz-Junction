#!/usr/bin/env sh
set -e

# If Render provides a PORT, reconfigure Apache to listen on it
if [ -n "$PORT" ]; then
  echo "Updating Apache to listen on port $PORT"
  sed -i "s/^Listen 80$/Listen $PORT/" /etc/apache2/ports.conf || true
  # Update VirtualHost declaration if present
  if grep -q "<VirtualHost \*:80>" /etc/apache2/sites-available/000-default.conf; then
    sed -i "s#<VirtualHost \*:80>#<VirtualHost *:$PORT>#" /etc/apache2/sites-available/000-default.conf || true
  fi
  export APACHE_RUN_PORT="$PORT"
fi

# Install dependencies via composer if composer.json exists
if [ -f "/var/www/html/composer.json" ]; then
  echo "Installing composer dependencies..."
  composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader || true
fi

# If a SQL dump exists and a database is configured, try to import once
if [ -n "$DB_HOST" ] && [ -n "$DB_USER" ] && [ -n "$DB_NAME" ] && [ -f "/var/www/html/exam.sql" ]; then
  echo "Attempting one-time database import from exam.sql (idempotent)"
  # Create a marker to avoid re-import on restarts
  if [ ! -f "/var/www/html/.db_imported" ]; then
    # Wait for DB to be reachable
    for i in 1 2 3 4 5 6 7 8 9 10; do
      if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1" >/dev/null 2>&1; then
        break
      fi
      echo "Waiting for database... ($i)"; sleep 2
    done
    # Create DB if missing and import
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`" || true
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "/var/www/html/exam.sql" || true
    touch "/var/www/html/.db_imported"
  fi
fi

exec "$@"
