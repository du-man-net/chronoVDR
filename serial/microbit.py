#!/usr/bin/env python3


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


import os
import socket
import time
from datetime import datetime
# from time import strftime
from numpy import size

import mysql.connector as connector
import serial
import serial.tools.list_ports as list_ports

# Dossier de base pages html
BASE_HTML = "/var/www/html/chronoVDR/"

PID_MICROBIT = 516
VID_MICROBIT = 3368
TIMEOUT = 0.1
MYSQL_PASSWORD = ""

id_activite_cache = 0
delais_cache = 0
id_participants_cache = dict()


# ====================================================
# Lecture du mt de passe de la base de donnée dans le fichier de conf
# ====================================================
def get_password():
    with open(BASE_HTML + "config/mysql_password", "r") as f:
        mysql_password = f.readline()
        mysql_password = mysql_password.replace("\n", "")
    f.close()
    return mysql_password


# ====================================================
# Connexion à la base de donnée
# ====================================================
def mysql_connect():
    global MYSQL_PASSWORD

    if MYSQL_PASSWORD == "":
        MYSQL_PASSWORD = get_password()
    try:
        dataBase = connector.connect(
            host="localhost", user="root", password=MYSQL_PASSWORD, database="chronoVDR"
        )
        return dataBase
    except:
        write_log("Err. connexion MariaDB")
        exit(1)


# ====================================================
# Récupération de l'adresse IP
# ====================================================
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


# ====================================================
# Récupération de la connexion série
# ====================================================
def find_comport(pid, vid, baud):
    # return a serial port
    ser_port = serial.Serial(timeout=TIMEOUT)
    ser_port.baudrate = baud
    ports = list(list_ports.comports())
    # print('scanning ports')
    for p in ports:
        write_log("port: {}".format(p))
        try:
            write_log("pid: {} vid: {}".format(p.pid, p.vid))
        except AttributeError:
            continue
        if (p.pid == pid) and (p.vid == vid):
            write_log(
                "Périphérique trouvé pid: {} vid: {} port: {}".format(
                    p.pid, p.vid, p.device
                )
            )
            ser_port.port = str(p.device)
            return ser_port
    return None


# ====================================================
# REcherche si des datas existent pour l'activité
# ====================================================
# def find_datas_for_activite(id_activite):
#    path = BASE_HTML + "datas/" + id_activite
#    if not os.path.exists(path):
#        return False
#    else:
#        if len(os.listdir(path)) == 0:
#            return False
#    return True


def find_datas_for_activite(cn, cur, id_activite):
    query = (
        "SELECT datas.id FROM datas, participants WHERE id_participant = participants.id AND participants.id_activite='"
        + id_activite
        + "'"
    )
    # print (query)
    try:
        cur.execute(query)
        results = cur.fetchall()
        # print (len(results))
        if len(results) > 0:
            return True

    except:
        write_log("Err. SQL " + query)

    return False


# ====================================================
# Insert l'heure de dépaart pour tous les participants
# ====================================================
# def insert_data_for_all(cn, cur, id_activite):
#    path = BASE_HTML + "datas/" + id_activite
#    if not os.path.exists(path):
#        os.makedirs(path)
#
#    query = "SELECT id FROM participants WHERE id_activite='" + id_activite + "'"
#    try:
#        cur.execute(query)
#        participants = cur.fetchall()
#        for participant in participants:
#            with open(path + "/" + participant, "w", encoding="utf8") as f:
#                f.write(datetime.now().strftime("%Y/%m/%d %H:%M:%S.%f")[:-3])
#                f.write(" ")
#                f.write("0")
#                f.write("\n")
#        return True
#
#    except:
#        write_log("Err. SQL " + query)
#
#    return False


def insert_data_for_all(cn, cur, id_activite):
    query = (
        "INSERT INTO datas (id_participant,temps) "
        + "SELECT id, NOW() FROM participants "
        + "WHERE participants.id_activite='"
        + id_activite
        + "'"
    )
    # print (query)
    try:
        cur.execute(query)
        lastid = str(cur.lastrowid)
        cn.commit()
        return lastid
    except:
        write_log("Err. SQL " + query)

    return False


# ====================================================
# lecture de l'id du participant dans le fichier tagTochange
# ====================================================
def get_tag_to_change():
    with open(BASE_HTML + "files/tagToChange", "r") as f:
        id_participant = f.readline()
        # print (id_participant)
    return id_participant


# ====================================================
# supprimer le fichier tagTochange
# ====================================================
def delete_tag_to_change():
    if os.path.exists(BASE_HTML + "files/tagToChange"):
        os.remove(BASE_HTML + "files/tagToChange")
        # print("File " + BASE_HTML + "files/tagToChange deleted successfully.")
    # else:
    # print("File " + BASE_HTML + "files/tagToChange not found.")


