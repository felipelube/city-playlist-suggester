FROM php:7.4.11-apache-buster
# Use a configuração de produção do PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
# Instale o composer
RUN set -ex; \
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; \
php -r "if (hash_file('sha384', 'composer-setup.php') === '795f976fe0ebd8b75f26a6dd68f78fd3453ce79f32ecb33e7fd087d39bfeb978342fb73ac986cd4f54edd0dc902601dc') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"; \
php composer-setup.php; \
php -r "unlink('composer-setup.php');"; \
mv composer.phar /usr/bin/composer; \
chmod a+x /usr/bin/composer;
# Copie os arquivos com as dependências
COPY ./composer.* ./
# Instale dependências do sistema operacional
RUN set -ex; \
apt-get update -qq; \
apt-get install -y unzip libicu-dev;
# Instale a extensão intl do PHP
RUN docker-php-ext-install intl;
# Instale as dependências da aplicação
RUN composer install --no-dev --optimize-autoloader
# Faça o dump das variáveis de produção
RUN composer dump-env prod
# Copie o resto da aplicação
COPY ./ ./
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
# Configuração básica do apache para rodar um app Symfony
COPY ./apache.conf /etc/apache2/sites-available/000-default.conf
# Limpe o cache
RUN php bin/console cache:clear --env=prod
# TODO: não usar root