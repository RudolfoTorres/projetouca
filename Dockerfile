FROM php:8.2-apache

# Copia o código da sua aplicação para o diretório padrão do Apache
COPY . /var/www/html/

# Mude o proprietário e as permissões do arquivo do banco de dados
# para que o usuário do Apache (www-data) possa escrever nele.
RUN chown -R www-data:www-data /var/www/html/

# Altera a configuração do Apache para usar a pasta 'public'
RUN sed -i -e 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/html\/public/g' /etc/apache2/sites-available/000-default.conf

# Habilita o módulo de reescrita do Apache
RUN a2enmod rewrite

# Reinicia o Apache para que as novas configurações sejam aplicadas
CMD ["apache2ctl", "-D", "FOREGROUND"]