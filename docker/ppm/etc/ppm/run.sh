#!/bin/bash

trapIt () { "$@"& pid="$!"; for SGNL in INT TERM CHLD USR1; do trap "kill -$SGNL $pid" "$SGNL"; done; while kill -0 $pid > /dev/null 2>&1; do wait $pid; ec="$?"; done; exit $ec; };

ARGS='--config /etc/ppm/ppm.json'
trapIt /var/www/vendor/bin/ppm start --ansi $ARGS  $@
