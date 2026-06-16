<?php

/* 
 * Copyright (C) 2026 gleon
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

require_once '../class/ActiviteClass.php';

$myactivite = new Activite();


/* Reception du JSON */
$jsonData = file_get_contents("php://input");
/* Verifie si JSON est vide */
if (strlen($jsonData) > 0) {
    /* Decoder JSON */
    $activite = json_decode($jsonData, true);
    /* Verifie les erreurs et le format final */
    if (json_last_error() == JSON_ERROR_NONE){
        $myactivite->infos = $activite;
        $myactivite->save();
        $t_activite = [];
        echo json_encode($t_activite);
    }else{
        die('Données JSON invalides.');
    }
} else{
    die('Aucune données JSON.');
}