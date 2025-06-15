FROM php:8.2-fpm-alpine

# Argumentos de build
ARG user=www
ARG uid=1000

# Instalar dependências do sistema Alpine
RUN apk update && apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    libzip-dev \
    supervisor \
    dcron \
    shadow \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar usuário do sistema para executar comandos do Composer e Artisan
# Alpine usa adduser em vez de useradd
RUN adduser -D -s /bin/sh -u $uid -G www-data $user || adduser -D -s /bin/sh $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Definir diretório de trabalho
WORKDIR /var/www

# Copiar arquivos de configuração customizados do PHP
COPY ./docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Copiar arquivos do projeto
COPY . /var/www

# Copiar arquivos de configuração do supervisor
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Criar diretórios necessários para logs (Alpine precisa criar explicitamente)
RUN mkdir -p /var/log/supervisor && \
    mkdir -p /var/run/supervisor

# Dar permissões apropriadas
RUN chown -R $user:www-data /var/www
RUN chmod -R 775 /var/www/storage
RUN chmod -R 775 /var/www/bootstrap/cache

# Mudar para o usuário criado
USER $user

# Expor porta 9000 e iniciar php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
