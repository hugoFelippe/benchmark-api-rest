#!/bin/bash

trapIt () { "$@"& pid="$!"; for SGNL in INT TERM CHLD USR1; do trap "kill -$SGNL $pid" "$SGNL"; done; while kill -0 $pid > /dev/null 2>&1; do wait $pid; ec="$?"; done; exit $ec; };

STATIC=/var/www/public
args=" $@ "
if [[ ! $args =~ " --help " ]]; then
  ARG_STATIC=`/ppm/vendor/bin/ppm config --show-option="static-directory" "$@"`
fi

[ ! -z "$ARG_STATIC" ] && STATIC="${STATIC}${ARG_STATIC}"
sed -i "s#STATIC_DIRECTORY#${STATIC}#g" /etc/nginx/sites-enabled/default

nginx

# make sure static-directory is not served by php-pm
ARGS="$ARGS --static-directory=''"

ARGS="$ARGS --config /etc/ppm/ppm.json"
trapIt /var/www/vendor/bin/ppm start --ansi $ARGS $@
