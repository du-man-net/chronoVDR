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

import json
from http.server import BaseHTTPRequestHandler, HTTPServer
import threading

from VDR_mysql import VDR_mysql
from VDR_mqtt import VDR_mqtt
from VDR_txt import VDR_logs, VDR_tag


# ====================================================
# Implémentation de la fonction de répéttion
# ====================================================
def set_interval(func, sec):
    def func_wrapper():
        set_interval(func, sec)
        func()

    t = threading.Timer(sec, func_wrapper)
    t.start()
    return t


# ====================================================
# changement de tag
# ====================================================
def change_tag(message):

    serial_datas = message.split(":")
    if len(serial_datas) > 0:
        str_id = serial_datas[0]
        logs.add("newid=" + str_id)

        if len(str_id) > 0:
            # print("rfid lu")
            # si le fichier tagToChange existe on est en mode insertion de tag

            if tag.exist():
                # print("fichier tagToChange trouvé")
                # on récupère l'id du tag à modifier dans le fichier
                id_participant = tag.id_participant()
                # print(id_participant + " " + str_id)

                # si le ref_id n'est pas utilisé,
                if not mysql.is_tag_used(str_id):
                    # on le modifie pour l'utilisateur
                    mysql.change_tag_participant(str_id, id_participant)
                    # print("tag changé")
                    # on détruit le fichier pour dire que tout c'est bien passé
                    tag.delete()
                    print("fichier détruit")
                else:
                    print("tag déja utilisé")
                    logs.add(" déjà utilisé")

        return
    logs.write("Err. requête")


# ====================================================
# changement de tag
# ====================================================
def save_data(message):

    serial_datas = message.split(",")
    for serial_data in serial_datas:
        s_data = serial_data.split(":")
        str_id = s_data[0]
        logs.add("id=" + str_id)

        str_data = ""
        if len(s_data) > 1:
            str_data = s_data[1]
            logs.add(" data=" + str_data)

        if mysql.etat == 2:
            if mysql.id_activite != "0":
                if mysql.get_participant_id(str_id):
                    id_participants = mysql.id_participants[str_id]
                    insert_is_valid = False
                    if mysql.delais > 0:
                        if mysql.delais_respected(id_participants):
                            insert_is_valid = True
                    else:
                        insert_is_valid = True

                    if insert_is_valid:
                        mysql.insert_data(id_participants, str_data)
                    else:
                        logs.write("delais non respécté")

        return
        logs.write("Err. requête")


# ====================================================
# traitement d'un d'une commande concernant un id
# ====================================================
def on_mqtt_message(client, userdata, msg):
    
    try:
        message = msg.payload.decode("utf-8")
        print(message)
        if tag.exist():
            change_tag(message)

        else:
            if message == "START?":
                mysql.start_for_all()
            else:    
                save_data(message)

        logs.write()
        
    except Exception:
        pass


# ====================================================
# class serveru http sur le port 8000
# ====================================================

class RequestHandler(BaseHTTPRequestHandler):

    def end_headers(self):
        self.send_header("Access-Control-Allow-Origin", "*")
        self.send_header("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
        self.send_header("Access-Control-Allow-Headers", "Content-Type")
        self.send_header("Content-Type", "application/json")
        super().end_headers()
        
    def do_GET(self):
        self.send_response(200)
        self.end_headers()
        response = {
            "status": "ok",
            "message": "Serveur HTTP Python opérationnel",
            "path": self.path
        }
        self.wfile.write(json.dumps(response).encode("utf-8"))

    def do_OPTIONS(self):
        self.send_response(204)
        self.end_headers()
        
    def do_POST(self):
        self.send_response(200)
        self.end_headers()
        
        # Lecture du corps de la requête
        content_length = int(self.headers.get("Content-Length", 0))
        body = self.rfile.read(content_length)

        try:
            jsonRes = json.loads(body.decode("utf-8"))
            print("message http vert mqtt : " + str(jsonRes))

        except Exception:
            jsonRes = {
                "raw": body.decode("utf-8", errors="ignore")
            }

        response = {
            "status": "ok",
            "received": jsonRes
        }

        self.wfile.write(json.dumps(response).encode("utf-8"))

        mqtt.pub_topic = "vdr/" + str(jsonRes["topic"])
        mqtt.publish(jsonRes["message"])


# ====================================================
# main
# ====================================================

tag = VDR_tag()
mysql = VDR_mysql()
logs = VDR_logs()
mysql.connect()

mysql.get_activite_infos()
t = set_interval(mysql.get_activite_infos, 2)

mqtt = VDR_mqtt("http_agent")
mqtt.sub_topic = "vdr/pub"
mqtt.on_message = on_mqtt_message
mqtt.connect()

host = "0.0.0.0"
port = 8080
server = HTTPServer((host, port), RequestHandler)

print(f"Serveur démarré sur http://{host}:{port}")
print("Appuyez sur Ctrl+C pour arrêter.")

try:
    print("démarrage serveur http")
    server.serve_forever()
except KeyboardInterrupt:
    print("\nArrêt du serveur...")
    server.server_close()
