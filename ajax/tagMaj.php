<?php
error_reporting(E_ALL & ~E_DEPRECATED);
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

require_once '../class/db.php';

$abort = "../files/abort";

function write_log($log){
    $f = fopen("../files/logs_ajax.txt", "a+") or die("Unable to open file!");
    fwrite($f, date('H:i:s')." - ".$log . "\n");
    fclose($f);   
}  

function mstime() {
    $mstime = explode(' ', microtime());
    return $mstime[1] . '' . (int) ($mstime[0] * 1000);
}

if (isset($_GET['id_participant'])) {

    $id_participant = $_GET['id_participant'];
    if (isset($_GET['ref_id'])) {
        $ref_id = $_GET['ref_id'];
        if($ref_id ==-1){ 
            $_ref_id = "";
        }else{
            $_ref_id = $ref_id;
        }
        if($_ref_id == ""){
            //on supprime l'ID que l'on veut utiliser
            file_put_contents($abort,"1",LOCK_EX);
            //On met à jour l'ID du participant dans la base de donnée
            $mysqli->query("UPDATE participants SET ref_id = '' WHERE id = '" . $id_participant . "'");
            usleep(500000);
            echo 'ok';
        }else{
            //on cherche tous les id_mat identiques
            $toClean = "";
            $result = $mysqli->query("SELECT id FROM participants WHERE ref_id='" . $_ref_id . "'");
            foreach ($result as $row) {
                $toClean .= ",".$row["id"];
            }
            $mysqli->query("UPDATE participants SET ref_id = '' WHERE ref_id = '" . $_ref_id . "'");
            //On met à jour l'ID du participant dans la base de donnée
            $mysqli->query("UPDATE participants SET ref_id = '" . $_ref_id . "' WHERE id = '" . $id_participant . "'");
            //on remonte les infos
            echo $_ref_id.$toClean;
        }
    }
}

close_db($mysqli);
