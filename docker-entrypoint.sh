#!/bin/bash
set -e

# Always overwrite db_config.php
DB_CONFIG_FILE=/var/www/html/backend/config/db_config.php
cat > "$DB_CONFIG_FILE" <<EOL
<?php
return array (
  'host' => '${DB_HOST}',
  'db' => '${DB_NAME}',
  'user' => '${DB_USER}',
  'pass' => '${DB_PASS}',
  'charset' => '${DB_CHARSET}',
);
EOL
echo "Written db_config.php from environment variables"

# Always overwrite Discord bot .env
DISCORD_ENV_FILE=/var/www/html/bot/discord-rehearsal-bot/.env
cat > "$DISCORD_ENV_FILE" <<EOL
DISCORD_TOKEN=${DISCORD_TOKEN}
GUILD_ID=${GUILD_ID}
SCHEDULE_CHANNEL_ID=${SCHEDULE_CHANNEL_ID}
SCHEDULE_PINGS=${SCHEDULE_PINGS}
CHANGELOG_PINGS=${CHANGELOG_PINGS}
CHANGELOG_CHANNEL_ID=${CHANGELOG_CHANNEL_ID}
EOL
echo "Written Discord .env from environment variables"

# Ensure log and upload directories are writable
chown -R www-data:www-data /var/www/html/uploads /var/www/html/logs
chmod -R 775 /var/www/html/uploads /var/www/html/logs

echo "Giving MySQL a few seconds to finish setup..."
sleep 10

# Wait for DB to be ready using PHP PDO
echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
MAX_ATTEMPTS=30
ATTEMPT=1

until php -r "
try {
    \$pdo = new PDO('mysql:host=${DB_HOST};port=${DB_PORT:-3306}', '${DB_USER}', '${DB_PASS}', [
        PDO::ATTR_TIMEOUT => 2,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
  if [ $ATTEMPT -ge $MAX_ATTEMPTS ]; then
    echo "❌ MySQL did not become ready after $MAX_ATTEMPTS attempts."
    exit 1
  fi
  echo "  MySQL not ready (attempt $ATTEMPT/$MAX_ATTEMPTS)..."
  ATTEMPT=$((ATTEMPT+1))
  sleep 2
done

echo "✅ MySQL is ready."

echo "Ensuring admin account exists..."
php -r "
\$pdo = new PDO('mysql:host=${DB_HOST};dbname=${DB_NAME}', '${DB_USER}', '${DB_PASS}');
\$exists = \$pdo->query(\"SELECT COUNT(*) FROM users WHERE username='Admin'\")->fetchColumn();
if (!\$exists) {
    \$hash = password_hash(getenv('ADMIN_PASSWORD'), PASSWORD_BCRYPT);
    \$stmt = \$pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)');
    \$stmt->execute(['Admin', \$hash, 'admin']);
    echo \"Admin account created\n\";
} else {
    echo \"Admin already exists\n\";
}
"

# Start Apache and PM2 bot
pm2 start /var/www/html/bot/discord-rehearsal-bot/bot.js --no-daemon &
exec apache2-foreground