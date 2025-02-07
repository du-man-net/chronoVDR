<?php

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

function mstime() {
    $mstime = explode(' ', microtime());
    return $mstime[1] . '' . (int) ($mstime[0] * 1000);
}

if (isset($_GET['id_participant'])) {
    $id_participant = $_GET['id_participant'];

    if (isset($_GET['ref_id'])) {
        
        $ref_id = $_GET['ref_id'];
        $mysqli->query("UPDATE participants SET ref_id = '" . $ref_id . "' WHERE id = '" . $id_participant . "'");
        echo $ref_id;
        
    } else {
        //On écrit dans un fichier temporaire l'id du participant
        $myfile = fopen("files/tagToChange", "w") or die("Unable to open file!");
        fwrite($myfile, $id_participant . "\n");
        fclose($myfile);

        //on attend 10 secondes que la page upload.php soit appelée pour 
        //récupérer l'id et ajouter le tag RFID dans la base donnée
        $debut = mstime();
        $last = $debut;
        while (file_exists("files/tagToChange") && ((mstime() - $debut) < 10000)) {
            
        }
        //Si le fichier n'existe plus, c'est que tout c'est bien passé
        if (!file_exists("files/tagToChange")) {
            //on retourne le RFID pour affichage Javascript dans l'interface
            $result = $mysqli->query("SELECT ref_id FROM participants WHERE id='" . $id_participant . "'");
            $row = $result->fetch_assoc();
            echo $row["ref_id"];
        } else {
            //on détruit le fichier temporaire si il n'a pas été utilisé par upload
            unlink("files/tagToChange");
        }
    }
}

close_db($mysqli);
