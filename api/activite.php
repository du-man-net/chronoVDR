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

$myactivite = new Activite();

$last_index = 0;
$t_datas = [];
        
if (isset($_GET['get'])) {
    $id_activite = $myactivite->get_id($_SESSION['id']);
    $t_datas["current"] = $myactivite->refresh();
    echo json_encode($t_datas);

    exit;
}

if (isset($_GET['set_id'])) {
    $id_activite = $_GET['set_id'];
    $myactivite->set_id($id_activite);
    echo json_encode($t_datas);

    exit;
}

if (isset($_GET['set_vue'])) {
    $vue = $_GET['set_vue'];
    $myactivite->set_vue($vue);
    echo json_encode($t_datas);

    exit;
}

if (isset($_GET['startStop'])) {
    $etat = $_GET['startStop'];
    $myactivite->startStop($etat);
    echo json_encode($t_datas);

    exit;
}

if (isset($_GET['add'])) {
    $myactivite->create($_SESSION['id']);
    $t_datas["current"] = $myactivite->refresh();
    echo json_encode($t_datas);

    exit;
}

if (isset($_GET['del'])) {
    $id_activite = $_GET['del'];
    $myactivite->delete($id_activite);
}

if (isset($_GET['del_datas'])) {
    $id_activite = $_GET['del_datas'];
    $myactivite->delete_all_datas($id_activite);
    echo json_encode($t_datas);
    
    exit;
}
 
if (isset($_GET['export'])) {
    $modele = $_GET['export'];
    $myactivite->refresh();
    $myactivite->export($modele);
    
    exit;
}


$lst_activite = $myactivite->get_list();
foreach ($lst_activite as $activite) {
    $myData = [];
    $myData['id'] = $activite['id'];
    $myData['id_admin'] = $activite['id_admin'];
    $myData['nom'] = $activite['nom'];
    $myDatas[] = $myData;
}

$t_datas["selected"] = $myactivite->get_id($_SESSION['id']);
$t_datas["liste"] = $myDatas;
$t_datas["type"] = $myactivite->type_activite;

echo json_encode($t_datas);

exit;
