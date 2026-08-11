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
from datetime import datetime
import socket
import serial.tools.list_ports as list_ports
import serial
from VDR_txt import VDR_logs

class VDR_serial:

    def __init__(self):
        self.PID_MICROBIT = 516
        self.VID_MICROBIT = 3368
        self.TIMEOUT = 0.1
        self.baud = 115200
        self.serial = None
        self._on_message = None
        
        self.logs = VDR_logs()
        self.find_com_port()
        self.connect()
        # self.listen()
        
    # ====================================================
    # Récupération du port série
    # ====================================================
    def find_com_port(self):
        while True:
            # return a serial port
            self.serial = serial.Serial(timeout=self.TIMEOUT)
            self.serial.baudrate = self.baud
            ports = list(list_ports.comports())
            # print('scanning ports')
            for p in ports:
                self.logs.write("port: {}".format(p))
                try:
                    self.logs.write("pid: {} vid: {}".format(p.pid, p.vid))
                except AttributeError:
                    continue
                if (p.pid == self.PID_MICROBIT) and (p.vid == self.VID_MICROBIT):
                    self.logs.write(
                        "Périphérique trouvé pid: {} vid: {} port: {}".format(
                            p.pid, p.vid, p.device
                        )
                    )
                    self.serial.port = str(p.device)
                    time.sleep(2)   
                    self.serial.open()
                    self.logs.write("Essais de connexion à Micro:bit")
                    return

                time.sleep(1)              
                     
    # ====================================================
    # essais de com avec le microbit
    # ====================================================
    def connect(self):
        while True:
            msg_start = "CONNECT\n"
            self.write(msg_start)
            time.sleep(0.1)
            strdatas = ""
            strdatas = self.read()
            if strdatas:
                if strdatas == "OK":
                    self.logs.write("Connexion à Micro:bit réussie")
                    break

    # ====================================================
    # écoute sur le port série. 
    # Quand un message arrive il est redirigé vers la fonction
    # de callback si elle affectée
    # ====================================================
    def listen(self):
        while True:
            strdatas = ""
            # Atttente d'une consigne du maitre
            strdatas = self.read()  # Read bytes from serial port
            if strdatas:
                if self._on_message:
                    self._on_message(strdatas)

    # ====================================================
    # propriété callback on_message
    # ====================================================
    @property
    def on_message(self):
        return self._on_message
        
    @on_message.setter
    def on_message(self, callback):
        self._on_message = callback

    # ====================================================
    # read data
    # ====================================================
    def read(self):
        res = self.serial.readline().decode("utf-8")
        if res:
            return res.lstrip('\x00').rstrip('\n')

    # ====================================================
    # write data
    # ====================================================
    def write(self, data):
        self.serial.write(data.encode("utf-8"))
