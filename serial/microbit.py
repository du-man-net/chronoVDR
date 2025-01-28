#!/usr/bin/env python3

# Ce programme est a l'ecoute de la MB maitre

import serial
import serial.tools.list_ports as list_ports
import time
import datetime
import socket
import mysql.connector as connector

# Dossier de base pages html
BASE_HTML = "/var/www/html/chronoVDR/"

# Detection automatique de la carte MB
PID_MICROBIT = 516
VID_MICROBIT = 3368
TIMEOUT = 0.1
MYSQL_PASSWORD = ""

def get_password():
    with open(BASE_HTML + "config/mysql_password","r") as f:
        mysql_password = f.readline()
        mysql_password = mysql_password.replace("\n", "")
    f.close() 
    return mysql_password

def mysql_connect():
    global MYSQL_PASSWORD

    if MYSQL_PASSWORD == "":
        MYSQL_PASSWORD = get_password()
    try:
        dataBase = connector.connect(
            host = "localhost",
            user = "root",
            password = MYSQL_PASSWORD,
            database = "chronoVDR"
        )
        return dataBase
    except:
        print ("connection error")
        exit(1)

def get_ip_address():
    # Detection acces internet
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect(("8.8.8.8", 80))
        mon_ip = s.getsockname()[0]
        s.close()
    except OSError:
        mon_ip = "172.16.1.1"
    return mon_ip

def find_comport(pid, vid, baud):
    ''' return a serial port '''
    ser_port = serial.Serial(timeout=TIMEOUT)
    ser_port.baudrate = baud
    ports = list(list_ports.comports())
    print('scanning ports')
    for p in ports:
        print('port: {}'.format(p))
        try:
            print('pid: {} vid: {}'.format(p.pid, p.vid))
        except AttributeError:
            continue
        if (p.pid == pid) and (p.vid == vid):
            print('found target device pid: {} vid: {} port: {}'.format(p.pid, p.vid, p.device))
            ser_port.port = str(p.device)
            return ser_port
    return None

def insert_data(url):
    datas = url.split(",")
    str_id = ""
    if len(datas)>0 :
        str_id = str(datas[0])
    str_data = ""
    if len(datas)>1 :
        str_data = str(datas[1])
         
    if(len(str_id) > 0):
        cn = mysql_connect()
        cur  = cn.cursor()
        query = "SELECT nb_max,temps_max,activites.id as ida ,participants.id as idp " + \
                    "FROM activites, participants " + \
                    "WHERE activites.etat = '2' " + \
                    "AND activites.id = participants.id_activite " + \
                    "AND ref_id = '" + str_id + "'"
        try:
            cur.execute(query)
            row = cur.fetchone()
        except:
            print ("erreur SQL participants")

        if row :
            if (len(row)>3):
                id_activite = row[2]
                id_participant = row[3]
                x = datetime.datetime.now()
                str_temps = x.strftime("%Y-%m-%d %H:%M:%S")
                lastid = 0

                query = "INSERT INTO datas (id_activite,id_participant,temps,data) " + \
                        "VALUES(" + str(id_activite) + "," + str(id_participant) + ",'" + str_temps + "','" + str_data + "')"
                try:
                    cur.execute(query)
                    lastid = str(cur.lastrowid)
                    cn.commit()
                    cur.close()
                except:
                    print ("erreur SQL insert data")

                if lastid :
                    try:
                        with open(BASE_HTML + "/files/lastupdate","w") as f:
                            f.write(lastid)
                        f.close() 
                        #print(lastid, "rec/maj")
                    except:
                        print ("erreur d'accès au fichier lastupdate")

                    accuse_rcp = "&" + str_id + "\n"
                    mb_serie.write(accuse_rcp.encode("utf-8"))



#
# Programme principal
#

while True:
    # Boucle d'attente MB
    print('Detection microbit')
    mb_serie = find_comport(PID_MICROBIT, VID_MICROBIT, 115200)
    if not mb_serie:
        print('microbit absente')
        time.sleep(5)
    else:
        print('ouverture de la communication avec MB Maitre')
        mb_serie.open()
        msg_start = "start\n"
        mb_serie.write(msg_start.encode("utf-8"))
        #
        # boucle principale
        #

        while True:
            # Atttente d'une consigne du maitre
            strdatas = mb_serie.readline().decode('utf-8')
            if strdatas:
                if strdatas == "IP?":
                    ip = "IP"+get_ip_address()+"\n"
                    #print(ip)
                    mb_serie.write(ip.encode("utf-8"))
                elif strdatas == "HEURE?":
                    x = datetime.datetime.now()
                    str_temps = "HEURE" + x.strftime("%H:%M:%S") + "\n"
                    mb_serie.write(str_temps.encode("utf-8"))
                elif strdatas[0:1] == "#":
                    # Reception d'un capteur
                    serial_datas = strdatas.split("#")
                    for serial_data in serial_datas:
                        if serial_data:
                            insert_data(serial_data)