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


from datetime import datetime
import mysql.connector as connector
from VDR_txt import VDR_logs, cash_to_clear, get_password


class VDR_mysql:

    def __init__(self):

        self.BASE_HTML = "/var/www/html/chronoVDR/"
        self.logs = VDR_logs()

        # Configuration MySQL
        self.password = ""
        self.db = self.connect()
        self.db.autocommit = True
        self.cursor = self.db.cursor()

        self.id_activite = "0"
        self.delais = 0
        self.etat = 0
        self.id_participants = {}

    # ====================================================
    # Connexion à la base de donnée
    # ====================================================
    def connect(self):

        if self.password == "":
            self.password = get_password()

        try:
            # Configuration MySQL
            return connector.connect(
                host="localhost",
                user="root",
                password=self.password,
                database="chronoVDR",
            )
        except:
            self.logs.write("Err. connexion MariaDB ")

    # ====================================================
    # REcherche si des datas existent pour l'activité
    # ====================================================
    def is_activite_empty(self):
        query = (
            "SELECT datas.id FROM datas, participants WHERE id_participant = participants.id AND participants.id_activite='"
            + self.id_activite
            + "'"
        )
        # print(query)
        try:
            self.cursor.execute(query)
            results = self.cursor.fetchall()
            # print (len(results))
            if len(results) > 0:
                return False

        except:
            self.logs.write("Err. SQL " + query)

        return True

    # ====================================================
    # Départ pour tout le monde si l'activité est vide
    # ====================================================
    def start_for_all(self):
        
        if self.id_activite != "0":
            if self.etat > 1:
                if self.is_activite_empty():
                    self.insert_data_for_all()

    # ====================================================
    # Insert l'heure de dépaart pour tous les participants
    # ====================================================
    def insert_data_for_all(self):
        query = (
            "INSERT INTO datas (id_participant,temps) "
            + "SELECT id, NOW() FROM participants "
            + "WHERE participants.id_activite='"
            + self.id_activite
            + "'"
        )
        # print (query)
        try:
            self.cursor.execute(query)
            lastid = str(self.cursor.lastrowid)
            self.db.commit()
            return lastid
        except:
            self.logs.write("Err. SQL " + query)

        return False

    # ====================================================
    # on vérifie que le ref_id n'est pas déjà utilisé dans cette activité
    # ====================================================
    def is_tag_used(self, str_id):
        query = (
            "SELECT participants.id FROM participants, activites "
            + "WHERE id_activite = activites.id "
            + "AND etat>0 "
            + "AND ref_id='"
            + str_id
            + "'"
        )
        # print(query)
        try:
            self.cursor.execute(query)
            if len(self.cursor.fetchall()) > 0:
                return True

        except:
            self.logs.write("Err. SQL " + query)

        return False

    # ====================================================
    # on modifie le refid du participant
    # ====================================================
    def change_tag_participant(self, str_id, id_participant):
        # on récupère l'ancien tag pour gérer le cache
        query = (
            "SELECT ref_id "
            + "FROM participants "
            + "WHERE id='"
            + id_participant
            + "' "
        )
        # print(query)
        try:
            self.cursor.execute(query)
            row = self.cursor.fetchone()
            if row:
                # print("participant trouve")
                old_strid = row[0]
                # si le tag est dans lel cache, on supprime le cache
                if old_strid in self.id_participants:
                    del self.participants[old_strid]
                    # print("cache mis a jour")
        except:
            self.logs.write("Err. SQL " + query)

        query = (
            "UPDATE participants SET ref_id = '"
            + str_id
            + "' WHERE id = '"
            + id_participant
            + "'"
        )
        # print (query)
        try:
            self.cursor.execute(query)
            self.db.commit()

        except:
            self.logs.write("Err. SQL " + query)

    # ====================================================
    # récuperation des infos concernant l'activite et le participant
    # ====================================================
    def get_activite_infos(self):
        
        if cash_to_clear():
            self.etat = 0
            self.id_activite = "0"
            print("clear")
        
        if self.id_activite == "0":
            query = "SELECT id,etat,delais_min FROM activites WHERE etat > 0 "
            try:
                self.cursor.execute(query)
                row = self.cursor.fetchone()
                if row:
                    self.id_participants = {}
                    self.id_activite = str(row[0])
                    self.etat = int(row[1])
                    self.delais = int(row[2])
                    return True
                    
            except:
                self.logs.write("Err. SQL " + query)
                self.id_activite = "0"
                self.id_participants = {}
                self.etat = 0
                self.delais = 0

        return False

    # ====================================================
    # récuperation des infos concernant l'activite et le participant
    # ====================================================
    def get_participant_id(self, str_id):

        if str_id in self.id_participants:
            return True

        query = (
            "SELECT id "
            + "FROM participants "
            + "WHERE id_activite='"
            + self.id_activite
            + "' AND ref_id='"
            + str_id
            + "' "
        )
        # print(query)
        try:
            self.cursor.execute(query)
            row = self.cursor.fetchone()
            if row:
                # print("participant trouve")
                id = int(row[0])
                ids = self.get_association_id(id)
                if ids:
                    self.id_participants[str_id] = ids
                else:
                    self.id_participants[str_id] = [id]
                return True
        except:
            self.logs.write("Err. SQL " + query)

        return False

    # ====================================================
    # récuperation des infos concernant l'activite et le participant
    # ====================================================
    def get_association_id(self, id):
        query = (
            "SELECT id "
            + "FROM participants "
            + "WHERE id_activite ='"
            + self.id_activite
            + "' "
            + "AND association = '"
            + str(id)
            + "'"
        )
        # print(query)
        try:
            self.cursor.execute(query)
            rows = self.cursor.fetchall()
            if rows:
                ids = []
                for row in rows:
                    ids.append(row[0])
                return ids

        except:
            self.logs.write("Err. SQL " + query)

        return False

    # ====================================================
    # vérifie si un enregistrement plus récent que delais existe
    # ====================================================
    def delais_respected(self, id_participants):
        query = (
            "SELECT * FROM ( "
            + "SELECT MAX(temps) as tm "
            + "FROM datas "
            + "WHERE id_participant = '"
            + str(id_participants[0])
            + "') last_entry "
            + "WHERE last_entry.tm > (NOW() - INTERVAL "
            + str(self.delais)
            + " SECOND)"
        )
        # print(query)
        try:
            self.cursor.execute(query)
            row = self.cursor.fetchone()
            if row:
                if len(row) > 0:
                    return False
        except:
            self.logs.write("Err. SQL " + query)

        return True

    # ====================================================
    # insertion d'un nouvel enrengistrement
    # ====================================================
    def insert_data(self, id_participants, str_data):

        values = []
        datas = len(str_data)>0
        strnow = datetime.now().strftime("%Y-%m-%d %H:%M:%S.%f")
        if datas:
            query = "INSERT INTO datas (id_participant,temps,data) VALUES (%s, %s, %s)"
        else:
            query = "INSERT INTO datas (id_participant,temps) VALUES (%s, %s)"

        for id in id_participants:
            str_data = str_data.rstrip("\r")
            if datas:
                value = (str(id), strnow[:-3], str_data)
            else:
                value = (str(id), strnow[:-3])
            values.append(value)

        try:
            self.cursor.executemany(query, values)
            self.db.commit()

        except:
            self.logs.write("Err. SQL " + query)

        return False
