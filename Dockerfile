FROM php:8.3-apache

# Instalar dependências de sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip

# Limpar cache do apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões do PHP necessárias (como PDO PostgreSQL)
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Baixar e instalar o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar diretório de trabalho
WORKDIR /var/www/html

# Copiar os arquivos do projeto para o container
COPY . .

# Dar permissão para o Laravel gravar nas pastas de cache e logs
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Instalar pacotes do Laravel
RUN composer install --optimize-autoloader --no-dev

# Configurar o Apache para apontar para a pasta /public do Laravel
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# O Render injeta dinamicamente a variável de ambiente $PORT
RUN echo "Listen \${PORT}" > /etc/apache2/ports.conf
EXPOSE $PORT

# Rodar as migrações no banco ao ligar o container e iniciar o servidor Apache
CMD php artisan migrate --force && apache2-foreground
