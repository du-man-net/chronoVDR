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

//base de données
$mysql_password_path = dirname(__FILE__)."/../config/mysql_password";
$admin_password_path = dirname(__FILE__)."/../config/admin_password";

function readPW($path){
    if (file_exists($path)) {
        $myfile = fopen($path, "r");
        $password = fgets($myfile);
        $password = str_replace("\n","",$password);
        fclose($myfile);
        return $password;
    }
    return false;
}
 
$username_db = "root";
$password_db = readPW($mysql_password_path);
$admin_password = readPW($admin_password_path);
$servername = "localhost";
$database = "chronoVDR";
