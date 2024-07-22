# Use uma imagem base do PHP com suporte para Apache
FROM php:8.3-apache-bookworm

# Instale dependências do sistema
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install zip \
    && docker-php-ext-install pdo pdo_mysql

# Instale o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Defina o diretório de trabalho
WORKDIR /var/www/html

# Defina a variável de ambiente para permitir o Composer rodar como superusuário
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copie o arquivo composer e instale as dependências
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --ignore-platform-reqs

# Copie o restante do código da aplicação
COPY . .

# Instale as dependências do Composer
RUN composer dump-autoload

# Crie o arquivo service.json durante o build
RUN mkdir -p /var/www/html/config && echo '{}' > /var/www/html/config/service.json

# Defina as permissões corretas
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exponha a porta que o servidor vai utilizar
EXPOSE 80

# Inicie o Apache
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]