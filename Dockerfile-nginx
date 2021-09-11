FROM existenz/webstack:7.4

COPY --chown=php:nginx application /www

COPY docker/common docker/nginx /

RUN find /www -type d -exec chmod -R 555 {} \; \
    && find /www -type f -exec chmod -R 444 {} \; \
    && find /www/public -type d -exec chmod -R 755 {} \; \
    && find /www/public -type f -exec chmod -R 644 {} \;

RUN apk -U --no-cache add \
    bash php7-opcache php7-ctype php7-json php7-dom php7-zip php7-zip php7-gd \
    php7-curl php7-mbstring php7-redis php7-mcrypt php7-iconv php7-posix php7-pdo_mysql php7-tokenizer php7-simplexml php7-session \
    php7-xml php7-sockets php7-openssl php7-fileinfo php7-ldap php7-exif php7-pcntl php7-xmlwriter php7-phar php7-zlib \
    php7-intl
