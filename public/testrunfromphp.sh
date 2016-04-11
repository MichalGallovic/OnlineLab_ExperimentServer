#!/bin/bash

#cd /tmp/ && mkdir tmptodelete

echo "start commpiling..."
cp /var/www/olm_app_server/public/source.c /var/www/olm_app_server/public/proj/src/sketch.ino; cd /var/www/olm_app_server/public/proj/; ino build; ino upload

echo "running done"
