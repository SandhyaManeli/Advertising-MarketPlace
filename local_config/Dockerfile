# our base image
FROM ubuntu:16.04

ENV PROJECT_PATH=/var/www \
    DEBIAN_FRONTEND=noninteractive \
    PHP_INI=/etc/php/7.0/cli/php.ini \
    TERM=xterm

COPY api /api

COPY bootstrap.sh /var/bootstrap.sh

RUN apt-get update && \
	apt-get -y install sudo  && \
	sudo apt-get install software-properties-common -y && \
	sudo apt-get install -y apache2
	
RUN	sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv EA312927  && \
	echo "deb http://repo.mongodb.org/apt/ubuntu xenial/mongodb-org/3.2 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.2.list  && \
	sudo apt-get update  && \
	sudo apt-get install -y mongodb-org
	
RUN	echo 'mysql-server mysql-server/root_password password mysql' | sudo debconf-set-selections
RUN	echo 'mysql-server mysql-server/root_password_again password mysql' | sudo debconf-set-selections
RUN	sudo apt-get -y install mysql-server && \
	sudo etc/init.d/mysql start && \
	sleep 10s && \
	sudo mysql -uroot -pmysql -e "CREATE DATABASE accounts /*\!40100 DEFAULT CHARACTER SET utf8 */;" && \
	sudo mysql -uroot -pmysql accounts < "/api/sql schema/accounts.sql"
	
RUN	sudo apt-get autoremove --purge php5-*  && \
	sudo LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php  && \
	sudo apt-get update  && \
	sudo apt-get install php7.0 php7.0-fpm php7.0-cli libapache2-mod-php7.0 php7.0-mbstring php7.0-xml php7.0-mysql php7.0-mongodb php7.0-dev php-xml php-pear -y	
	
RUN	sudo apt-get install pkg-config

RUN	sudo pecl install mongodb  && \
	echo 'extension=mongodb.so' | sudo tee /etc/php/7.0/mods-available/mongodb.ini  && \
	sudo a2enmod php7.0 && \
	sudo phpenmod -v 7.0 xml mongodb

RUN	sudo service apache2 restart
	
RUN	sudo apt-get install -y unzip
	
RUN	sudo apt-get install curl && \
	curl -sS https://getcomposer.org/installer -o composer-setup.php  && \
	sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer  && \
	composer global require "laravel/lumen-installer"  && \
	
RUN	a2enmod rewrite  && \
	sudo service mongod start  && \
	cd /api  && \
	composer update
RUN	cd /api  && \
	php -S 0.0.0.0:8000 -t public
	
EXPOSE 8000