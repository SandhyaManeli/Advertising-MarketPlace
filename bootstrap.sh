# disable stdin
export DEBIAN_FRONTEND=noninteractive

# copy the application code to ~/src
sudo mv -R . ~/src

# installing apache2
apt-get install -y apache2

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
sudo drop database accounts;
sudo mysql -uroot -pmysql -e "CREATE DATABASE accounts /*\!40100 DEFAULT CHARACTER SET utf8 */;"
sudo mysql -uroot -pmysql accounts < "/vagrant/sql schema/newone.sql"

#install php
sudo apt-get autoremove --purge php5-*
sudo apt-get install software-properties-common python-software-properties -y
sudo LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php7.0 php7.0-fpm php7.0-cli libapache2-mod-php7.0 php7.0-mbstring php7.0-gd php7.0-intl php7.0-xsl php7.0-xml php7.0-mysql php7.0-mongodb php7.0-dev php-xml php-pear -y
sudo apt-get install pkg-config -y
sudo pecl install mongodb
echo 'extension=mongodb.so' | sudo tee /etc/php/7.0/mods-available/mongodb.ini
sudo a2enmod libapache2-mod-php7.0
sudo phpenmod -v 7.0 xml mongodb

#installing xdebug
# sudo cp ./xdebug-2.5.4.tgz ~/xdebug-2.5.4.tgz
# cd ~
# sudo tar -xvzf xdebug-2.5.4.tgz
# cd xdebug-2.5.4
# phpize
# ./configure
# make
# cp modules/xdebug.so /usr/lib/php/20151012
# echo 'zend_extension = /usr/lib/php/20151012/xdebug.so' >> /etc/php/7.0/apache2/php.ini
# echo 'xdebug.remote_enable=1' >> /etc/php/7.0/apache2/php.ini
# echo 'xdebug.remote_autostart = 1' >> /etc/php/7.0/apache2/php.ini
# echo 'xdebug.remote_connect_back = 1' >> /etc/php/7.0/apache2/php.ini	
# echo 'xdebug.remote_log=/var/log/xdebug.log' >> /etc/php/7.0/apache2/php.ini
# sudo service apache2 restart
	
#installing unzip
sudo apt-get install -y unzip

#install composer
sudo apt-get install curl -y
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# copying application to /var/www
sudo rm -rf /var/www/*
sudo cp -R ~/src /var/www/bbi-api
cd /var/www/bbi-api

# make necessary directories
cd /var/www/bbi-api
sudo mkdir vendor
sudo mkdir storage/fonts

# change ownership of directories to appropriate user
sudo chown -R www-data:www-data /var/www/*

# change permissions of directories
sudo chmod -R 766 /var/www/bbi-api/vendor
sudo chmod -R 766 /var/www/bbi-api/html/uploads
sudo chmod -R 766 /vagrant/bbi-api/storage

sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod proxy_balancer
sudo a2enmod lbmethod_byrequests
# allow override for directory specific .htaccess files.
sudo sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride all/' /etc/apache2/apache2.conf
sudo mv /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf.bak
sudo touch /etc/apache2/sites-available/000-default.conf
CONF=$(cat <<EOL
<VirtualHost *:*>
    ProxyPreserveHost On

    # Servers to proxy the connection, or;
    # List of application servers:
    # Usage:
    # ProxyPass / http://[IP Addr.]:[port]/
    # ProxyPassReverse / http://[IP Addr.]:[port]/
    # Example:
    ProxyPass / http://0.0.0.0:8080/
    ProxyPassReverse / http://0.0.0.0:8080/

    ServerName localhost
</VirtualHost>
EOL
)
echo "$CONF" | sudo tee /etc/apache2/sites-available/000-default.conf
sudo touch /etc/apache2/sites-available/bbi-api.conf
APICONF=$(cat <<EOLAPI
<VirtualHost *:8080>
    # The ServerName directive sets the request scheme, hostname and port that
    # the server uses to identify itself. This is used when creating
    # redirection URLs. In the context of virtual hosts, the ServerName
    # specifies what hostname must appear in the request's Host: header to
    # match this virtual host. For the default virtual host (this file) this
    # value is not decisive as it is used as a last resort host regardless.
    # However, you must set it for any further virtual host explicitly.
    #ServerName www.example.com

    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/bbi-api/html

    # Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
    # error, crit, alert, emerg.
    # It is also possible to configure the loglevel for particular
    # modules, e.g.
    #LogLevel info ssl:warn

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    # For most configuration files from conf-available/, which are
    # enabled or disabled at a global level, it is possible to
    # include a line for only one particular virtual host. For example the
    # following line enables the CGI configuration for this host only
    # after it has been globally disabled with "a2disconf".
    #Include conf-available/serve-cgi-bin.conf
</VirtualHost>
EOLAPI
)
echo "$APICONF" | sudo tee /etc/apache2/sites-available/bbi-api.conf
# vim: syntax=apache ts=4 sw=4 sts=4 sr noet

# Enable mod_rewrite
sudo a2enmod rewrite

# set up git
cd /var/www	
git init
git remote add origin https://bitbucket.org/mridulkashyap57/bbiapi.git

# take care of application dependencies
composer update
composer dump-autoload

# application specific needs
 
# php artisan migrate:refresh --seed
php artisan jwt:secret

# restart services
sudo service apache2 restart
sudo service mongod restart