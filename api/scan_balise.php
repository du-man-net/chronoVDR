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

require_once '../class/db.php';
$scan_balise = "../files/scan_balise";

function write_log($log){
    $f = fopen("../files/logs_ajax.txt", "a+") or die("Unable to open file!");
    fwrite($f, date('H:i:s')." - ".$log . "\n");
    fclose($f);   
}  

function mstime() {
    $mstime = explode(' ', microtime());
    return $mstime[1] . '' . (int) ($mstime[0] * 1000);
}
    

//On ouvre un fichier temporaire
file_put_contents($scan_balise,"ok");
//on attend 15 secondes que la page upload.php soit appelée pour 
//récupérer l'id et ajouter le tag RFID dans le fichier tag_balise
$debut = mstime();
$last = $debut;
while (file_exists($scan_balise) && ((mstime() - $debut) < 15000)) {

}
if (is_file("files/tag_balise")) {
    $tag = file_get_contents("files/tag_balise");
    unlink("files/tag_balise");
    echo $tag;
}
//si le fichier scan_balise est toujours la, on le vire
if (is_file($scan_balise)) {
    unlink($scan_balise);
}


close_db($mysqli);
