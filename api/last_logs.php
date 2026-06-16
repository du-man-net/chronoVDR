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
$logs = [];$t_logs = [];

if (file_exists($logs_file)) {
    
    $last_index = 0;
    if (isset($_GET['idx'])) {
        $last_index = $_GET['idx'];
    }

    $lines = file($logs_file);
    
    //Si plus de 15 lignes ont été ajoutée, 
    //on place l'index au début des 15 dernières
    $nb_lines  = count($lines);
    if($nb_lines-$last_index>15){
        $last_index = $nb_lines-15;
    }
    
    //On ne lit que les ligne nouvelles
    for($i=0;$i<$nb_lines;$i++){
        if($i >= $last_index){
            $logs[] = $lines[$i];
        } 
    }
    
    if ($nb_lines>15){
        //On ne garde que les 15 dernières ligne de logs
        $lines = array_slice ($lines,-15);
        //concaténation du tableau de ligne en un texte
        $txtnew = implode ("",$lines);
        //on ré-écrit le fichier avec le nouveau contenu 
        $f=fopen($logs_file,"w+" );
        fwrite($f,$txtnew);
        fclose($f);
    }
    
    $last_index = count($lines);
    $t_logs["last_index"]=$last_index;
    $t_logs["logs"]=$logs;
    echo json_encode($t_logs);
}      
