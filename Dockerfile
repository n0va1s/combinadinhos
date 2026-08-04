FROM php:8.4-apache

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

# Dar permissão para o Apache ler todos os arquivos e gravar em logs/cache
RUN chown -R www-data:www-data /var/www/html

# Instalar pacotes do Laravel
RUN composer install --optimize-autoloader --no-dev

# Configurar o Apache para apontar para a pasta /public do Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/public/!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
RUN a2enmod rewrite

# O Render injeta dinamicamente a variável de ambiente $PORT
RUN echo "Listen \${PORT}" > /etc/apache2/ports.conf
EXPOSE $PORT

# Rodar as migrações no banco ao ligar o container e iniciar o servidor Apache
CMD php artisan migrate --force && apache2-foreground
