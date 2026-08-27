FROM php:8.3-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" exif gd mbstring pdo_mysql zip \
    && a2enmod rewrite \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/humogen.ini
COPY --chown=www-data:www-data . /var/www/html/

RUN mkdir -p \
        /var/www/html/admin/backup_files \
        /var/www/html/admin/gedcom_files \
        /var/www/html/media \
        /var/www/html/tmp_files \
    && chown -R www-data:www-data \
        /var/www/html/admin/backup_files \
        /var/www/html/admin/gedcom_files \
        /var/www/html/media \
        /var/www/html/tmp_files

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD php -r '$s = @fsockopen("127.0.0.1", 80); exit($s ? 0 : 1);'
