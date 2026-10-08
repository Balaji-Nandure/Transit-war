FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    openssl \
    ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY ./public /var/www/html/
COPY ./app /var/www/app/
COPY ./config /var/www/config/
COPY ./scripts /var/www/scripts
COPY ./Phase2.csv /var/www/Phase2.csv

RUN mkdir -p /var/www/storage/uploads && mkdir -p /var/www/storage/logs

COPY ./sql /docker-entrypoint-initdb.d/

RUN chown -R www-data:www-data /var/www/html /var/www/app /var/www/config /var/www/storage

RUN mkdir -p /var/www/sessions && chmod 777 /var/www/sessions
RUN echo 'session.save_path = "/var/www/sessions"' > /usr/local/etc/php/conf.d/session-save-path.ini

COPY ./docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Enable SSL + rewrite + headers
RUN a2enmod ssl headers rewrite

# Generate self-signed certificate
RUN openssl req -x509 -nodes -days 365 \
    -newkey rsa:2048 \
    -keyout /etc/ssl/private/apache-selfsigned.key \
    -out /etc/ssl/certs/apache-selfsigned.crt \
    -subj "/C=IN/ST=Telangana/L=Hyderabad/O=IITH/CN=localhost"

# Add Apache site config
COPY docker/ssl.conf /etc/apache2/sites-available/ssl.conf
RUN a2dissite 000-default.conf
RUN a2ensite ssl.conf

EXPOSE 80 443

ENTRYPOINT ["/entrypoint.sh"]
