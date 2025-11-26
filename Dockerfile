FROM php:8.2-apache

# Instalar mysqli (para poder conectar PHP con MySQL)
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copiar todos tus archivos del proyecto al contenedor
COPY ./template /var/www/html/

# Exponer el puerto 80 del contenedor para que se vea desde tu navegador
EXPOSE 80