# ====================================================
# on vérifie que le ref_id n'est pas déjà utilisé dans cette activité
# ====================================================
def is_tag_alwready_used(cn, cur, str_id, id_participant):
    query = (
        "SELECT id FROM participants WHERE ref_id='"
        + str_id
        + "' "
        + "AND id_activite IN (SELECT id_activite from participants WHERE id = '"
        + id_participant
        + "')"
    )
    # print (query)
    try:
        cur.execute(query)
        results = cur.fetchall()
        # print (len(results))
        if len(results) > 0:
            return True

    except:
        write_log("Err. SQL " + query)

    return False


# ====================================================
# on modifie le refid du participant
# ====================================================
def change_tag_participant(cn, cur, str_id, id_participant):
    query = (
        "UPDATE participants SET ref_id = '"
        + str_id
        + "' WHERE id = '"
        + id_participant
        + "'"
    )
    # print (query)
    try:
        cur.execute(query)
        cn.commit()

    except:
        write_log("Err. SQL " + query)


# ====================================================
# récuperation des infos concernant l'activite et le participant
# ====================================================
def get_activite_infos(cn, cur):
    global id_activite_cache
    global delais_cache

    query = (
        "SELECT nb_max,temps_max,delais_min,id "
        + "FROM activites "
        + "WHERE etat = '2' "
    )
    # print(query)
    try:
        cur.execute(query)
        row = cur.fetchone()
        if row:
            if len(row) > 2:
                if id_activite_cache != str(row[3]):
                    id_participants_cache.clear()
                delais_cache = int(row[2])
                id_activite_cache = str(row[3])
                return True
    except:
        write_log("Err. SQL " + query)

    return False


# ====================================================
# récuperation des infos concernant l'activite et le participant
# ====================================================
def get_participant_id(cn, cur, str_id, id_activite):
    global id_participants_cache

    if str_id in id_participants_cache:
        return True
    query = (
        "SELECT id "
        + "FROM participants "
        + "WHERE id_activite ='" + id_activite + "' "
        + "AND ref_id = '" + str_id + "'"
    )
    # print(query)
    try:
        cur.execute(query)
        row = cur.fetchone()
        if row:
            if len(row) > 0:
                id = int(row[0])
                ids = get_association_id(cn, cur, id, id_activite)
                if ids:
                    id_participants_cache[str_id] = ids
                else:
                    id_participants_cache[str_id] = [id]
                return True
    except:
        write_log("Err. SQL " + query)

    return False

# ====================================================
# récuperation des infos concernant l'activite et le participant
# ====================================================
def get_association_id(cn, cur, id, id_activite):
    query = (
        "SELECT id "
        + "FROM participants "
        + "WHERE id_activite ='" + id_activite + "' "
        + "AND association = '" + str(id) + "'"
    )
    # print(query)
    try:
        cur.execute(query)
        rows = cur.fetchall()
        if rows:
            ids = []
            for row in rows:
                ids.append(row[0])
            return ids

    except:
        write_log("Err. SQL " + query)

    return False

# ====================================================
# vérifie si un enregistrement plus récent que delais existe
# ====================================================
# def delais_respected(id_activite, id_participant, delais):
#
#    path = BASE_HTML + "datas/" + id_activite + "/" + id_participant
#    if os.path.exists(path):
#        with open(
#            path,
#            "r",
#            encoding="utf8",
#        ) as f:
#            for ligne in f.readlines()[-1:]:
#                lastime = datetime.strptime(ligne[:19], "%Y/%m/%d %H:%M:%S")
#                now = datetime.now()
#                difference = now - lastime
#                if difference.total_seconds() < delais:
#                    return False
#    return True


def delais_respected(cn, cur, id_participants, delais):
    query = (
        "SELECT * FROM ( "
        + "SELECT MAX(temps) as tm "
        + "FROM datas "
        + "WHERE id_participant = '"
        + str(id_participants[0])
        + "') last_entry "
        + "WHERE last_entry.tm > (NOW() - INTERVAL "
        + str(delais)
        + " SECOND)"
    )
    # print (query)
    try:
        cur.execute(query)
        row = cur.fetchone()
        if row:
            if len(row) > 0:
                return False
    except:
        write_log("Err. SQL " + query)

    return True


# ====================================================
# insertion d'un nouvel enrengistrement
# ====================================================
# def insert_data_for_participant(id_activite, id_participant, str_data):
#    path = BASE_HTML + "datas/" + id_activite
#    if not os.path.exists(path):
#        os.makedirs(path)
#
#    path = path + "/" + id_participant
#    with open(path, "a", encoding="utf8") as f:
#        f.write(datetime.now().strftime("%Y/%m/%d %H:%M:%S.%f")[:-3])
#        f.write(" ")
#        f.write(str_data)
#        f.write("\n")


def insert_data_for_participant(cn, cur, id_participants, str_data):

    query = "INSERT INTO datas (id_participant,temps,data) VALUES (%s, %s, %s)"
    values = []
    strnow = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    for id in id_participants:
        value = (str(id), strnow, str_data)
        values.append(value)

    # print (query)
    try:
        cur.executemany(query, values)
        lastid = str(cur.lastrowid)
        cn.commit()
        return lastid

    except:
        write_log("Err. SQL " + query)

    return False


