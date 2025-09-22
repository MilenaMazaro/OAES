#!/usr/bin/env bash
set -e

# Porta definida pelo Render. Se rodar localmente, usa 8080.
PORT="${PORT:-8080}"

# Faz o Apache ouvir na porta informada
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf || true
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf || true
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf  || true

# (Opcional) ativar mod_rewrite
a2enmod rewrite >/dev/null 2>&1 || true

# Sobe o Apache em foreground (modo correto para containers)
exec apache2-foreground
