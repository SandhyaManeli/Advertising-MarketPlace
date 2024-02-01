#!/bin/sh

# disable stdin
export DEBIAN_FRONTEND=noninteractive

# installing apache2
sudo apt-get update
sudo apt-get install -y apache2

#installing mongodb
sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv EA312927
echo "deb http://repo.mongodb.org/apt/ubuntu xenial/mongodb-org/3.2 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.2.list
sudo apt-get update
sudo apt-get install -y mongodb-org

#install mysql
echo 'mysql-server mysql-server/root_password password mysql' | sudo debconf-set-selections
echo 'mysql-server mysql-server/root_password_again password mysql' | sudo debconf-set-selections
sudo apt-get -y install mysql-server

#create database
sudo mysql -uroot -pmysql -e "CREATE DATABASE accounts /*\!40100 DEFAULT CHARACTER SET utf8 */;"
sudo mysql -uroot -pmysql accounts < "/api/sql schema/accounts.sql"

#install php
sudo apt-get autoremove --purge php5-*
sudo LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php7.0 php7.0-fpm php7.0-cli libapache2-mod-php7.0 php7.0-mbstring php7.0-xml php7.0-mysql php7.0-mongodb php7.0-dev php-xml php-pear -y
sudo apt-get install pkg-config
sudo pecl install mongodb
echo 'extension=mongodb.so' | sudo tee /etc/php/7.0/mods-available/mongodb.ini
sudo a2enmod php7.0
sudo phpenmod -v 7.0 xml mongodb
sudo service apache2 restart

#installing unzip
sudo apt-get install -y unzip

#install composer
sudo apt-get install curl
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

#install Lumen
composer global require "laravel/lumen-installer"


# configuration	
################

# Enable mod_rewrite
a2enmod rewrite

# start mongo service
sudo service mongod start

#update Lumen project dependencies
cd /api
composer update

cd /api
php -S 0.0.0.0:8000 -t public