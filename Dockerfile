FROM php:8.2-apache

# System deps + msmtp + Python 3
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libjpeg-dev libwebp-dev msmtp \
    python3 python3-pip \
    && pip3 install --break-system-packages openpyxl \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions (calendar fixes cal_days_in_month)
RUN docker-php-ext-configure gd --with-jpeg --with-webp \
 && docker-php-ext-install mysqli pdo_mysql gd calendar

# msmtp config — routes PHP mail() through MailHog (dev) or real SMTP
RUN echo "account default\nhost mailhog\nport 1025\nfrom noreply@sjassms.local\nauto_from on\nlogfile /var/log/msmtp.log" \
    > /etc/msmtprc && chmod 644 /etc/msmtprc

# Point PHP sendmail_path at msmtp
RUN echo 'sendmail_path = "/usr/bin/msmtp -t --read-envelope-from"' \
    > /usr/local/etc/php/conf.d/mail.ini

# Enable mod_rewrite for .htaccess support
RUN a2enmod rewrite

# Allow .htaccess overrides in the web root
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# PHP upload limits
RUN echo "upload_max_filesize = 15M\npost_max_size = 16M\nmemory_limit = 256M" \
    > /usr/local/etc/php/conf.d/uploads.ini

# Ensure uploads directory is writable
RUN mkdir -p /var/www/html/uploads/website/gallery \
             /var/www/html/uploads/website/news \
             /var/www/html/uploads/website/sliders \
 && chown -R www-data:www-data /var/www/html/uploads