# ====================================================
# on met à jour l'id du dernier enregistrement dans lasupdate
# ====================================================
def write_last_update(lastid):
    try:
        with open(BASE_HTML + "files/lastupdate", "w") as f:
            f.write(lastid)
    except:
        write_log("Err. accès au fichier lastupdate")


# ====================================================
# on laisse une trace dans le fichier de log
# ====================================================
def write_log(log):
    try:
        now = datetime.now()
        dt_string = now.strftime("%H:%M:%S")
        with open(BASE_HTML + "files/logs.txt", "a") as f:
            f.write(dt_string + " : " + log + "\n")
    except:
        print("erreur d'accès au fichier  de logs")


# ====================================================
# Départ pour tout le monde si l'activité est vide
# ====================================================
def start_for_all():
    cn = mysql_connect()
    cur = cn.cursor()
    row = get_activite_infos(cn, cur)
    id_activite = str(row[0])
    cur.close()
    if id_activite:
        if not find_datas_for_activite(id_activite):
            insert_data_for_all(id_activite)
            write_last_update(0)
            accuse_rcp = "START\n"
            mb_serie.write(accuse_rcp.encode("utf-8"))


# ====================================================
# traitement d'un d'une commande concernant un id
# ====================================================
def insert_url(url):
    global id_participants_cache
    global id_activite_cache
    global delais_cache

    datas = url.split("&")
    strlog = ""

    str_id = ""
    temp_str = ""
    if len(datas) > 0:
        temp_str = str(datas[0])
        if len(temp_str) > 0:
            str_id = temp_str
            strlog = "id=" + str_id

    str_data = "0"
    temp_str = ""
    if len(datas) > 1:
        temp_str = str(datas[1])
        if len(temp_str) > 0:
            str_data = temp_str
            strlog += " data=" + str_data

    if len(str_id) > 0:
        # print("rfid lu")
        # si le fichier tagToChange existe on est en mode insertion de tag
        if os.path.isfile(BASE_HTML + "files/tagToChange"):
            # print("fichier tagToChange trouvé")
            # on récupère l'id du tag à modifier dans le fichier
            id_participant = get_tag_to_change()
            # print(id_participant + " " + str_id)
            cn = mysql_connect()
            cur = cn.cursor()
            # si le ref_id n'est pas utilisé,
            if not is_tag_alwready_used(cn, cur, str_id, id_participant):
                # on le modifie pour l'utilisateur
                change_tag_participant(cn, cur, str_id, id_participant)
                # print("tag changé")
                cur.close()
                # on détruit le fichier pour dire que tout c'est bien passé
                delete_tag_to_change()
                # print("fichier détruit")
            else:
                # print("tag déja utilisé")
                strlog += " déjà utilisé"

        else:
            # print("debut enregistrement")
            cn = mysql_connect()
            cur = cn.cursor()
            if get_activite_infos(cn, cur):
                delais = delais_cache
                id_activite = id_activite_cache
                if get_participant_id(cn, cur, str_id, id_activite):
                    id_participants = id_participants_cache[str_id]

                    insert_is_valid = False
                    if delais > 0:
                        if delais_respected(cn, cur, id_participants, delais):
                            insert_is_valid = True
                    else:
                        insert_is_valid = True
                        
                    if insert_is_valid:
                        lastid = insert_data_for_participant(cn, cur, id_participants, str_data)
                        cur.close()

                        if lastid:
                            write_last_update(lastid)
                    else:
                        write_log("delais non respécté")
                        # print("delais non respécté")

        write_log(strlog)
        accuse_rcp = "#" + str_id + "\n"
        mb_serie.write(accuse_rcp.encode("utf-8"))
        return

    write_log("Err. requête - ?" + url)


#
# Programme principal
#
write_log("Lancement du script série")
while True:
    # Boucle d'attente MB
    # print("Detection microbit")
    mb_serie = find_comport(PID_MICROBIT, VID_MICROBIT, 115200)
    if not mb_serie:
        write_log("pas de Micro:bit connecté")
        time.sleep(2)
    else:
        mb_serie.open()
        write_log("Essais de connexion à Micro:bit")
        msg_start = "CONNECT\n"

        ok = False
        while not ok:
            mb_serie.write(msg_start.encode("utf-8"))
            time.sleep(0.1)
            strdatas = mb_serie.readline().decode("utf-8")
            if strdatas:
                if strdatas == "ok":
                    write_log("Connexion à Micro:bit réussie")
                    ok = True
        #
        # boucle principale
        #

        while True:
            # Atttente d'une consigne du maitre
            strdatas = mb_serie.readline().decode("utf-8")
            if strdatas:
                # print(strdatas)
                if strdatas[0:1] == "?":
                    serial_datas = strdatas.split("?")
                    for serial_data in serial_datas:
                        if serial_data:
                            insert_url(serial_data)
                elif strdatas == "IP?":
                    ip = "IP" + get_ip_address() + "\n"
                    # print("ip" + ip)
                    mb_serie.write(ip.encode("utf-8"))
                elif strdatas == "START?":
                    start_for_all()