FROM alpine:3.14

ENV LC_ALL en_US.UTF-8
ENV LANG en_US.UTF-8

RUN apk -U upgrade && apk --no-cache add tzdata && \
    cp /usr/share/zoneinfo/Etc/UTC /etc/localtime && \
    echo "UTC" | tee /etc/timezone && \
    apk del tzdata

RUN apk --no-cache add curl bash nginx

RUN apk --no-cache add \
    php7 php7-opcache php7-fpm php7-cgi php7-ctype php7-json php7-dom php7-zip php7-zip php7-gd \
    php7-curl php7-mbstring php7-redis php7-mcrypt php7-iconv php7-posix php7-pdo_mysql php7-tokenizer php7-simplexml php7-session \
    php7-xml php7-sockets php7-openssl php7-fileinfo php7-ldap php7-exif php7-pcntl php7-xmlwriter php7-phar php7-zlib \
    php7-intl

COPY docker/common docker/hybrid /

ADD application /var/www

WORKDIR /var/www

ENTRYPOINT ["/bin/bash", "/etc/ppm/run.sh"]
# ENTRYPOINT ["/bin/bash"]

EXPOSE 80
