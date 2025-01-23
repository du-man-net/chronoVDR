<?php
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set("display_errors", 1);
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

require_once '../class/ActiviteClass.php';
$myactivite = new Activite($mysqli);

echo urlencode(date('Y-m-d H:i:s'));
echo "<br/>";
$result = $mysqli->query("SELECT last_update FROM mysql.innodb_table_stats WHERE table_name = 'datas'");
$ep = $result->fetch_assoc();

$last_update = strtotime($ep['last_update']);
echo $last_update;
echo "<br/>";

if(isset($_GET['lastmaj'])){
    $lastmaj = strtotime($_GET['lastmaj']);
    echo $lastmaj;
    echo "<br/>";
    $interval = $lastmaj-$last_update;
    echo $interval;
}



$participants = $myactivite->get_participants();
if (!empty($participants)) {
    foreach ($participants as $participant) {
        ///$myhtml->openTr();

        /*
         * ----------------------------------------------------------------
         * affichage première cellule pour déterminer la hauteur de la ligne
         * ----------------------------------------------------------------
         */
        //$myhtml->openTd('', 'height=22px');
        $nbr = substr_count($participant["nom"], '<br/>');
        //echo str_repeat("<br/>&nbsp;", $nbr);
        //$myhtml->closeTd();

        /*
         * ----------------------------------------------------------------
         * récupération de la liste des datas pour le participants
         * ----------------------------------------------------------------
         */
        $datas = $myactivite->get_datas($participant['id_participant']);
        
        $dt_start = null;
        $dt1 = null;
        $dt2 = null;
        $i = 0;
        $nb_max = 1;
        foreach ($datas as $data) {
            if($i < $nb_max + 1){
                //$myhtml->openTd('data');
                if ($data['temps']) {

                    /*
                     * ----------------------------------------------------------------
                     * la première data défini l'heure de départ
                     * ----------------------------------------------------------------
                     */
                    if ($i == 0) {
                        $dt_start = new DateTimeImmutable($data['temps']);
                        //echo $dt_start->format('H:i:s');
                        $dt1 = $dt_start;
                        /*
                         * ----------------------------------------------------------------
                         * les données suivantes définissent les temps au tour
                         * ----------------------------------------------------------------
                         */
                    } else {
                        $dt2 = new DateTimeImmutable($data['temps']);
                        $interval = $dt1->diff($dt2);
                        $dt1 = $dt2;
                        //echo $interval->format("%I:%S");
                    }
                    
                }
                //$myhtml->closeTd();
            }
            $i++;
        }
        for($j = $i ;$j < $nb_max + 1; $j++ ){
            //$myhtml->openTd('data');
            //$myhtml->closeTd();
        }
        /*
         * ----------------------------------------------------------------
         * la dernière colomne défini le temps total sur l'épreuve
         * ----------------------------------------------------------------
         */
        //$myhtml->openTd('total');
        if (!empty($dt_start) and !empty($dt2)) {
            $interval = $dt_start->diff($dt2);
            //echo $interval->format("%I:%S");
        }
        //$myhtml->closeTd();

        //$myhtml->closeTr();
    }
}
//$myhtml->closeTable();
