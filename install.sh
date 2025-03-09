#!/bin/bash


# Copyright (C) 2025 Gérard Léon
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.


vdrpath='chronoVDR-master'

echo '------------------------------------------------------'
while [ -z "$adminPW" ]; do
echo "Entrez le mot de passe admin : "
read -s first
read -s -p "Confirmer le mot de passe admin: " second
if [ $first == $second ];
then
adminPW=$first
else
echo "Les mots de passe sont différents. Reessayez..."
continue
fi
break
done

echo
echo '------------------------------------------------------'
read -r -p "Voulez vous installer les mises à jour : [o/n] " maj
case "$maj" in
[oO])
apt update  && apt upgrade -y -q
;;
[nN])
;;
esac

echo
echo '---------------------------------------'
echo Téléchargement des sources depuis github
echo

rm master.zip
wget https://github.com/du-man-net/chronoVDR/archive/refs/heads/master.zip

echo
echo '------------------------------------------------------'
echo Décompression des sources
echo

rm $vdrpath -R
unzip -o master.zip  | awk 'BEGIN {ORS=""} {if(NR%2==0)print "."}'

echo
echo '------------------------------------------------------'
echo Installation de apache2
echo

if [ -f /etc/init.d/apache2* ]; then
echo "Apache2 est installé"
else
apt install apache2 php php-mbstring -y -q
fi

# mise en place des virtual host sur les ports 80, 8080, 3000
if [ -d /var/www/html/chronoVDR ]; then
echo "Dossier web présent"
else
mkdir /var/www/html/chronoVDR
fi

echo
echo '------------------------------------------------------'
echo Configartion de apache2
echo

if [ -f /etc/apache2/sites-enabled/chronovdr_vhost.conf ]; then
echo "VirtualHost installé"
else
a2dissite 000-default
cp $vdrpath/conf/chronovdr_vhost.conf /etc/apache2/sites-available/chronovdr_vhost.conf
a2ensite chronovdr_vhost
systemctl reload apache2
fi

echo
echo '------------------------------------------------------'
echo Installation de mariadb-server
echo

if [ -f /etc/init.d/mariadb* ]; then
cho "MariaDB est installé"
else

apt install mariadb-server php-mysql -y -q

echo
echo '------------------------------------------------------'
echo Sécurisation de mariadb-server
echo

mysql --user=root  <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD("$adminPW");
DELETE FROM mysql.user WHERE USER LIKE '';
DELETE FROM mysql.user WHERE user = 'root' and host NOT IN ('127.0.0.1', 'localhost');
FLUSH PRIVILEGES;
DELETE FROM mysql.db WHERE db LIKE 'test%';
DROP DATABASE IF EXISTS test ;
EOF

echo
echo '------------------------------------------------------'
echo Mise en place de la base de donnée
echo

mysql --user=root --password=$adminPW --execute="create database chronoVDR;"
mysql --user=root --password=$adminPW chronoVDR < $vdrpath/conf/chronoVDR.sql

fi

echo
echo '------------------------------------------------------'
echo Installation de phpmyadmin
echo

if [ -d /usr/share/phpmyadmin ]; then
echo "phpmyadmin est installé"
else

export DEBIAN_FRONTEND=noninteractive
debconf-set-selections <<< "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2"
debconf-set-selections <<< "phpmyadmin phpmyadmin/dbconfig-install boolean true"
debconf-set-selections <<< "phpmyadmin phpmyadmin/db/app-user string root"
debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/app-pass password $adminPW"
debconf-set-selections <<< "phpmyadmin phpmyadmin/app-password-confirm password $adminPW"
apt install phpmyadmin -y -q 
fi

if grep -q "Include /etc/phpmyadmin/apache.conf" /etc/apache2/apache2.conf; then
echo "phpmyadmin est activé"
else
echo "Include /etc/phpmyadmin/apache.conf" >> /etc/apache2/apache2.conf
fi

echo
echo '------------------------------------------------------'
echo Mise à jour des fichiers WEB
echo

