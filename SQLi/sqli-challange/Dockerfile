FROM php:8.1-apache
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html/data && chmod -R 755 /var/www/html/data
EXPOSE 80