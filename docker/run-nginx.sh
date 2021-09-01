#!/bin/bash

trapIt () { "$@"& pid="$!"; for SGNL in INT TERM CHLD USR1; do trap "kill -$SGNL $pid" "$SGNL"; done; while kill -0 $pid > /dev/null 2>&1; do wait $pid; ec="$?"; done; exit $ec; };

STATIC=/var/www/
args=" $@ "
if [[ ! $args =~ " --help " ]]; then
  ARG_STATIC=`/var/www/vendor/bin/ppm config --show-option="static-directory" "$@"`
fi

[ ! -z "$ARG_STATIC" ] && STATIC="${STATIC}${ARG_STATIC}"
sed -i "s#STATIC_DIRECTORY#${STATIC}#g" /etc/nginx/sites-enabled/default

nginx

mkdir -p /var/www/run
chmod -R 777 /var/www/run
ARGS='--port=8080 --socket-path=/var/www/run --pidfile=/var/www/ppm.pid'

# make sure static-directory is not served by php-pm
ARGS="$ARGS --static-directory=''"

trapIt /var/www/vendor/bin/ppm start --ansi $ARGS $@
