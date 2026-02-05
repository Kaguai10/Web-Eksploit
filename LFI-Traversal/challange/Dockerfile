FROM php:8.2-apache
RUN a2enmod rewrite
COPY src/ /var/www/html/
COPY secret/ /secret/
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
EXPOSE 80
