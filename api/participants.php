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
require_once '../class/UsersClass.php';

$myactivite = new Activite();

$myusers = new Users();
$lst_err = [];
$t_datas = [];

if (isset($_GET['idmat_used'])) {
    $id_participant = $_GET['idmat_used'];
    $ref_id = $myactivite->get_all_ref_id($id_participant);
    $myDatas=[];
    foreach ($ref_id as $idmat) {
        $myDatas[] = $idmat['ref_id']; 
    }
    $t_datas["idmat"] = $myDatas;
    echo json_encode($t_datas, JSON_PRETTY_PRINT);
    exit;
}

if (isset($_GET['lst_toadd'])) {
    $classe = $_GET['lst_toadd'];
    if ($classe) {
        $myDatas=[];
        $lst_participants = $myactivite->get_participantsToAdd($classe);
        foreach ($lst_participants as $participant) {
            $myData = [];
            $myData['id'] = $participant['id'];
            $myData['nom'] = $participant['nom'];
            $myData['prenom'] = $participant['prenom'];
            $myData['classe'] = $participant['classe'];
            $myDatas[] = $myData;
        }
        $t_datas["participants"] = $myDatas;
    } else {
        $classes = $myusers->getClasses();
        foreach ($classes as $classe) {
            $t_datas['classes'][] = $classe['classe'];
        }
    }

    echo json_encode($t_datas, JSON_PRETTY_PRINT);
    exit;
}

if (isset($_GET['etodel'])) {
    $equipeToDel = explode(",", $_GET['etodel']);
    $myactivite->delete_equipe($equipeToDel);
}
if (isset($_GET['etoadd'])) {
    $equipeToAdd = explode(",", $_GET['etoadd']);
    $myactivite->create_equipe($equipeToAdd);
}
if (isset($_GET['ptoadd'])) {
    $pToAdd = explode(",", $_GET['ptoadd']);
    if (is_array($pToAdd)) {
        foreach ($pToAdd as $uToAdd) {
            $myactivite->add_participants($uToAdd);
        }
    }
}
if (isset($_GET['ptodel'])) {
    $pToDel = explode(",", $_GET['ptodel']);
    if (is_array($pToDel)) {
        foreach ($pToDel as $uToDel) {
            $err = $myactivite->delete_participant($uToDel);
            if ($err != "ok") {
                $lst_err[] = $err;
            }
        }
    }
}

$lst_participants = $myactivite->get_participants();
$myDatas = [];
foreach ($lst_participants as $participant) {
    $myData = [];
    $myData['id'] = $participant['id_participant'];
    $myData['nom'] = $participant['nom'];
    $myData['ref_id'] = $participant['ref_id'];
    if (strpos($participant['nom'], "<br/>") > 0) {
        $myData['assoc'] = 1;
    } else {
        $myData['assoc'] = 0;
    }
    $myDatas[] = $myData;
}
$t_datas['err'] = $lst_err;
$t_datas["participants"] = $myDatas;

echo json_encode($t_datas, JSON_PRETTY_PRINT);
exit;
