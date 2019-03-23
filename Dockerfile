FROM php:7.3-apache

RUN apt-get update && apt-get install -y git unzip zip libpq-dev ghostscript
RUN apt-get install -y libmagickwand-dev --no-install-recommends
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql
RUN yes '' | pecl install imagick && docker-php-ext-enable imagick

RUN curl -SL https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
	&& chmod +x /usr/bin/composer

COPY 000-default.conf /etc/apache2/sites-available/
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT /srv/http
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY src/ /srv/http/paperjam
RUN (cd /srv/http/paperjam; composer install)
