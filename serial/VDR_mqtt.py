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


import time

import paho.mqtt.client as mqtt


class VDR_mqtt:

    def __init__(self, client_id):

        # Configuration MQTT
        self.mqtt_host = "172.16.1.1"
        self.mqtt_port = 1883

        # Client MQTT
        self.client = mqtt.Client(
            client_id=client_id,
            callback_api_version=mqtt.CallbackAPIVersion.VERSION2,
        )
        self.client.username_pw_set('chronovdr', '12345678')
        self.client.on_connect = self.on_connect
        
    ###########################################################

    def connect(self):

        try:
            print("Connexion au broker...")

            self.client.connect(self.mqtt_host, self.mqtt_port, 60)

            # boucle MQTT
            self.client.loop_start()

        except Exception as e:

            print("Broker indisponible :", e)

    ###########################################################

    def on_connect(self, client, userdata, flags, reason_code, properties):

        if reason_code == 0:
            print("Connecté au broker")
            client.subscribe(self._sub_topic)
            print("abonné mqtt : " + self._sub_topic)
        else:
            print("Erreur de connexion :", reason_code)

    ###########################################################

    @property
    def on_message(self):
        pass

    @on_message.setter
    def on_message(self, on_msg):
    
        self.client.on_message = on_msg
        
    ###########################################################
    
    @property
    def sub_topic(self):
        return self._sub_topic
        
    @sub_topic.setter
    def sub_topic(self, topic):
    
        self._sub_topic = topic

    ###########################################################
    
    @property
    def pub_topic(self):
        return self._pub_topic
        
    @pub_topic.setter
    def pub_topic(self, topic):
    
        self._pub_topic = topic
        
    ###########################################################

    def publish(self, message):

        self.client.publish(self._pub_topic, message, qos=1)


