#!/usr/bin/zsh
# I don't know how to share bfr and http
# the http doe not create with group permissions!
FIND="$(which find)"
# allow for bfr and httpd to use
$FIND . -type d -exec chmod 777 {} \;
# allow for bfr and httpd to use and write
$FIND . -type f -exec chmod 666 {} \;
