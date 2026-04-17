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

function str_to_noaccent($str){

    $str = preg_replace('#Ç#', 'C', $str);
    $str = preg_replace('#ç#', 'c', $str);
    $str = preg_replace('#è|é|ê|ë#', 'e', $str);
    $str = preg_replace('#È|É|Ê|Ë#', 'E', $str);
    $str = preg_replace('#à|á|â|ã|ä|å#', 'a', $str);
    $str = preg_replace('#@|À|Á|Â|Ã|Ä|Å#', 'A', $str);
    $str = preg_replace('#ì|í|î|ï#', 'i', $str);
    $str = preg_replace('#Ì|Í|Î|Ï#', 'I', $str);
    $str = preg_replace('#ð|ò|ó|ô|õ|ö#', 'o', $str);
    $str = preg_replace('#Ò|Ó|Ô|Õ|Ö#', 'O', $str);
    $str = preg_replace('#ù|ú|û|ü#', 'u', $str);
    $str = preg_replace('#Ù|Ú|Û|Ü#', 'U', $str);
    $str = preg_replace('#ý|ÿ#', 'y', $str);
    $str = preg_replace('#Ý#', 'Y', $str);
     
    return ($str);
}

//================================================
// On enregistre l'état du parcours 
//================================================
function setEtat($id_co, $etat) {
    global $mysqli;
    $mysqli->query("UPDATE co SET etat = '".$etat."' WHERE id='".$id_co."'");
}

//================================================
// On retrouve les infos du parcours à partir de l'Id du matériel id_mat
//================================================
function getInfoFromIdmat($idmat) {
    global $mysqli;
    $infos=[];
    $participants = [];
    $result = $mysqli->query("SELECT participants.id as id_participant , "
            . "users.nom, users.prenom, "
            . "co.id as id_co "
            . "FROM participants, activites, users, co "
            . "WHERE co.id_participant = participants.id "
            . "AND co.id_activite = activites.id "
            . "AND activites.id = participants.id_activite "
            . "AND users.id = participants.id_user "
            . "AND activites.etat > '0' "
            . "AND co.etat = '0' "
            . "AND participants.ref_id = '".$idmat."'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $ids = get_associations($row['id_participant']);
        if ($ids){
            $participants = $ids;
        }else{
            $prenom = str_to_noaccent($row['prenom']);
            $nom = str_to_noaccent($row['nom']);
            $participants[] = ['id'=>$row['id_participant'],'prenom'=>$prenom,'nom'=>$nom];
        }
        $infos['participants'] = $participants;
        $infos['id_co'] = $row['id_co'];
        return $infos;
    }
    return false;
}

//====================================================
//récuperation des infos concernant l'activite et le participant
//====================================================
function get_associations($id) {
    global $mysqli;
    $ids = [];
    $result = $mysqli->query("SELECT participants.id,users.nom,users.prenom "
            . "FROM participants,users "
            . "WHERE users.id = participants.id_user "
            . "AND association = '".$id."'");
    if ($result->num_rows > 0) {
        foreach($result as $row){
            $prenom = str_to_noaccent($row['prenom']);
            $nom = str_to_noaccent($row['nom']);
            $ids[] = ['id'=>$row['id'],'prenom'=>$prenom,'nom'=>$nom];
        }
        return $ids;
    }
    return false;
}

//================================================
// On retrouve les infos du parcours à partir de l'Id du matériel id_mat
//================================================
function getParcoursFromIdco($id_co) {
    global $mysqli;
    $parcours = [];
    $result = $mysqli->query("SELECT "
            . "parcours.nom as nom_parcours,"
            . "parcours.id as id_parcours "
            . "FROM co,parcours "
            . "WHERE co.id_parcours = parcours.id "
            . "AND co.id = " . $id_co );
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $parcours['id_parcours'] = $row['id_parcours'];
        $parcours['nom_parcours'] = $row['nom_parcours'];
        return $parcours;
    } 
    return false;
}

//====================================================
//récuperation de la liste des balises
//====================================================
function getbalisesFromIdp($id_parcours) {
    global $mysqli;
    $balises = [];
    $nom = "";
    $result = $mysqli->query("SELECT balises.nom as nombalise, tag, liste_balises.nom, ordre, value "
            . "FROM balises, liste_balises "
            . "WHERE liste_balises.id_parcours ='".$id_parcours."' "
            . "AND liste_balises.id_balise = balises.id ORDER by ordre");
    if ($result->num_rows > 0) {
        foreach($result as $row){
            if ($row['nom']==""){
                $nom = $row['nombalise'];
            }else{
                $nom = $row["nom"];
            }
            $balises[] = ['tag'=>$row['tag'],'nom'=>$nom,'ordre'=>$row['ordre'],'value'=>$row['value']];
        }
        return $balises;
    }
    return false;
}

//====================================================
//récuperation de la liste des balises
//====================================================
function add_start($id_co) {
    global $mysqli;
    date_default_timezone_set("Europe/Paris");
    $mysqli->query("UPDATE co SET t_start='".date('Y-m-d H:i:s')."' WHERE id='" . $id_co."'");
}

//====================================================
//Ajout de l'heure de début du parcours
//====================================================
function add_fin($id_co) {
    global $mysqli;
    date_default_timezone_set("Europe/Paris");
    $mysqli->query("UPDATE co SET t_end='".date('Y-m-d H:i:s')."' WHERE id='" . $id_co."'");
}

//====================================================
//Ajout de l'heure de fin de parcours
//====================================================
function add_resultas($id_co,$tag,$temps) {
    global $mysqli;
    $query = "INSERT INTO co_datas (id_co,tag,temps) VALUES ('". $id_co . "','" . $tag . "','" . $temps ."')";
    $mysqli->query($query);
    return $mysqli->insert_id;
}
//================================================
// traitement d'un d'une commande concernant un id
//================================================
//
//lecture du ref_id passé en paramèrtre
$temp_str = "";
$idmat = "";
if (isset($_GET["idmat"])) {
    $temp_str = $_GET["idmat"];
    if (strlen($temp_str) > 0) {
        $idmat = $temp_str;
        $str_log = "idmat=" . $idmat;
    }
}
$temp_str = "";
$id_co = "";
if (isset($_GET["id_co"])) {
    $temp_str = $_GET["id_co"];
    if (strlen($temp_str) > 0) {
       $id_co = $temp_str;
        $str_log = "id_co=" . $id_co;
    }
}

if (strlen($idmat) > 0) {
    if (strlen($id_co) > 0) {
        if (isset($_FILES['resultas']) && $_FILES['resultas']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['resultas']['tmp_name'];
            $lines = file($tmpName);
            foreach($lines as $line) {
                $line = trim($line);
                $info = explode(',', $line); 
                add_resultas($id_co,$info[1],$info[2]);
            }
            add_fin($id_co);
            setEtat($id_co,'2');//terminée, archivée
            echo 'ok';
        }
    }else{
        $infos = getInfoFromIdmat($idmat);
        if($infos){
            $infos['parcours'] = getParcoursFromIdco($infos['id_co']);
            $infos['balises'] = getbalisesFromIdp($infos['parcours']['id_parcours']);
            setEtat($infos['id_co'],'1'); //en cours
            add_start($infos['id_co']);
            echo json_encode($infos);
        }else{
            echo "empty";
        }
    }    
}
close_db($mysqli);


