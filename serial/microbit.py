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


from VDR_mqtt import VDR_mqtt
from VDR_serial import VDR_serial
from VDR_txt import (VDR_logs, VDR_tag)


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
# traitement d'un message provenant de rqtt
# ====================================================
def on_mqtt_connect(client, userdata, flags, reason_code, properties):

    if reason_code == 0:
        print("Connecté au broker")
        client.subscribe("vdr/mbit")
    else:
        print("Erreur de connexion :", reason_code)
            
# ====================================================
# traitement d'un message provenant de rqtt
# ====================================================
def on_mqtt_message(client, userdata, msg):

    global serial
    message = msg.payload.decode("utf-8")
    #print("message mqtt vers microbit : " + message)
    serial.write(message + "\n")

# ====================================================
# traitement d'un message provenant de la liaison série microbit
# ====================================================
def on_serial_message(msg):
    
    global serial
    global mqtt
    topic = ""
    name= ""
    value= ""
    #print("message microbit vers mqtt : " + msg)
    msg = msg.strip()
    serial_datas = msg.split(":",1)
    
    topic = serial_datas[0]
    if len(serial_datas) > 1:
        msg = serial_datas[1]
        
    if topic == "IP?":
        ip = get_ip_address() + "\n"
        serial.write(ip)
    elif topic == "START?":
        mqtt.publish(msg)
    elif topic == "pub":
        mqtt.pub_topic = "vdr/pub"
        mqtt.publish(msg)
        s_data = msg.split(":")
        str_id = s_data[0]
        accuse_rcp = "#" + str_id + "\n"
        serial.write(accuse_rcp)
    elif topic == "mbit":
        mqtt.pub_topic = "vdr/mbit"
        mqtt.publish(msg)
    else:
        print("vdr/" + topic + " - " + msg)
        mqtt.pub_topic = "vdr/" + topic
        mqtt.publish(msg)



# ====================================================
# main
# ====================================================

tag = VDR_tag()
logs = VDR_logs()

serial = VDR_serial()
serial.on_message = on_serial_message

mqtt = VDR_mqtt("mbit_agent")
mqtt.sub_topic = "vdr/mbit"
mqtt.pub_topic = "vdr/pub"
mqtt.on_message = on_mqtt_message
mqtt.connect()

serial.listen()