mkdir -p /var/www/html/chronoVDR/files
touch /var/www/html/chronoVDR/files/lastupdate
touch /var/www/html/chronoVDR/files/logs.txt
cp -R $vdrpath/class /var/www/html/chronoVDR/
cp -R $vdrpath/img /var/www/html/chronoVDR/
cp -R $vdrpath/vues /var/www/html/chronoVDR/
cp -R $vdrpath/node_modules /var/www/html/chronoVDR/
cp $vdrpath/ajax_back.php /var/www/html/chronoVDR/ajax_back.php
cp $vdrpath/index.php /var/www/html/chronoVDR/index.php
cp $vdrpath/script.js /var/www/html/chronoVDR/script.js
cp $vdrpath/style.css /var/www/html/chronoVDR/style.css
cp $vdrpath/update.php /var/www/html/chronoVDR/update.php
cp $vdrpath/last_update.php /var/www/html/chronoVDR/last_update.php

if [ -f /var/www/html/chronoVDR/config/config.php ]; then
echo "configuration chronoVDR OK"
else
mkdir -p /var/www/html/chronoVDR/config
echo  $"$adminPW" > /var/www/html/chronoVDR/config/mysql_password
fi

chown -R pi:www-data /var/www/html/
chmod -R 770 /var/www/html/

service apache2 restart

echo
echo '------------------------------------------------------'
echo Communication série avec Micro:bit
echo

if [ -f /etc/udev/rules.d/99-serial_background.rules ]; then
echo "Configration communication avec Micro:bit OK"
else
apt install python3 python3-serial python3-pip at -y -q
rm /usr/lib/python3.11/EXTERNALLY-MANAGED
pip3 install mysql-connector
cp $vdrpath/conf/99-serial_background.rules /etc/udev/rules.d/99-serial_background.rules
fi

cp -R $vdrpath/serial /var/www/html/chronoVDR/
chown -R pi:www-data /var/www/html/chronoVDR/serial/
chmod -R 770 /var/www/html/chronoVDR/serial/
chmod +x /var/www/html/chronoVDR/serial/serial.sh
chmod +x /var/www/html/chronoVDR/serial/microbit.py
udevadm control --reload

if [ -f /lib/systemd/system/chronovdr.service ]; then
echo "Service de communication série installé"
else
echo "Installation du service de communication série"
cp $vdrpath/conf/chronovdr.service /lib/systemd/system/chronovdr.service
chmod 644 /lib/systemd/system/chronovdr.service
systemctl daemon-reload
systemctl enable chronovdr.service
fi

echo
echo '------------------------------------------------------'
echo Hotspot WIFI
echo

nmcli radio wifi on
iw reg set FR
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
nmcli con add type wifi ifname $wifidevice mode ap con-name Hotspot ssid chrono.vdr
nmcli con modify Hotspot ipv4.method shared ipv4.address 172.16.1.1/24
nmcli con modify Hotspot ipv6.method disabled
nmcli con modify Hotspot wifi-sec.key-mgmt wpa-psk
nmcli con modify Hotspot wifi-sec.psk "12345678"
nmcli con up Hotspot
fi
fi

echo
echo '------------------------------------------------------'
echo installation de dnsmasq
echo

if [ -f /usr/sbin/dnsmasq ]; then
echo "dnsmasq-base est installé"
else
apt install dnsmasq-base -y -q 
fi
if [ -f /etc/NetworkManager/conf.d/00-use-dnsmasq.conf ]; then
echo "plugin dnsmasq activé"
else
cp $vdrpath/conf/00-use-dnsmasq.conf /etc/NetworkManager/conf.d/00-use-dnsmasq.conf
cp $vdrpath/conf/00-chronovdr.conf /etc/NetworkManager/dnsmasq.d/00-chronovdr.conf
sudo systemctl restart NetworkManager
fi

echo
echo '------------------------------------------------------'
echo configuration du système de nommage
echo

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

echo
echo '------------------------------------------------------'
echo fin de l\'installation
echo

echo "La configuration de chronoVDR nécessite un redémarage"
read -r -p "Voulez vous redémarrer maintenant ? : [o/n] " redemarrer
case "$redemarrer" in
[oO])
sudo reboot
;;
[nN])
;;
esac

