
FROM php:5.6-apache

COPY . /var/www/html/

RUN a2enmod rewrite headers

ENV TZ=America/Sao_Paulo

EXPOSE 80

CMD ["apache2-foreground"]
