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
import socket


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
        client.subscribe("vdr/mbit")
        print("Agent série connecté a vdr/mbit")
    else:
        print("Erreur de connexion :", reason_code)


# ====================================================
# traitement d'un message provenant de rqtt
# ====================================================
def on_mqtt_message(client, userdata, msg):

    global serial
    message = msg.payload.decode("utf-8")
    # print("message mqtt vers microbit : " + message)
    serial.write(message + "\n")
    logs.write("vdr/mbit : " + message)


# ====================================================
# traitement d'un message provenant de la liaison série microbit
# ====================================================
def on_serial_message(msg):

    global serial
    global mqtt

    # print("message microbit vers mqtt : " + msg)
    msg = msg.strip()
    serial_datas = msg.split(":", 1)

    topic = serial_datas[0]
    if len(serial_datas) > 1:
        message = serial_datas[1]

    if topic == "IP?":
        ip = get_ip_address() + "\n"
        serial.write(ip)
        logs.write("demande d'adresse IP")
    elif topic == "START?":
        serial.write("START")
        mqtt.pub_topic = "vdr/pub"
        mqtt.publish(topic)
        logs.write("Démarrage pour tous !")
    elif topic == "pub":
        mqtt.pub_topic = "vdr/pub"
        mqtt.publish(message)
        s_data = message.split(":")
        str_id = s_data[0]
        accuse_rcp = "#" + str_id + "\n"
        serial.write(accuse_rcp)
    elif topic == "mbit":
        mqtt.pub_topic = "vdr/mbit"
        mqtt.publish(message)
    else:
        mqtt.pub_topic = "vdr/" + topic
        mqtt.publish(message)
        logs.write("de microbit vers vdr/" + topic + " : " + message)


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
