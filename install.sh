
#!/bin/bash

echo "Entrez le mot de passe admin : "
read -s adminPW

# recupération des sources depuis github
echo wget github
wget https://github.com/du-man-net/chronoVDR/archive/refs/heads/master.zip
echo prépartion
mkdir chronoVDR
echo prépartion
#erreur
unzip master.zip 
cd chronoVDR-master

#configuration du fichier hosts - et installation du DNS

apt install dnsmasq-base
cp conf/dnsmasq.conf /etc/NetworkManager/dnsmasq-shared.d/dnsmasq.conf
mv /etc/NetworkManager/NetworkManager.conf /etc/NetworkManager/NetworkManager.back
cp conf/NetworkManager.conf /etc/NetworkManager/NetworkManager.conf

# configuration de Network Manager et création du HotSpot Wifi
nmcli con delete Hotspot
nmcli con add type wifi ifname wlan0 mode ap con-name Hotspot ssid chronoVDR
nmcli con modify Hotspot ipv4.method manual ipv4.address 172.16.1.1/24
nmcli con modify Hotspot ipv6.method disabled
nmcli con modify Hotspot wifi-sec.key-mgmt wpa-psk
nmcli con modify Hotspot wifi-sec.psk "12345678"
nmcli con up Hotspot

systemctl restart dnsmasq 
systemctl restart NetworkManager.service

# installation de apache

echo "Installation/configuration de APACHE"
apt install apache2 php php-mbstring -y

# mise en place des virtual host sur les ports 80, 8080, 3000
#erreur
mkdir /var/www/html/chronoVDR
mv /etc/apache2/sites-available/virtual.host.conf /etc/apache2/sites-available/virtual.host.back

#erreur
cp conf/virtual.host.conf /etc/apache2/sites-available/virtual.host.conf
mv /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.back
cat /etc/apache2/sites-available/000-default.conf
sudo a2ensite virtual.host

# installation de mariadb

echo "Mot de passe de la base de donnée"
sudo mysql <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$adminPW';
FLUSH PRIVILEGES;
EOF

debconf-set-selections <<< 'mariadb-server mysql-server/root_password password $adminPW'
debconf-set-selections <<< 'mariadb-server mysql-server/root_password_again password $adminPW'
apt install mariadb-server php-mysql -y

#erreur
mysql -u root -p'$adminPW' --execute="create database chronoVDR;"
mysql -u root -p'$adminPW' chronoVDR < conf/chronoVDR.sql


# installation de phpmyadmin

echo "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2" | sudo debconf-set-selections
echo "phpmyadmin phpmyadmin/dbconfig-install boolean true"             | sudo debconf-set-selections
echo "phpmyadmin phpmyadmin/db/app-user string root"                   | sudo debconf-set-selections
echo "phpmyadmin phpmyadmin/mysql/app-pass password $adminPW"          | sudo debconf-set-selections
echo "phpmyadmin phpmyadmin/app-password-confirm password $adminPW"    | sudo debconf-set-selections

sudo apt install phpmyadmin -y
sudo phpenmod mysqli
sudo phpenmod mbstring
ln -s /usr/share/phpmyadmin /var/www/html/phpmyadmin

# mise en place des fichiers web

mkdir /var/www/html/chronoVDR/files
cp -R class /var/www/html/chronoVDR/class
cp -R config /var/www/html/chronoVDR/config
cp -R img /var/www/html/chronoVDR/img
cp -R vues /var/www/html/chronoVDR/vues
cp -R node_modules /var/www/html/chronoVDR/node_modules
cp ajax_back.php /var/www/html/chronoVDR/ajax_back.php
cp index.php /var/www/html/chronoVDR/index.php
cp script.js /var/www/html/chronoVDR/script.js
cp style.css /var/www/html/chronoVDR/style.css
cp update /var/www/html/chronoVDR/update

#Ajouter les mots de passe admin et mysql au fichier config

echo "\$password_db = '$adminPW';" >> /var/www/html/chronoVDR/config/config.php
echo "\$admin_password = '$adminPW';" >> /var/www/html/chronoVDR/config/config.php

# mise en place des librairies javascript
#cd /var/www/html/chronoVDR
#sudo apt install npm
#npm install chart.js
#npm install chart.js --save
#npm fund
#npm install chartjs-adapter-luxon --save
#npm fund

sudo chown -R pi:www-data /var/www/html/
sudo chmod -R 770 /var/www/html/

sudo service apache2 restart

#configuration du fichier hostname - nom de l'ordinateur

mv /etc/hostname /etc/hostname.back
cp conf/hostname /etc/hostname
hostname chronoVDR
mv /etc/hosts /etc/hosts.back
cp conf/hosts /etc/hosts

#ménage
cd ..
rm -r chronoVDR
rm -r chronoVDR-master
rm master.zip