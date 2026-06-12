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

require_once '../class/AdminClass.php';

$myadmin = new Admin();

if (isset($_GET['add'])) {
    $myadmin->create("nom", "login", "12345678");
}

if (isset($_GET['del'])) {
    $id = $_GET['del'];
    $myadmin->delete($id);
}

if (isset($_GET['save'])) {
    $id = $_GET['save'];

    if (isset($_GET['login'])) {
        $login = $_GET['login'];
        $myadmin->setLogin($id, $login);
    }

    if (isset($_GET['nom'])) {
        $nom = $_GET['nom'];
        $myadmin->setNom($id, $nom);
    }

    if (isset($_GET['pw'])) {
        $pw = $_GET['pw'];
        $myadmin->changePassword($id, $pw);
    }
}

$t_datas = [];$myDatas =[];
$lst_admin = $myadmin->get_list();
foreach ($lst_admin as $admin) {
    $myData = [];
    $myData['id'] = $admin['id'];
    $myData['login'] = $admin['login'];
    $myData['nom'] = $admin['nom'];
    $myDatas[] = $myData;
}
$t_datas["liste"] = $myDatas;

echo json_encode($t_datas);

exit;
