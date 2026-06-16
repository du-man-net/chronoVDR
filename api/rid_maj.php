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
$tagToChange = "../files/tagToChange";

function write_log($log) {
    $f = fopen("../files/logs_ajax.txt", "a+") or die("Unable to open file!");
    fwrite($f, date('H:i:s') . " - " . $log . "\n");
    fclose($f);
}

function mstime() {
    $mstime = explode(' ', microtime());
    return $mstime[1] . '' . (int) ($mstime[0] * 1000);
}

if (isset($_GET['id_participant'])) {
    $id_participant = $_GET['id_participant'];

    $t_datas = [];

    if (isset($_GET['id_mat'])) {
        $id_mat = $_GET['id_mat'];
        if ($id_mat == "delete") {
            //On met à jour l'ID du participant dans la base de donnée
            $mysqli->query("UPDATE participants SET ref_id = '' WHERE id = '" . $id_participant . "'");
            
            $t_datas["action"]="delete";
            $t_datas["newid"] = "";
            echo json_encode($t_datas);
        } else {
            //on cherche tous les id_mat identiques
            $toClean = [];
             $result = $mysqli->query("SELECT id FROM `participants` "
                    . "WHERE id_activite = ("
                    . "SELECT id_activite "
                    . "FROM `participants` "
                    . "WHERE id = '" . $id_participant . "') "
                    . "AND ref_id='" . $id_mat . "'");
            foreach ($result as $row) {
                $toClean[] = $row["id"];
            }
            $t_datas["idtodel"] = $toClean;

            //On met à jour l'ID du participant dans la base de donnée
            $mysqli->query("UPDATE participants SET ref_id = '" . $id_mat . "' WHERE id = '" . $id_participant . "'");
            //on remonte les infos
            
            $t_datas["action"]="set";
            $t_datas["newid"] = $id_mat;
            echo json_encode($t_datas);
        }
    } else if (isset($_GET['rid'])) {
        $rid = $_GET['rid'];
        if ($rid == "toscan") {

            file_put_contents($tagToChange, $id_participant);
            if (file_exists($abort)) {
                unlink($abort);
            }
            //on attend 15 secondes que la page upload.php soit appelée pour 
            //récupérer l'id et ajouter le tag RFID dans la base donnée
            $debut = mstime();
            while (true) {
                clearstatcache();
                if ((mstime() - $debut) > 10000) {
                    break;
                }
                if (!file_exists($tagToChange)) {
                    break;
                }
                if (file_exists($abort)) {
                    break;
                }
                usleep(200000);
            }

            //Si le fichier n'existe plus, c'est que tout c'est bien passé
            if (!file_exists($tagToChange)) {
                //on retourne le RFID pour affichage Javascript dans l'interface
                $result = $mysqli->query("SELECT ref_id FROM participants WHERE id='" . $id_participant . "'");
                $row = $result->fetch_assoc();
                $t_datas["action"]="set";
                $t_datas["newid"] = $row['ref_id'];
            } else {
                //on détruit le fichier temporaire si il n'a pas été utilisé par upload
                unlink($tagToChange);
                if (file_exists($abort)) {
                    unlink($abort);
                }
                $t_datas["action"]="cancel";
                $t_datas["newid"] = "";
            }
            echo json_encode($t_datas);
        }
    }
}

close_db($mysqli);
