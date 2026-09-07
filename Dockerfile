# =============================================================================
# daba - Dockerfile pour Backend PHP
# Optimisé pour la production et Kubernetes
# =============================================================================

FROM php:8.2-fpm-alpine

# Métadonnées
LABEL maintainer="Daba <contact@daba.com>"
LABEL version="1.0.0"
LABEL description="Daba Backend API"

# Installer les dépendances système
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    oniguruma-dev \
    postgresql-dev \
    icu-dev \
    $PHPIZE_DEPS

# Installer les extensions PHP requises
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        zip \
        mbstring \
        exif \
        pcntl \
        bcmath \
        intl \
        opcache

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer l'utilisateur de l'application
RUN addgroup -g 1000 bloom && \
    adduser -D -u 1000 -G bloom -h /var/www bloom

# Définir le répertoire de travail
WORKDIR /var/www

# Copier les fichiers de l'application
COPY --chown=bloom:bloom . /var/www

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configurer PHP pour la production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "opcache.enable=1" >> "$PHP_INI_DIR/php.ini" \
    && echo "opcache.memory_consumption=128" >> "$PHP_INI_DIR/php.ini" \
    && echo "opcache.interned_strings_buffer=8" >> "$PHP_INI_DIR/php.ini" \
    && echo "opcache.max_accelerated_files=10000" >> "$PHP_INI_DIR/php.ini" \
    && echo "expose_php=Off" >> "$PHP_INI_DIR/php.ini" \
    && echo "display_errors=Off" >> "$PHP_INI_DIR/php.ini" \
    && echo "log_errors=On" >> "$PHP_INI_DIR/php.ini" \
    && echo "error_log=/var/log/php_errors.log" >> "$PHP_INI_DIR/php.ini"

# Créer les répertoires nécessaires avec les bons permissions
RUN mkdir -p /var/www/backend/logs \
    /var/www/bloom_rate_limit \
    /var/log/php \
    && chown -R bloom:bloom /var/www \
    && chmod -R 755 /var/www

# Configurer PHP-FPM
RUN sed -i 's/user = nobody/user = bloom/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/group = nobody/group = bloom/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;listen.owner = nobody/listen.owner = bloom/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;listen.group = nobody/listen.group = bloom/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;listen.mode = 0660/listen.mode = 0660/g' /usr/local/etc/php-fpm.d/www.conf

# Exposer le port HTTP
EXPOSE 8080 10000 80

# Health check HTTP
HEALTHCHECK --interval=30s --timeout=10s --start-period=20s --retries=3 \
    CMD php -r "file_get_contents('http://localhost:' . (getenv('PORT') ?: '8080') . '/health.php') !== false || exit(1);"

# Démarrer le serveur Web PHP avec le routeur centralisé
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} backend/index.php"]

