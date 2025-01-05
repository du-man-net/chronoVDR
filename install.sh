#!/bin/bash

read -r -p "Voulez vous installer les mises à jour : [y/n] " maj
case "$maj" in
    [yY])
        apt update  && apt upgrade -y
        ;;
    [nN])
        ;;
esac

#read -p "Entrez le mot de passe admin : " adminPW
adminPW='hbreagq&'
vdrpath='chronoVDR-master'

echo  $vdrpath

echo '---------------------------------------'
echo Téléchargement des sources depuis github

rm master.zip
wget https://github.com/du-man-net/chronoVDR/archive/refs/heads/master.zip

echo '------------------------------------------------------'
echo Décompression des sources

rm $vdrpath -R
unzip master.zip

echo '------------------------------------------------------'
echo Installation de apache2

if [ -f /etc/init.d/apache2* ]; then
    echo "Apache2 est installé"
else
apt install apache2 php php-mbstring -y
fi

# mise en place des virtual host sur les ports 80, 8080, 3000
if [ -d /var/www/html/chronoVDR ]; then
    echo "Dossier web présent"
else
mkdir /var/www/html/chronoVDR
fi

echo '------------------------------------------------------'
echo Configartion de apache2
if [ -f /etc/apache2/sites-enabled/chronovdr_vhost.conf ]; then
    echo "VirtualHost installé"
else
a2dissite 000-default
cp $vdrpath/conf/chronovdr_vhost.conf /etc/apache2/sites-available/chronovdr_vhost.conf
a2ensite chronovdr_vhost
systemctl reload apache2
fi

echo '------------------------------------------------------'
echo Installation de mariadb-server

if [ -f /etc/init.d/mariadb* ]; then
    echo "MariaDB est installé"
else

apt install mariadb-server php-mysql -y

echo '------------------------------------------------------'
echo Sécurisation de mariadb-server

mysql --user=root  <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD("$adminPW");
DELETE FROM mysql.user WHERE USER LIKE '';
DELETE FROM mysql.user WHERE user = 'root' and host NOT IN ('127.0.0.1', 'localhost');
FLUSH PRIVILEGES;
DELETE FROM mysql.db WHERE db LIKE 'test%';
DROP DATABASE IF EXISTS test ;
EOF

echo '------------------------------------------------------'
echo Mise en place de la base de donnée

mysql --user=root --password=$adminPW --execute="create database chronoVDR;"
mysql --user=root --password=$adminPW chronoVDR < $vdrpath/conf/chronoVDR.sql
fi

echo '------------------------------------------------------'
echo Installation de phpmyadmin

if [ -d /usr/share/phpmyadmin ]; then
    echo "phpmyadmin est installé"
else

export DEBIAN_FRONTEND=noninteractive
debconf-set-selections <<< "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2"
debconf-set-selections <<< "phpmyadmin phpmyadmin/dbconfig-install boolean true"
debconf-set-selections <<< "phpmyadmin phpmyadmin/db/app-user string root"
debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/app-pass password $adminPW"
debconf-set-selections <<< "phpmyadmin phpmyadmin/app-password-confirm password $adminPW"

apt install phpmyadmin -qy
fi

if grep -q "Include /etc/phpmyadmin/apache.conf" /etc/apache2/apache2.conf; then
echo "phpmyadmin est activé"
else
echo "Include /etc/phpmyadmin/apache.conf" >> /etc/apache2/apache2.conf
fi

echo '------------------------------------------------------'
echo Mise à jour des fichiers WEB

mkdir -p /var/www/html/chronoVDR/files
cp -R $vdrpath/class /var/www/html/chronoVDR/class
cp -R $vdrpath/img /var/www/html/chronoVDR/img
cp -R $vdrpath/vues /var/www/html/chronoVDR/vues
cp -R $vdrpath/node_modules /var/www/html/chronoVDR/node_modules
cp $vdrpath/ajax_back.php /var/www/html/chronoVDR/ajax_back.php
cp $vdrpath/index.php /var/www/html/chronoVDR/index.php
cp $vdrpath/script.js /var/www/html/chronoVDR/script.js
cp $vdrpath/style.css /var/www/html/chronoVDR/style.css
cp $vdrpath/update /var/www/html/chronoVDR/update

if [ -f /var/www/html/chronoVDR/config/config.php ]; then
    echo "configuration chronoVDR OK"
else
    mkdir -p /var/www/html/chronoVDR/config
    echo  "<?php" >> /var/www/html/chronoVDR/config/config.php
    echo  '$username_db = "root";' >> /var/www/html/chronoVDR/config/config.php
    echo  $'$password_db = "'$adminPW'";' >> /var/www/html/chronoVDR/config/config.php
    echo  $'$admin_password = "'$adminPW'";' >> /var/www/html/chronoVDR/config/config.php
fi

chown -R pi:www-data /var/www/html/
chmod -R 770 /var/www/html/

service apache2 restart

echo '------------------------------------------------------'
echo Hotspot WIFI

wifidevice="no"
for device in $(nmcli device | awk '$2=="wifi" {print $1}'); do
    wifidevice=$device
done

wificon="no"
for con in $(nmcli con show | awk '$1=="Hotspot" {print $1}'); do
   wificon=$con
done

if [ "$wifidevice" = "no" ];then
   echo "Aucune carte réseau wifi trouvée"
else
   if [ "$wificon" = "Hotspot" ]; then
      echo "conexion $wificon trouvée pour $wifidevice"
   else
      echo "création du Hotspot wifi pour $wifidevice"
      nmcli con add type wifi ifname $wifidevice mode ap con-name Hotspot ssid chronoVDR
      nmcli con modify Hotspot ipv4.method shared ipv4.address 172.16.1.1/24
      nmcli con modify Hotspot ipv6.method disabled
      nmcli con modify Hotspot wifi-sec.key-mgmt wpa-psk
      nmcli con modify Hotspot wifi-sec.psk "12345678"
      nmcli con up Hotspot
   fi
fi

echo '------------------------------------------------------'
echo installation de dnsmasq
if [ -f /usr/sbin/dnsmasq ]; then
    echo "dnsmasq est installé"
else
   apt install dnsmasq-base -y
   if [ -f /etc/NetworkManager/conf.d/00-use-dnsmasq.conf ]; then
      echo "plugin dnsmasq activé"
   else
      cp $vdrpath/conf/00-use-dnsmasq.conf /etc/NetworkManager/conf.d/00-use-dnsmasq.conf
      cp $vdrpath/conf/00-chronovdr.conf /etc/NetworkManager/dnsmasq.d/00-chronovdr.conf
      cp $vdrpath/conf/01-chronovdr.conf /etc/NetworkManager/dnsmasq.d/01-chronovdr.conf
      cp $vdrpath/conf/02-chronovdr.conf /etc/NetworkManager/dnsmasq.d/02-chronovdr.conf
   fi
   sudo systemctl restart NetworkManager
fi

echo '------------------------------------------------------'
echo configuration du système de nommage
if grep -q "chronovdr" /etc/hostname; then
   echo "/etc/hostname vérifié"
else
   echo "chronovdr" > /etc/hostname
fi

if grep -q "chronovdr" /etc/hosts; then
   echo "/etc/hosts vérifié"
else
   cp $vdrpath/conf/hosts /etc/hosts
fi

# mise en place des librairies javascript
#cd /var/www/html/chronoVDR
#sudo apt install npm
#npm install chart.js
#npm install chart.js --save
#npm fund
#npm install chartjs-adapter-luxon --save
#npm fund
