FROM php:5.6-apache
RUN a2enmod rewrite
WORKDIR /var/www/html
COPY . /var/www/html
# Permissões básicas
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
