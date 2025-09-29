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

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# プロジェクトファイルをコピー
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# package.jsonとnpmの依存関係をコピー・インストール
COPY package.json package-lock.json ./
RUN npm ci

# アプリケーションのファイルをコピー
COPY . .

# アセットをビルド
RUN npm run build

# 本番用のnode_modulesを再インストール（サイズ削減のため）
RUN rm -rf node_modules && npm ci --only=production

# 権限設定
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# 実行ユーザーを変更
USER www-data

# ポートを公開
EXPOSE 9000

# PHP-FPMを起動
CMD ["php-fpm"]