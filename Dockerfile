FROM php:8.2-apache

# Set Apache root to /dist
ENV APACHE_DOCUMENT_ROOT /var/www/html/dist

# Update Apache config
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Copy project
COPY . /var/www/html/

# Enable rewrite
RUN a2enmod rewrite