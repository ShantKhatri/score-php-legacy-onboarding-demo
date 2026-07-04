FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql

COPY app/src/ /var/www/html/

# The only two files this onboarding adds.
COPY docker/config.php.tmpl          /app-config-template/config.php.tmpl
COPY docker/render-config-and-start.sh /usr/local/bin/render-config-and-start.sh
RUN chmod +x /usr/local/bin/render-config-and-start.sh

ENTRYPOINT ["/usr/local/bin/render-config-and-start.sh"]
