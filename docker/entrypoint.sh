#!/bin/bash
set -e

echo "========================================="
echo "Radio Review App - Starting..."
echo "========================================="

# データベース接続待機
echo "Waiting for database connection..."
max_attempts=30
attempt=0

while ! php artisan db:show 2>/dev/null; do
    attempt=$((attempt + 1))
    if [ $attempt -ge $max_attempts ]; then
        echo "ERROR: Database connection timeout"
        exit 1
    fi
    echo "Attempt $attempt/$max_attempts - Waiting for database..."
    sleep 2
done

echo "✓ Database connected successfully"

# マイグレーション実行
echo ""
echo "Running database migrations..."
php artisan migrate --force
echo "✓ Migrations completed"

# 番組データ登録（初回のみ）
echo ""
echo "Checking program data..."
program_count=$(php artisan tinker --execute="echo \App\Models\RadioProgram::count();" 2>/dev/null || echo "0")

if [ "$program_count" -eq "0" ]; then
    echo "Importing initial program data..."
    php artisan radio:import-programs --force
    echo "✓ Program data imported"
else
    echo "✓ Program data already exists ($program_count programs)"
fi

echo ""
echo "========================================="
echo "Radio Review App - Ready!"
echo "========================================="
echo ""

# PHP-FPM起動
exec php-fpm
