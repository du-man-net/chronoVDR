<?php
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set("display_errors", 1);
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

//Gestion des logs des entrée WIFI/série

$logs_file = "files/logs.txt";

if (file_exists($logs_file)) {
    //lecture du fichiers de logs
    $lines = file($logs_file);
    //concaténation du tableau de ligne en un texte
    $txtold = implode ("",$lines);
    
    //On ne garde que les 15 dernière ligne de logs
    $lines = array_slice ($lines,-15);
    //concaténation du tableau de ligne en un texte
    $txtnew = implode ("",$lines);
    
    //si le fichier de log a changé
    if($txtnew != $txtold){
        //on ré-écrit le fichier avec le nouveau contenu 
        //tronqué à 15 lignes
        $f=fopen($logs_file,"w+" );
        fwrite($f,$txtnew);
        fclose($f);
    }
    //on écrit le contenu du fichier pour affichage ajax
    //dans la fenètre de logs de l'application.
    $txt = "";
    foreach($lines as $line){
        $txt .= $line."<br>";
    }
    echo $txt;
}      
