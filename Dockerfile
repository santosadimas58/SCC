FROM node:20-slim AS node_base

FROM php:8.2-fpm

COPY --from=node_base /usr/local/bin/node /usr/local/bin/node
COPY --from=node_base /usr/local/bin/npm  /usr/local/bin/npm
COPY --from=node_base /usr/local/bin/npx  /usr/local/bin/npx
COPY --from=node_base /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -sf /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -sf /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
    && node -v && npm -v

RUN apt-get update && apt-get install -y \
    libpq-dev libonig-dev libxml2-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    zip unzip git curl gnupg ca-certificates sudo nginx supervisor \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring xml zip gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm install && npm run build

RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

RUN cat > /etc/nginx/sites-available/default << 'NGINXCONF'
server {
    listen 8080;
    server_name _;
    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param HTTP_X_FORWARDED_PROTO https;
        fastcgi_param HTTPS on;
    }
}
NGINXCONF

RUN cat > /etc/supervisor/conf.d/app.conf << 'SUPCONF'
[supervisord]
nodaemon=true
logfile=/dev/null
logfile_maxbytes=0

[program:php-fpm]
command=php-fpm -F
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
SUPCONF

RUN cat > /usr/local/bin/startup << 'SCRIPT'
#!/bin/sh
chmod -R 777 /var/www/html/storage
chown -R www-data:www-data /var/www/html/storage
php /var/www/html/artisan migrate --force
php /var/www/html/artisan db:seed --force 2>/dev/null || true
php /var/www/html/artisan storage:link || true
php /var/www/html/artisan config:clear
php /var/www/html/artisan route:clear
php /var/www/html/artisan cache:clear
php /var/www/html/artisan view:clear
exec supervisord -c /etc/supervisor/supervisord.conf
SCRIPT

RUN chmod +x /usr/local/bin/startup

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/startup"]
