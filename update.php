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

$tabUrl =  $_SERVER [ 'REQUEST_URI' ] ;
$myfile = fopen("files/log.txt", "w") or die("Unable to open file!");
fwrite($myfile, $tabUrl . "\n");
fclose($myfile);

//lecture du ref_id passé en paramèrtre
if (isset($_GET["id"])) {
    $ref_id = $_GET["id"];
    
    //Si le fichier tagToChange est présent, il s'agit d'un enregistrement de ref_id pour un participant
    if (file_exists("files/tagToChange")) {
        
        //on récupère dans le fichier l'id du participant dont il faut changer le TAG
        $myfile = fopen("files/tagToChange", "r");
        $id_participant = substr(fgets($myfile), 0, -1);
        fclose($myfile);
        
        //on vérifie que le ref_id n'est pas déjà utilisée dans cette activité
        $result = $mysqli->query("SELECT id FROM participants WHERE ref_id='" . $ref_id . "' "
                . "AND id_activite IN (SELECT id_activite from participants WHERE id = '" . $id_participant . "')");  
        if ($result->num_rows == 0) {
            //si le ref_id n'est pas utilisé, on le modifie et on détruit le fichier pour dire que tout c'est bien passé
            $mysqli->query("UPDATE participants SET ref_id = '" . $ref_id . "' WHERE id = '" . $id_participant . "'");
            unlink("files/tagToChange");
        }
        
    //Sinon, c'est un ajout de données
    } else {
        //on récupère les données de l'activité en cours d'enregistrement (activites.etat = '2')
        //et on vérifie qye le ref_id du participant est bien dans cette activité
        $result = $mysqli->query("SELECT nb_max,temps_max,activites.id as ida ,participants.id as idp "
                . "FROM activites, participants "
                . "WHERE activites.etat = '2' "
                . "AND activites.id = participants.id_activite "
                . "AND ref_id = '".$_GET["id"]."'");
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id_activite = $row['ida'];
            $id_participant = $row['idp'];
            
            //si le champs data est présent, on prépare son insertion
            $str_data = ''; $ins_data='';
            if (isset($_GET["data"])) {
                $data = $_GET["data"];
                $str_data = "','" . $data;
                $ins_data = ",data";
            }
            
            //Si le champ remps est présent, on l'utilise, sinon on utiltise la date/heure du système
            if (isset($_GET["temps"])) {
                $str_temps = $_GET["temps"];
            }else{
                $str_temps = date('Y-m-d H:i:s');
            }
            
            //insertion des données
            $mysqli->query("INSERT INTO datas (id_activite,id_participant,temps".$ins_data.") VALUES "
                    . "('" . $id_activite . "','" . $id_participant . "','" . $str_temps . $str_data . "')");
            
            //création d'un fichier contenant l'id de la dernière donnée pour identifier la dernière modif de la bdd
            $myfile = fopen("files/lastupdate", "w");
            fwrite($myfile, $mysqli->insert_id);
            fclose($myfile);

            echo "ok";
        }
    }
}

close_db($mysqli);
