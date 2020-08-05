#!/bin/bash
echo Updating...
if [ ! -d "/var/www/bolt-tts.tk/assets" ]; then
    mkdir /var/www/bolt-tts.tk/assets
fi
if [ ! -d "/var/www/bolt-tts.tk/protected/qrcodes" ]; then
    mkdir /var/www/bolt-tts.tk/protected/qrcodes
fi
if [ ! -d "/var/www/bolt-tts.tk/uploads" ]; then
    mkdir /var/www/bolt-tts.tk/uploads
fi

echo stopping wt service...
systemctl stop wt

git stash
git pull

chown -R www-data:www-data /var/www/bolt-tts.tk/assets/
chown -R www-data:www-data /var/www/bolt-tts.tk/uploads/
chown -R www-data:www-data /var/www/bolt-tts.tk/protected/runtime/
chown -R www-data:www-data /var/www/bolt-tts.tk/protected/log/
chown -R www-data:www-data /var/www/bolt-tts.tk/protected/qrcodes/

chmod 755 /var/www/bolt-tts.tk/protected/yiic
chmod 755 /var/www/bolt-tts.tk/update-test.sh
chmod +x /var/www/bolt-tts.tk/update-test.sh

echo Versioning...
git rev-parse HEAD>version.txt

echo Starting wt service...
systemctl start wt

echo Done!
