<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

/* 
 * Copyright (C) 2025 Gérard Léon
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
$str_log = "";
$str_id="";
$str_data=""; 

function write_log($log){
    $f = fopen("files/logs.txt", "a+") or die("Unable to open file!");
    fwrite($f, date('H:i:s')." - ".$log . "\n");
    fclose($f);   
}  

//lecture du ref_id passé en paramèrtre
if (isset($_GET["id"])) {
    $str_id = $_GET["id"];
    if (strlen($str_id)>0){
        $str_log = "id=".$str_id;
    }
}
if (isset($_GET["data"])) {
    $str_data = $_GET["data"];
    if (strlen($str_data)>0){
        $str_log .= " data=".$str_data;
    }
}

if (strlen($str_id)>0){
    //Si le fichier tagToChange est présent, il s'agit d'un enregistrement de ref_id pour un participant
    if (file_exists("files/tagToChange")) {
        
        //on récupère dans le fichier l'id du participant dont il faut changer le TAG
        $myfile = fopen("files/tagToChange", "r");
        $id_participant = substr(fgets($myfile), 0, -1);
        fclose($myfile);
        
        //on vérifie que le ref_id n'est pas déjà utilisée dans cette activité
        $result = $mysqli->query("SELECT id FROM participants WHERE ref_id='" . $str_id . "' "
                . "AND id_activite IN (SELECT id_activite from participants WHERE id = '" . $id_participant . "')");  
        if ($result->num_rows == 0) {
            //si le ref_id n'est pas utilisé, on le modifie et on détruit le fichier pour dire que tout c'est bien passé
            $mysqli->query("UPDATE participants SET ref_id = '" . $str_id . "' WHERE id = '" . $id_participant . "'");
            unlink("files/tagToChange");
        }else{
            $str_log .= " déjà utilisé";
            write_log($str_log);
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
            $val_data = ''; $ins_data='';
            if (strl($str_data)>0){
                $val_data = "','" . $str_data;
                $ins_data = ",data";
            }
                       
            //insertion des données
            $mysqli->query("INSERT INTO datas (id_activite,id_participant,temps".$ins_data.") VALUES "
                    . "('" . $id_activite . "','" . $id_participant . "','" . date('Y-m-d H:i:s') . $val_data . "')");
            
            //création d'un fichier contenant l'id de la dernière donnée pour identifier la dernière modif de la bdd
            $myfile = fopen("files/lastupdate", "w");
            fwrite($myfile, $mysqli->insert_id);
            fclose($myfile);

            echo "ok";
            return;
        }
        write_log($str_log);
    }
        
}else{
    $str_log .= "Err. requète ".$_SERVER['QUERY_STRING'];
    write_log($str_log);   
}


    
close_db($mysqli);
