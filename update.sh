#!/bin/bash
echo Updating...
if [ ! -d "/var/www/bolt/assets" ]; then
    mkdir /var/www/bolt/assets
fi
if [ ! -d "/var/www/bolt/protected/qrcodes" ]; then
    mkdir /var/www/bolt/protected/qrcodes
fi
if [ ! -d "/var/www/bolt/uploads" ]; then
    mkdir /var/www/bolt/uploads
fi

echo stopping wt service...
systemctl stop wt

git stash
git pull

chown -R www-data:www-data /var/www/bolt/assets/
chown -R www-data:www-data /var/www/bolt/uploads/
chown -R www-data:www-data /var/www/bolt/protected/runtime/
chown -R www-data:www-data /var/www/bolt/protected/log/
chown -R www-data:www-data /var/www/bolt/protected/qrcodes/

chmod 755 /var/www/bolt/protected/yiic
chmod 755 /var/www/bolt/update.sh
chmod +x /var/www/bolt/update.sh

rm index.php
cp index-production.php index.php 

echo Versioning...
git rev-parse HEAD>version.txt

echo Starting wt service...
systemctl start wt

echo Done!
