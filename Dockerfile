# Base image with PHP 8.1 + Apache
FROM php:8.2-apache

# Install required extensions and tools
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git python3 python3-pip default-mysql-client dos2unix \
    && docker-php-ext-install pdo pdo_mysql zip \
    && pip3 install --break-system-packages --no-cache-dir pymupdf

RUN echo "output_buffering = 4096" > /usr/local/etc/php/conf.d/output-buffering.ini

RUN echo "display_errors = Off" > /usr/local/etc/php/conf.d/errors.ini && \
    echo "log_errors = On" >> /usr/local/etc/php/conf.d/errors.ini && \
    echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT" >> /usr/local/etc/php/conf.d/errors.ini

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy custom Apache config
COPY docker/apache/drama.conf /etc/apache2/sites-available/drama.conf
RUN a2ensite drama.conf && a2dissite 000-default.conf

COPY theatremanager.sql /docker-entrypoint-initdb.d/theatremanager.sql
RUN chmod 644 /docker-entrypoint-initdb.d/theatremanager.sql


# Copy app files
COPY . /var/www/html/
RUN find /var/www/html -type f -name "*.php" -exec dos2unix {} \;


# Copy entrypoint
COPY docker-entrypoint.sh /var/www/html/docker-entrypoint.sh
RUN chmod +x /var/www/html/docker-entrypoint.sh


# Expose port 8079
EXPOSE 8079

# Entrypoint is handled via docker-compose entrypoint
