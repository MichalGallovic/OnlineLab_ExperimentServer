#!/bin/bash

# COLORING HELPERS
wget https://raw.githubusercontent.com/xr09/rainbow.sh/master/rainbow.sh
source rainbow.sh

ran_fromm=$(pwd)

# Composer checks - php dependency manager
echoyellow "Checking Composer installation"

if [ -f "composer.phar" ]; then
echogreen "Composer is already installed"
else
echoyellow "Composer missing. Initiating installation ..."
php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
php -r "if (hash('SHA384', file_get_contents('composer-setup.php')) === '7228c001f88bee97506740ef0888240bd8a760b046ee16db8f4095c0d8d525f2367663f22a46b48d072c816e7fe19959') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
fi

echoyellow "Pulling master branch from git"

# Pulling changes from git
git pull

echoyellow "Updating app dependencies"
php composer.phar install

echoyellow "Settings permisions"
cd ..
chmod -R 777 $ran_from
cd $ran_from

rm rainbow.sh
