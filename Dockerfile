# Dockerfile (raiz do repo)
FROM php:5.6-apache

# Opcional: mod_rewrite
RUN a2enmod rewrite

# Copia a app
COPY . /var/www/html

# Entrypoint que muda a porta do Apache para $PORT
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
