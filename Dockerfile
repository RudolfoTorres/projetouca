FROM php:8.2-apache

# Define a pasta raiz do servidor web como 'public'
COPY . /var/www/html/
WORKDIR /var/www/html/

# Exponha a porta 80
EXPOSE 80