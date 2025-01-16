<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

/* 
 * Copyright (C) 2024 gleon
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


require_once 'class/db.php';

if (isset($_GET["id"])) {
    $ref_id = $_GET["id"];
    if (file_exists("files/tagToChange")) {
        $myfile = fopen("files/tagToChange", "r");
        $id_participant = substr(fgets($myfile), 0, -1);
        fclose($myfile);
        //on essaye de retrouver l'id de l'activité
        $result = $mysqli->query("SELECT id_activite FROM participants WHERE id = '" . $id_participant . "'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            //on cherche si un autre participant a le même RFID
            $result = $mysqli->query("SELECT id FROM participants WHERE ref_id = '" . $ref_id . "' AND id_activite = '" . $row['id_activite'] . "'");
            if ($result->num_rows == 0) {
                //si le TAG n'est pas utilisé, on l'ajoute et on détruit le fichier pour dire que tout c'est bien passé
                $mysqli->query("UPDATE participants SET ref_id = '" . $ref_id . "' WHERE id = '" . $id_participant . "'");
                unlink("files/tagToChange");
            }
        }
    } else {

        $result = $mysqli->query("SELECT nb_max,temps_max,activites.id as ida ,participants.id as idp "
                . "FROM activites, participants "
                . "WHERE activites.etat = '2' "
                . "AND activites.id = participants.id_activite "
                . "AND ref_id = '".$_GET["id"]."'");
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id_activite = $row['ida'];
            $id_participant = $row['idp'];

            $str_data = ''; $ins_data='';
            if (isset($_GET["data"])) {
                $data = $_GET["data"];
                $str_data = "','" . $data;
                $ins_data = ",data";
            }

            if (isset($_GET["temps"])) {
                $str_temps = $_GET["temps"];
            }else{
                $str_temps = date('Y-m-d H:i:s');
            }

            $mysqli->query("INSERT INTO datas (id_activite,id_participant,temps".$ins_data.") VALUES "
                    . "('" . $id_activite . "','" . $id_participant . "','" . date('Y-m-d H:i:s') . $str_data . "')");
            
            $mysqli->query("UPDATE activites SET UPDATE_TIME='" . date('Y-m-d H:i:s') . "' WHERE id='".$id_activite."'");

            echo "ok";
        }
    }
}

close_db($mysqli);
