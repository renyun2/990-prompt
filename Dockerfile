FROM php:8.2-apache

# 安装PHP扩展
RUN apt-get update && apt-get install -y \
    mariadb-client \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY app/ /var/www/html/

# 复制Apache虚拟主机配置
COPY app/vhost.conf /etc/apache2/sites-available/000-default.conf

# 复制启动脚本
COPY app/docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

# 设置Apache配置
RUN chown -R www-data:www-data /var/www/html && \
    a2enmod rewrite && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80

ENTRYPOINT ["/docker-entrypoint.sh"]
