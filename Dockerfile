FROM php:5.6-apache

# (Opcional) habilite o rewrite se usar .htaccess
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . .

# Entrypoint que ajusta a porta e inicia o Apache
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

CMD ["docker-entrypoint.sh"]
