<?php

/* 
 * Copyright (C) 2024 gleon
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

require_once dirname(__FILE__)."/../config/config.php";

function connect_db() {
    global $mysqli;
    global $username_db;
    global $password_db;
    
    if (!isset($mysqli)) {
        $servername = "localhost";
        $database = "chronoVDR";
        $mysqli = new mysqli($servername, $username_db, $password_db, $database);
        /* Vérification de la connexion */
        if (mysqli_connect_errno()) {
            printf("Échec de la connexion : %s\n", mysqli_connect_error());
            exit();
        }
        $mysqli->set_charset("utf8");
    }
}

function close_db() {
    global $mysqli;
    mysqli_close($mysqli);
}

$mysqli = null;
connect_db();