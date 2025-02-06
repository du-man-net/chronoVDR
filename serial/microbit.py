#!/usr/bin/env python3

# Ce programme est a l'ecoute de la MB maitre

import os
import serial
import serial.tools.list_ports as list_ports
import time
import datetime
import socket
import mysql.connector as connector

# Dossier de base pages html
BASE_HTML = "/var/www/html/chronoVDR/"

PID_MICROBIT = 516
VID_MICROBIT = 3368
TIMEOUT = 0.1
MYSQL_PASSWORD = ""

#====================================================
# Lecture du mt de passe de la base de donnée dans le fichier de conf
#====================================================
def get_password():
    with open(BASE_HTML + "config/mysql_password","r") as f:
        mysql_password = f.readline()
        mysql_password = mysql_password.replace("\n", "")
    f.close() 
    return mysql_password

#====================================================
# Connexion à la base de donnée
#====================================================
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

#====================================================
# Récupération de l'adresse IP
#====================================================
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

#====================================================
# Récupération de la connexion série
#====================================================
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

#====================================================
# Recherche de l'id de l'activite en cours
#====================================================
def find_rec_activite_id(cn,cur):
    query = "SELECT id FROM activites WHERE activites.etat = '2'"
    #print (query)
    try:
        cur.execute(query)
        row = cur.fetchone()
        if (row):
            id_activite = str(row[0])
            return id_activite

    except:
        print ("erreur SQL participants")
    
    return False

#====================================================
# REcherche si des datas existent pour l'activité
#====================================================
def find_datas_for_actiivte(cn,cur,id_activite):
    query = "SELECT datas.id FROM datas, activites WHERE datas.id_activite='"+id_activite+"'"
    #print (query)
    try:
        cur.execute(query)
        results = cur.fetchall()
        #print (len(results))
        if len(results)>0:
            return True

    except:
        print ("erreur SQL participants")
    
    return False

#====================================================
# Insert l'heure de dépaart pour tous les participants
#====================================================
def insert_data_for_all(cn,cur,id_activite):
    query = "INSERT INTO datas (id_activite,id_participant,temps) " + \
            "SELECT id_activite, participants.id, NOW() FROM participants " + \
            "WHERE participants.id_activite='"+id_activite+"'";  
    #print (query)
    #try:
    cur.execute(query)
    lastid = str(cur.lastrowid)
    cn.commit()
    return lastid
    #except:
        #print ("erreur SQL participants")
    
    return False

#====================================================
# lecture de l'id du participant dans le fichier tagTochange
#====================================================
def get_tag_to_change():
    with open(BASE_HTML + "/files/tagToChange","r") as f:
        id_participant = f.readline()
        #print (id_participant)
    f.close() 
    return id_participant

#====================================================
# supprimer le fichier tagTochange
#====================================================
def delete_tag_to_change():
    if os.path.isfile(BASE_HTML + "/files/tagToChange"):
        os.remove(BASE_HTML + "files/tagToChange")

#====================================================
# #on vérifie que le ref_id n'est pas déjà utilisé dans cette activité
#====================================================
def is_tag_alwready_used(cn,cur,str_id,id_participant):
    query = "SELECT id FROM participants WHERE ref_id='" + str_id + "' " + \
            "AND id_activite IN (SELECT id_activite from participants WHERE id = '" + id_participant + "')"
    #print (query)
    try:
        cur.execute(query)
        results = cur.fetchall()
        #print (len(results))
        if len(results)>0:
            return True

    except:
        print ("erreur SQL participants")

    return False

#====================================================
# on modifie le refid du participant 
#====================================================
def change_tag_participant(cn,cur,str_id,id_participant):               
    query = "UPDATE participants SET ref_id = '" + str_id + "' WHERE id = '" + id_participant + "'"
    #print (query)
    try:
        cur.execute(query)
        cn.commit()
       
    except:
        print ("erreur SQL participants")

