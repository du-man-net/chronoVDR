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
from datetime import datetime


class VDR_tag:

    def __init__(self):
        self.path = "/var/www/html/chronoVDR/files/tagToChange"

    def exist(self):
        return os.path.isfile(self.path)

    def id_participant(self):
        with open(self.path, "r") as f:
            id_participant = f.readline()
            # print (id_participant)
        return id_participant

    def delete(self):
        if os.path.exists(self.path):
            os.remove(self.path)


class VDR_logs:

    def __init__(self):
        self.path = "/var/www/html/chronoVDR/files/logs.txt"
        self.logs = ""
        self.nbline = 0
    
    def write(self, log=""):
        #try:
        if len(log) == 0:
            log = self.logs
        now = datetime.now()
        dt_string = now.strftime("%H:%M:%S") + " - " + log + "\n"
        print(dt_string)

        if self.nbline > 50:
            with open(self.path, "r") as f:
                lines = f.readlines()                  
            with open(self.path, "w") as f:
                f.writelines(lines[-20:])
                f.write(dt_string)
            self.nbline == 0
        else:
            with open(self.path, "a") as f:
                f.write(dt_string)

        self.logs = ""
        self.nbline += 1

        #except:
            #print("erreur d'accès au fichier  de logs")

    def add(self, log):
        self.logs += log


def get_password():
    path = "/var/www/html/chronoVDR/config/mysql_password"
    with open(path, "r") as f:
        pw = f.readline()
        pw = pw.replace("\n", "")
    f.close()
    return pw


def cash_to_clear(module):
    path = "/var/www/html/chronoVDR/files/clearcache." + module
    if os.path.exists(path):
        os.remove(path)
        return True
