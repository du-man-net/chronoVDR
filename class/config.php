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

//base de données
$mypwdfile = dirname(__FILE__)."/../config/mysql_password";

if (file_exists($mypwdfile)) {
    $myfile = fopen($mypwdfile, "r");
    $password_db = fgets($myfile);
    $password_db = str_replace("\n","",$password_db);
    fclose($myfile);
}    
$username_db = "root";
$admin_password = $password_db;
$servername = "localhost";
$database = "chronoVDR";
