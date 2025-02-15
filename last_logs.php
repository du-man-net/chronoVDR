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


$logs_file = "files/logs.txt";

if (file_exists($logs_file)) {
    $lines = file($logs_file);
    $txtold = implode ("",$lines);
    
    $lines = array_slice ($lines,-15);
    $txtnew = implode ("",$lines);
    
    if($txtnew != $txtold){
        $f=fopen($logs_file,"w+" );
        fwrite($f,$txtnew);
        fclose($f);
    }
    
    $txt = "";
    foreach($lines as $line){
        $txt .= $line."<br>";
    }
    echo $txt;
}      
