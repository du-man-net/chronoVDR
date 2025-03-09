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


//on récupère le dernier id de la table data qui est enregisté
//dans files/lastupdate. 
//Si l'id a changé, alors on met a jour l'affichage
//en faisant une requète sql. 

$lastupdate = "files/lastupdate";
        
if (file_exists($lastupdate)) {
    $myfile = fopen($lastupdate, "r");
    $last_modifiy = fgets($myfile);
    fclose($myfile);
    echo $last_modifiy;
}      



