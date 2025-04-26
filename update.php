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
$str_id = "";
$str_data = "";

//================================================
// écriture des donnés dans le fichier de log
//================================================
function get_tag_to_change() {
    if (file_exists("files/tagToChange")) {
        //on récupère dans le fichier l'id du participant dont il faut changer le TAG
        $myfile = fopen("files/tagToChange", "r");
        $id_participant = substr(fgets($myfile), 0, -1);
        fclose($myfile);
        return $id_participant;
    }
    return false;
}

//================================================
// Supprimer le fichier tag_to change
//================================================
function delete_tag_to_change() {
    if (file_exists("files/tagToChange")) {
        unlink("files/tagToChange");
    }
}

//================================================
// on vérifie que le ref_id n'est pas déjà utilisé dans cette activité
//================================================
function is_tag_alwready_used($str_id,$id_participant) {
    global $mysqli;
    //on vérifie que le ref_id n'est pas déjà utilisée dans cette activité
    $result = $mysqli->query("SELECT id FROM participants WHERE ref_id='" . $str_id . "' "
            . "AND id_activite IN (SELECT id_activite from participants WHERE id = '" . $id_participant . "')");
    if ($result->num_rows > 0) {
        return true;
    }
    return false;
}

//================================================
// on vérifie que le ref_id n'est pas déjà utilisé dans cette activité
//================================================
function change_tag_participant($str_id,$id_participant) {
    global $mysqli;
    $mysqli->query("UPDATE participants SET ref_id = '" . $str_id . "' WHERE id = '" . $id_participant . "'");
}

//================================================
// on vérifie que le ref_id n'est pas déjà utilisé dans cette activité
//================================================
function get_activite_infos() {
    global $mysqli;
    $result = $mysqli->query("SELECT nb_max,temps_max,delais_min,id "
            . "FROM activites "
            . "WHERE etat = '2'");

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row;
    }
    return false;   
}
//====================================================
//récuperation des infos concernant l'activite et le participant
//====================================================
function get_participant_id($str_id, $id_activite) {
    global $mysqli;
    $result = $mysqli->query("SELECT id FROM participants "
            . "WHERE id_activite ='".$id_activite."' "
            . "AND ref_id = '".$str_id."'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row['id'];
        $ids = get_association_id($id, $id_activite);
        if ($ids){
            return $ids;
        }else{
            return array($id);
        }
    }
    return false;
}

//====================================================
//récuperation des infos concernant l'activite et le participant
//====================================================
function get_association_id($id, $id_activite) {
    global $mysqli;
    $ids = [];
    $result = $mysqli->query("SELECT id FROM participants "
            . "WHERE id_activite ='".$id_activite."' "
            . "AND association = '".$id."'");
    if ($result->num_rows > 0) {
        foreach($result as $row){
            $ids[] = $row['id'];
        }
        return $ids;
    }
    return false;
}
    
//====================================================
//vérifie si un enregistrement plus récent que delais existe
//====================================================
function delais_respected($id_participants, $delais) {
    global $mysqli;
    $result = $mysqli->query("SELECT * FROM "
                . "(SELECT MAX(temps) as tm "
                . "FROM datas "
                . "WHERE id_participant = '".$id_participants[0]."') last_entry "
            . "WHERE last_entry.tm > (NOW() - INTERVAL ".$delais." SECOND)");
    if ($result->num_rows > 0) {
        return false;
    }
    return true;
} 
    
//====================================================
//insertion d'un nouvel enrengistrement
//====================================================
function insert_data_for_participant($id_participants, $data) {
    global $mysqli;
    $query = "INSERT INTO datas (id_participant,temps,data) VALUES ";
    foreach($id_participants as $id){
        $query .= "('".$id."', '".date('Y-m-d H:i:s')."', '".$data."'),";
    }
    $query = substr($query, 0, -1);
    $mysqli->query($query);
    return $mysqli->insert_id;
}

//====================================================
//insertion d'un nouvel enrengistrement
//====================================================
function write_last_update($lastid) {
    $f = fopen("files/lastupdate", "w") or die("Unable to open file!");
    fwrite($f, $lastid);
    fclose($f);
}

//================================================
// écriture des donnés dans le fichier de log
//================================================
function write_log($log) {
    $f = fopen("files/logs.txt", "a+") or die("Unable to open file!");
    fwrite($f, date('H:i:s') . " - " . $log . "\n");
    fclose($f);
}

//================================================
// traitement d'un d'une commande concernant un id
//================================================
//
//lecture du ref_id passé en paramèrtre
$temp_str = "";
$str_id = "";
if (isset($_GET["id"])) {
    $temp_str = $_GET["id"];
    if (strlen($temp_str) > 0) {
        $str_id = $temp_str;
        $str_log = "id=" . $str_id;
    }
}
$temp_str = "";
$str_data = "0";
if (isset($_GET["data"])) {
    $temp_str = $_GET["data"];
    if (strlen($temp_str) > 0) {
        $str_data = $temp_str;
        $str_log .= " data=" . $str_data;
    }
}

if (strlen($str_id) > 0) {
    //Si le fichier tagToChange est présent, il s'agit d'un enregistrement de ref_id pour un participant
    if (file_exists("files/tagToChange")) {

        //on récupère l'id du tag à modifier dans le fichier
        $id_participant = get_tag_to_change();
        //si le ref_id n'est pas utilisé,
        if (!is_tag_alwready_used($str_id,$id_participant)){
            //on le modifie pour l'utilisateur
            change_tag_participant($str_id, $id_participant);
            # on détruit le fichier pour dire que tout c'est bien passé
            unlink("files/tagToChange");
        }else{
            $strlog += " déjà utilisé";
        }
        
    //Sinon, c'est un ajout de données
    } else {
        $infos = get_activite_infos();
        if ($infos){
            $id_activite = $infos['id'];
            $delais = $infos['delais_min'];
            
            $id_participants = get_participant_id($str_id, $id_activite);

            $insert_is_valid = false;
            if($delais>0){
                if(delais_respected($id_participants, $delais)){
                    $insert_is_valid = true;
                }
            }else{
                $insert_is_valid = true;
            }
            
            if($insert_is_valid){
                $lastid = insert_data_for_participant($id_participants, $str_data);
                if($lastid){
                    write_last_update($lastid);
                }
            }else{
                write_log("delais non respécté");
            }
        } 
    }
    close_db($mysqli);
    write_log($str_log);
    echo "ok";
    return;
    
} else {
    close_db($mysqli);
    $str_log .= "Err. requète " . $_SERVER['QUERY_STRING'];
    write_log($str_log);
}




