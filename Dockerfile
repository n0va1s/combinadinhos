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
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões do PHP
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar configuração limpa do Apache (substitui a padrão)
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Instalar o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar diretório de trabalho
WORKDIR /var/www/html

# Copiar os arquivos do projeto
COPY . .

# Definir permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Instalar dependências do Laravel
RUN composer install --optimize-autoloader --no-dev

# Porta dinâmica do Render
RUN echo "Listen \${PORT}" > /etc/apache2/ports.conf
EXPOSE 8080

# Rodar migrations e iniciar Apache
CMD php artisan migrate --force && apache2-foreground
