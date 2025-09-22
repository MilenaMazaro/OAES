#!/usr/bin/env bash
set -e

PORT_TO_USE="${PORT:-8080}"

# Troca a porta 80 -> $PORT em ports.conf
if grep -qE '^Listen 80$' /etc/apache2/ports.conf ; then
  sed -ri "s/^Listen 80$/Listen ${PORT_TO_USE}/" /etc/apache2/ports.conf
fi

# Troca a porta no VirtualHost default
if grep -q "<VirtualHost *:80>" /etc/apache2/sites-available/000-default.conf ; then
  sed -ri "s#<VirtualHost \*:80>#<VirtualHost *:${PORT_TO_USE}>#" /etc/apache2/sites-available/000-default.conf
fi

# (Opcional) permitir .htaccess
# sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Sobe o Apache em foreground
exec apache2-foreground
