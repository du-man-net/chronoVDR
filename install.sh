
#!/bin/bash

# recupération des sources depuis github
wget https://github.com/du-man-net/chronoVDR/archive/refs/heads/master.zip
uunzip master.zip chronoVDR
CD chronoVDR


adminPW = chronoVDR    
echo "Mise à jour du gestionnaire de paquets"
apt-get update

#configuration du nom du raspberry pi

hostname chronoVDR

#configuration du fichier hosts - et installation du DNS

mv /etc/hosts /etc/hosts.back
cp conf/hosts /etc/hosts
apt install dnsmasq-base
cp dnsmasq.conf /etc/NetworkManager/dnsmasq-shared.d/dnsmasq.conf
mv /etc/NetworkManager/NetworkManager.conf /etc/NetworkManager/NetworkManager.back
cp conf/NetworkManager.conf /etc/NetworkManager/NetworkManager.conf

# configuration de Network Manager et création du HotSpot Wifi

nmcli device wifi hotspot ssid chronoVDR password 12345678
nmcli connection modify Hotspot connection.autoconnect yes
nmcli con modify Hotspot ipv4.method manual ipv4.address 172.16.111.1/24
nmcli con modify Hotspot ipv6.method disabled
nmcli con up Hotspot

systemctl restart dnsmasq 
systemctl restart NetworkManager.service

# installation de apache

echo "Installation/configuration de APACHE"
apt install apache2 php php-mbstring -y

# mise en place des virtual host sur les ports 80, 8080, 3000
mv /etc/apache2/sites-available/virtual.host.conf /etc/apache2/sites-available/virtual.host.back
cp conf/virtual.host.conf /etc/apache2/sites-available/virtual.host.conf
mv /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.back
cat /etc/apache2/sites-available/000-default.conf
sudo a2ensite virtual.host

# installation de mariadb

echo "Mot de passe de la base de donnée"
debconf-set-selections <<< 'mariadb-server mysql-server/root_password password $adminPW'
debconf-set-selections <<< 'mariadb-server mysql-server/root_password_again password $adminPW'
apt install mariadb-server php-mysql -y

mysql --user=root --password=$adminPW --execute="create database chronoVDR; use chronoVDR;Source chronoVDR.sql;"

# installation de phpmyadmin

sudo apt install phpmyadmin -y
sudo phpenmod mysqli
ln -s /usr/share/phpmyadmin /var/www/html/phpmyadmin

# mise en place des fichiers web

mkdir /var/www/html/chronoVDR/files
cp -R class /var/www/html/chronoVDR/class
cp -R config /var/www/html/chronoVDR/config
cp -R img /var/www/html/chronoVDR/img
cp -R vues /var/www/html/chronoVDR/vues
cp ajax_back.php /var/www/html/chronoVDR/ajax_back.php
cp index.php /var/www/html/chronoVDR/index.php
cp script.js /var/www/html/chronoVDR/script.js
cp style.css /var/www/html/chronoVDR/style.css
cp update /var/www/html/chronoVDR/update

# mise en place des librairies javascript
cd /var/www/html/chronoVDR
sudo apt install npm
npm install chart.js
npm install chart.js --save
rpm fund
npm install chartjs-adapter-luxon --save
rpm fund

sudo chown -R pi:www-data /var/www/html/
sudo chmod -R 770 /var/www/html/

sudo service apache2 restart

