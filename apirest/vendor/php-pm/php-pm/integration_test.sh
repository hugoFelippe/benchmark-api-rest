#!/bin/bash
# exit when any command fails
set -e

# Integration test
mkdir web && echo "Hello" > web/index.html
bin/ppm start --workers=1 --bridge=StaticBridge --static-directory=web --max-requests=1 -v &
sleep 5
bin/ppm status
curl -v -f "http://127.0.0.1:8080"
bin/ppm status
curl -v -f "http://127.0.0.1:8080"
bin/ppm status
bin/ppm stop
# Test memory limit
bin/ppm start --workers=1 --bridge=PHPPM\\Tests\\TestBridge --static-directory=web --memory-limit=5 -v > /tmp/ppmout &
sleep 5
# Memory limit: Nothing should happen
curl -f --silent "http://127.0.0.1:8080/test?memory=0" &
sleep 5
bash -c 'grep -q "because it reached memory limit of 5" /tmp/ppmout || exit 0'
bin/ppm status
sleep 5
# Memory limit: Worker should restart
curl -f --silent "http://127.0.0.1:8080/test?memory=10" &
sleep 5
bash -c 'grep -q "because it reached memory limit of 5" /tmp/ppmout && exit 0'
bin/ppm status
bin/ppm stop
# Trigger 502 error by triggering an exit() in the worker
bin/ppm start --workers=1 --bridge=PHPPM\\Tests\\TestBridge --static-directory=web --max-requests=1 --max-execution-time=15 -v > /tmp/ppmout &
sleep 5
bash -c 'if [[ $(curl --write-out %{http_code} --silent --output /dev/null "http://127.0.0.1:8080/test?exit_prematurely=1") == "502" ]]; then exit 0; else exit 1; fi'
bin/ppm status
# Trigger 503 error by tying up the worker and making a second request
curl -f --silent "http://127.0.0.1:8080/test?sleep=10000" &
bash -c 'if [[ $(curl --write-out %{http_code} --silent --output /dev/null "http://127.0.0.1:8080") == "503" ]]; then exit 0; else exit 1; fi'
bin/ppm status
# Trigger 504 error by making a sleep request and waiting until the timeout
bash -c 'if [[ $(curl --write-out %{http_code} --silent --output /dev/null "http://127.0.0.1:8080/test?sleep=10000") == "504" ]]; then exit 0; else exit 1; fi'
bin/ppm status
# Trigger 502 error by making the bridge throw an exception
bash -c 'if [[ $(curl --write-out %{http_code} --silent --output /dev/null "http://127.0.0.1:8080/test?exception=1") == "502" ]]; then exit 0; else exit 1; fi'
bash -c 'grep -q "An exception was thrown by the bridge. Forcing restart of the worker. The exception was" /tmp/ppmout && exit 0'
bash -c 'grep -q "This is a very bad exception" /tmp/ppmout && exit 0'
bash -c 'grep -q "Shutdown function triggered" /tmp/ppmoutshutdownfunc && exit 0'
bin/ppm status
bin/ppm stop
