#!/bin/bash
apt-get update
apt-get upgrade -y
apt-get install oracle-java8-jdk -y
apt-get install ant git vim -y
echo "ServerName 127.0.0.1" >> /etc/apache2/apache2.conf
apt-get install apache2 -y
apache2ctl configtest
apt-get install php libapache2-mod-php php-mcrypt php-mysql -y
service apache2 restart
apt-get install mysql-server -y
mysql -u root -praspberry -e "CREATE DATABASE data;GRANT ALL PRIVILEGES ON data.* TO 'root'@'localhost' IDENTIFIED BY 'raspberry';FLUSH PRIVILEGES;"
echo "Include /etc/phpmyadmin/apache.conf" >> /etc/apache2/apache2.conf
service apache2 restart
sudo mkdir /var/www/html/HealthKeeper/
sudo chmod -R 777 /var/www/html/HealthKeeper/
sudo cp ../Browser-src/Worker/* /var/www/html/HealthKeeper/
sudo chmod 777 /var/www/html/HeathKeeper/*
cd /var/www/html/HealthKeeper/
javac ./analyzer.java
echo ".................................."
echo "Successfully Installed Raspi-Fog"
echo "Note the worker IP address :"
hostname -I
echo "Press Enter to run"
read
java analyzer
