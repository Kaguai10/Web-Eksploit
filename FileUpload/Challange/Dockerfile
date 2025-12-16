FROM php:8.1-apache
COPY src/ /var/www/html/
COPY flag.txt /flag.txt
RUN chmod 444 /flag.txt
RUN chmod -R 777 /var/www/html/uploads
RUN a2enmod rewrite
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
EXPOSE 80
