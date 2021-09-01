@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../php-pm/php-pm/bin/ppm
php "%BIN_TARGET%" %*