#====================================================
# récuperation des infos concernant l'activite et le participant
#====================================================
def get_uid_infos(cn,cur,str_id):  
    query = "SELECT nb_max,temps_max,activites.id as ida ,participants.id as idp " + \
                "FROM activites, participants " + \
                "WHERE activites.etat = '2' " + \
                "AND activites.id = participants.id_activite " + \
                "AND ref_id = '" + str_id + "'"
    #print (query)
    try:
        cur.execute(query)
        row = cur.fetchone()
        return row

    except:
        print ("erreur SQL participants")
    
    return False

#====================================================
# insertion d'un nouvel enrengistrement
#====================================================
def insert_data_for_participant(cn,cur,id_activite,id_participant,str_data):  
    query = "INSERT INTO datas (id_activite,id_participant,temps,data) " + \
            "VALUES(" + str(id_activite) + "," + str(id_participant) + ",NOW(),'" + str_data + "')"
    #print (query)
    try:
        cur.execute(query)
        lastid = str(cur.lastrowid)
        cn.commit()
        return lastid

    except:
        print ("erreur SQL insert data")

    return False

#====================================================
# récuperation des infos concernant l'activite et le participant
#====================================================
def write_last_update(lastid):
    try:
        with open(BASE_HTML + "/files/lastupdate","w") as f:
            f.write(lastid)
        f.close() 
    except:
        print ("erreur d'accès au fichier lastupdate")

#====================================================
# on laisse une trace dans le fichier de log
#====================================================
def write_log(url):
    try:
        with open(BASE_HTML + "/files/logs.txt","w") as f:
            #print(url)
            f.write(url)
        f.close() 
    except:
        print ("erreur d'accès au fichier  de logs")

#====================================================
# Départ pour tout le monde si l'activité est vide
#====================================================
def start_for_all():
    cn = mysql_connect()
    cur  = cn.cursor()
    id_activite = find_rec_activite_id(cn,cur)
    if(id_activite):
        if(not find_datas_for_actiivte(cn,cur,id_activite)):
            lastid = 0

            lastid = insert_data_for_all(cn,cur,id_activite)
            cur.close()

            if lastid :
                write_last_update(lastid)
                accuse_rcp = "START\n"
                mb_serie.write(accuse_rcp.encode("utf-8"))

#====================================================
# traitement d'un d'une cmmande concernant un id
#====================================================
def insert_url(url):
    datas = url.split("&")
    str_id = ""
    if len(datas)>0 :
        str_id = str(datas[0])
    str_data = ""
    if len(datas)>1 :
        str_data = str(datas[1])
    
    if len(str_id) > 0:
        #print(str_id)
        #si le fichier tagToChange existe on est en mode insertion de tag
        if os.path.isfile(BASE_HTML + "/files/tagToChange"):
            #on récupère l'id du tag à modifier dans le fichier
            id_participant = get_tag_to_change()

            cn = mysql_connect()
            cur  = cn.cursor()
            #si le ref_id n'est pas utilisé, 
            if(not is_tag_alwready_used(cn,cur,str_id,id_participant)):
                #on le modifie pour l'utilisateur')
                change_tag_participant(cn,cur,str_id,id_participant)
                cur.close()
                #on détruit le fichier pour dire que tout c'est bien passé
                delete_tag_to_change()
                

        else:
            cn = mysql_connect()
            cur  = cn.cursor()
            row = get_uid_infos(cn,cur,str_id)

            if row :
                if (len(row)>3):
                    id_activite = row[2]
                    id_participant = row[3]
                    lastid = 0
                    
                    lastid = insert_data_for_participant(cn,cur,id_activite,id_participant,str_data)
                    cur.close()

                    if lastid :
                        #print(lastid)
                        write_last_update(lastid)
                        accuse_rcp = "#" + str_id + "\n"
                        mb_serie.write(accuse_rcp.encode("utf-8"))

    write_log(url)


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
        mb_serie.open()
        print('communication avec MB Maitre ouverte')
        msg_start = "CONNECT\n"
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
                elif strdatas == "START?":
                    start_for_all()
                elif strdatas[0:1] == "?":
                    # Reception d'un capteur
                    serial_datas = strdatas.split("?")
                    for serial_data in serial_datas:
                        if serial_data:
                            insert_url(serial_data)