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

require_once '../class/ImportClass.php';
require_once '../class/UsersClass.php';

$myusers = new Users();
$myimport = new Import();

if (isset($_FILES['fileImport'])){
    $t_datas['users'] = [];
    $myimport->set_file($_FILES['fileImport']);
    if ($myimport->is_csv_file()) {
        $myimport->read_file();
        if (filter_has_var(INPUT_POST, 'importClasse')) {
            $classe = filter_input(INPUT_POST, 'importClasse', FILTER_SANITIZE_STRING);
        }
        $eleves_read = $myimport->getElevesArray($classe);
        $t_datas["users"] = $eleves_read;
        $t_datas["erreur"] = $myimport->get_erreur();
        $t_datas["erreur_message"] = $myimport->get_erreur_message();
    }
    echo json_encode($t_datas, JSON_PRETTY_PRINT);
    exit;
}

/* Reception du JSON */
$jsonData = file_get_contents("php://input");
/* Verifie si JSON est vide */
if (strlen($jsonData) > 0) {
    /* Decoder JSON */
    $users = json_decode($jsonData, true);
    /* Verifie les erreurs et le format final */
    if (json_last_error() == JSON_ERROR_NONE) {
        foreach ($users as $user) {
            $classe = $user["classe"];
            $myusers->insertUser($user);
        }
        $myusers->cleanUsersFromClasse($users, $classe);
        $t_datas = [];
        echo json_encode($t_datas, JSON_PRETTY_PRINT);
    } else {
        die('Données JSON invalides.');
    }
} else {
    die('Aucune données JSON.');
}
