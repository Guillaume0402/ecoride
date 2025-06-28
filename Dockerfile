#dockerfile
FROM php:8.2-apache

# Installation des extensions PHP nécessaires pour votre ECF
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Installation d'extensions supplémentaires si nécessaire
RUN apt-get update && apt-get install -y libzip-dev zip \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*


# Activation du module Apache rewrite (pour les URL propres)
RUN a2enmod rewrite

# Configuration Apache pour votre ECF
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Copie des fichiers du projet
COPY . /var/www/html/

# Permissions appropriées
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80