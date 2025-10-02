# Usar PHP 8.1 con Apache
FROM php:8.1-apache

# Instalar dependencias del sistema para PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

# Habilitar módulos necesarios de Apache
RUN a2enmod rewrite headers expires

# Configurar el DocumentRoot de Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html

# Actualizar la configuración de Apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configurar Apache para servir archivos PHP correctamente
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    DirectoryIndex index.php index.html\n\
</Directory>\n\
<FilesMatch \.php$>\n\
    SetHandler application/x-httpd-php\n\
</FilesMatch>' > /etc/apache2/conf-available/docker-php.conf \
    && a2enconf docker-php

# Verificar que PHP esté configurado correctamente
RUN php -v && apache2ctl -M | grep php

# Configurar permisos para Apache
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Copiar archivos de la aplicación
COPY . /var/www/html/

# Configurar permisos después de copiar
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Exponer el puerto 80
EXPOSE 80

# Comando para iniciar Apache
CMD ["apache2-foreground"]
