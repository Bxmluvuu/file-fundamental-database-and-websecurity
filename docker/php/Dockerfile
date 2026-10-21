FROM php:8.3-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install mysqli mbstring \
    && rm -rf /var/lib/apt/lists/*

RUN printf '%s\n' \
    'date.timezone=Asia/Bangkok' \
    'display_errors=Off' \
    'log_errors=On' \
    'error_log=/proc/self/fd/2' \
    'error_reporting=E_ALL' \
    > /usr/local/etc/php/conf.d/lab09.ini
