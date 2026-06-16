<?php

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set("display_errors", 1);
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
require_once '../class/ParcoursClass.php';

$myactivite = new Activite();
$myparcours = new Parcours();

/* Reception du JSON */
$jsonData = file_get_contents("php://input");
/* Verifie si JSON est vide */
if (strlen($jsonData) > 0) {
    /* Decoder JSON */
    $jsonRes = json_decode($jsonData, true);
    /* Verifie les erreurs et le format final */
    if (json_last_error() == JSON_ERROR_NONE) {
        if (array_key_exists('parcours_id', $jsonRes)) {
            $myparcours->set_id($jsonRes['parcours_id']);

            if ($jsonRes['action'] == 'saveParcours') {
                $myparcours->set_nom($jsonRes['nom']);
                $myparcours->set_ordre($jsonRes['ordre']);
                $t_parcours["response"] = "ok";
                echo json_encode($t_parcours);
                
            } elseif ($jsonRes['action'] == 'saveBaliseParcours') {
                $balise = $jsonRes['balise'];
                $myparcours->set_info($balise['id'], $balise['nom'], $balise['ordre'], $balise['value']);
                $t_parcours["response"] = "ok";
                echo json_encode($t_parcours);
                
            } elseif ($jsonRes['action'] == 'delParcours') {
                $myparcours->delete();
                $t_parcours = [];
                echo json_encode($t_parcours);
                
            } elseif ($jsonRes['action'] == 'addBaliseParcours') {
                $myparcours->add_balise($jsonRes['balise_id']);
                $t_parcours = [];
                echo json_encode($t_parcours);
                
            } elseif ($jsonRes['action'] == 'delBaliseParcours') {
                if($myparcours->remove_balise($jsonRes['balise_id'])){
                    $t_parcours["response"] = "ok";
                }else{
                    $t_parcours["response"] = "used";
                }
                echo json_encode($t_parcours);
            }
        } else {
            if ($jsonRes['action'] == 'addParcours') {
                $id = $myparcours->create();
                $t_parcours = [];
                $t_parcours["id"] = $id;
                echo json_encode($t_parcours);
            }
        }
    } else {
        die('Données JSON invalides.');
    }
} else {
    die('Aucune données JSON.');
}
    
