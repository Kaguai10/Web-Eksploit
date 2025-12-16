FROM php:8.2-apache

# Enable apache rewrite module
RUN a2enmod rewrite

# Allow .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy web files
COPY public/ /var/www/html/

# Copy hidden flag
COPY hidden/ /hidden/

RUN chmod -R 755 /hidden

EXPOSE 80
