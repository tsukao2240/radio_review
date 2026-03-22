# Dockerfile for Laravel 11 + React + Vite
FROM php:8.2-fpm-alpine

# 作業ディレクトリを設定
WORKDIR /var/www

# システムの依存関係をインストール
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install zip

# Redis PHPエクステンションをインストール
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del pcre-dev $PHPIZE_DEPS

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# プロジェクト設定ファイルをコピー
# ロックファイルがなくてもビルドが止まらないように * を使用
COPY composer.json composer.lock* ./
COPY package.json package-lock.json* ./

# PHPとNodeの依存関係をインストール
# npm ci ではなく install を使うことで、ロックファイルの不整合によるビルド失敗を恒久的に防ぎます
RUN composer install --no-dev --optimize-autoloader --no-scripts || composer install --no-dev --optimize-autoloader --no-scripts --no-interaction
RUN npm install

# アプリケーションの全ファイルをコピー
COPY . .

# アセットをビルド
# ビルドエラー時に原因を特定しやすくするためログを出力
RUN npm run build || (echo "Vite build failed" && exit 1)

# 本番用のnode_modulesに整理（必要に応じて）
RUN rm -rf node_modules && npm install --production

# --- 権限設定の恒久対策 ---
# 1. まず全ファイルの所有者を www-data に変更
RUN chown -R www-data:www-data /var/www

# 2. 書き込みが必要なディレクトリに適切なパーミッションを付与
# Laravelのセッション・ログ・キャッシュ用
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Entrypointスクリプトをコピーして実行権限付与
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# ポートを公開
EXPOSE 9000

# Entrypointを設定（マイグレーション・DB登録を自動実行）
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
