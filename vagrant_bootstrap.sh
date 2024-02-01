# disable stdin
export DEBIAN_FRONTEND=noninteractive

# Determine if this machine has already been provisioned
# Basically, run everything after this command once, and only once
if ! [ -f "/var/vagrant_provision" ]; then 
	
	#install nodejs
	# curl -sL https://deb.nodesource.com/setup_8.x | sudo -E bash -
	# sudo apt-get install -y nodejs
	# sudo ln -sf /usr/bin/nodejs /usr/bin/node
	
	# installing apache2
	# apt-get update
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
	sudo mysql -uroot -pmysql -e "CREATE DATABASE accounts /*\!40100 DEFAULT CHARACTER SET utf8 */;"
	# sudo mysql -uroot -pmysql accounts < "/vagrant/sql schema/accounts.sql"

	#install php
	sudo apt-get autoremove --purge php5-*
	sudo apt-get install software-properties-common python-software-properties -y
	sudo LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php
	sudo apt-get update
	sudo apt-get install php7.0 php7.0-fpm php7.0-cli libapache2-mod-php7.0 php7.0-mbstring php7.0-gd php7.0-intl php7.0-xsl php7.0-xml php7.0-mysql php7.0-mongodb php7.0-dev php-xml php-pear php7.0-curl -y
	sudo apt-get install pkg-config
	sudo pecl install mongodb
	echo 'extension=mongodb.so' | sudo tee /etc/php/7.0/mods-available/mongodb.ini
	sudo a2enmod libapache2-mod-php7.0
	sudo phpenmod -v 7.0 xml mongodb
	sudo service apache2 restart

	#installing xdebug
	sudo cp /vagrant/xdebug-2.5.4.tgz ~/xdebug-2.5.4.tgz
	cd ~
	sudo tar -xvzf xdebug-2.5.4.tgz
	cd xdebug-2.5.4
	sudo phpize
	sudo ./configure
	sudo make
	sudo cp modules/xdebug.so /usr/lib/php/20151012
	echo 'zend_extension = /usr/lib/php/20151012/xdebug.so' >> /etc/php/7.0/apache2/php.ini
	echo 'xdebug.remote_enable=1' >> /etc/php/7.0/apache2/php.ini
	echo 'xdebug.remote_autostart = 1' >> /etc/php/7.0/apache2/php.ini
	echo 'xdebug.remote_connect_back = 1' >> /etc/php/7.0/apache2/php.ini	
	echo 'xdebug.remote_log=/var/log/xdebug.log' >> /etc/php/7.0/apache2/php.ini
	sudo service apache2 restart
	
	#installing unzip
	sudo apt-get install -y unzip
	
	#install composer
	sudo apt-get install curl -y
	curl -sS https://getcomposer.org/installer -o composer-setup.php
	sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

	#install Lumen
	# composer global require "laravel/lumen-installer"

	#global node dependencies
	# sudo npm install -gy webpack webpack-dev-server typescript @angular/cli --no-bin-links
	
	# check if the public html directory exists
	#creating symlink for application
	sudo rm -rf /var/www
	cd /vagrant
	sudo ln -s /vagrant /var/www
	cd /var/www
	sudo mkdir vendor
	sudo chmod -R 777 vendor
	sudo chown -R $USER ~/.composer/

	sudo sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride all/' /etc/apache2/apache2.conf
	# sudo sed -i '/net:/,/\#processManagement:/ s/bindIp: 127.0.0.1/bindIp: 0.0.0.0/' /etc/mongod.conf

	# chown the html folder and everything inside it to www-data:www-data
	sudo chown -R www-data:www-data /var/www/html
	# change the permission to read and write
	sudo chmod -R 766 /var/www/html/uploads
	
	# Enable mod_rewrite
	sudo a2enmod rewrite

	sudo service apache2 restart

	sudo service mongod start
	
	cd /var/www	
	composer update
	composer dump-autoload
	sudo cp /vagrant/.env.example .env
	sudo chmod 766 .env
	php artisan migrate:refresh --seed
	php artisan jwt:secret
	# Making necessary folders for the application to run
	sudo mkdir /var/www/storage/fonts
	sudo chown -R www-data:www-data storage
	sudo chmod -R 766 /var/www/storage
	sudo chmod -R a+X /var/www/storage

	# if [ -d /var/www ]; 
		
	# 	else
	# 		sudo mkdir /var/www
	# 		sudo ln -fs /vagrant /var/www
	# fi
		
	# Making sure the installations part doesn't run again
	sudo touch /var/vagrant_provision
	
fi

# configuration	
################

# start mongo service
sudo service mongod start

#update Lumen project dependencies
cd /var/www
composer update
composer dump-autoload

# cd /vagrant
# php -S 0.0.0.0:8000 -t public &
# update angular app dependencies
# cd /vagrant/app
# npm install --no-bin-links
# ng serve &