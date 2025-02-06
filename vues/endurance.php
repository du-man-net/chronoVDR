
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

/*
 * ------------------------------------------------------
 * Déclaration des class et connexion à la base de donnée
 * ------------------------------------------------------
 */
require_once '../class/HtmlClass.php';
require_once '../class/ActiviteClass.php';

$myactivite = new Activite();
$myhtml = new Html;

/*
 * ------------------------------------------------------
 * Mise en place des headers de la page 
 * isertion du fichier css et du fichier javascript
 * ------------------------------------------------------
 */
$myhtml->openHtml();
echo '<title>ChronoVDR</title>';
echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8">';
echo '<style>
    body {
        margin: 0;
    }

    table{
        border-collapse : collapse;
    }

    tr,td{
        padding:1px 3px;
        font:12px Arial;
        border:1px solid #DDDDDD;
    }
    .bandeau{
        font:11px Arial;
    } 
    .data{
        font:14px Arial;
    }

    .total{
        font:14px Arial;
        border:1px solid blue;
        background-color:#EEEEEE;
        text-align:center;
        min-width:80px;
        overflow: hidden;
    }

    #lstdatas
    {
        float:left;
        width: 100%;
        min-height: 95vh;
        border: 1px solid black;
        background-color:#FFFFFF;
    }

    .titre_time
    {
        padding:3px;
        height:22px;
        text-align:center;
        font:16px Arial;
        background:#FFFFFF;
        border:1px solid red;
        color:red;
    }
    </style>';

echo '<script language="JavaScript" type="text/JavaScript">';
echo 'window.addEventListener("resize", function () {
      const parentIframe = window.parent.frames;
      if (parentIframe && parentIframe.ajusteIframe) {
        parentIframe.ajusteIframe();
      }
    });';
echo 'window.addEventListener("message", function(event) {
  if (event.origin == " ") {
    return;
  }
  window.location.reload(); 

});';
echo '</script>';
    
$myhtml->closeHead();

$myhtml->openBody();

/*
 * ------------------------------------------------------
 * La variable id de l'activitée est la seule passée par la méthode GET. 
 * Initialisation de la class activité avec l'id passée en GET
 * ------------------------------------------------------
 */

$myhtml->openDiv('lstdatas');

$myhtml->openTable('width="100%"');
$myhtml->openTr();
$myhtml->openTd('bandeau');
    echo "&nbsp;";
$myhtml->closeTd(); 
$myhtml->closeTr();
$myhtml->closeTable();

$myhtml->openTable('id="results" width="100%"');
$myhtml->openTr();
$myhtml->openTd('', 'width=2px');
$myhtml->closeTd();

/*
 * --------------------------------
 * Entête de la colomne de départ
 * --------------------------------
 */
$myhtml->openTd('titre_time');
echo 'Départ';
$myhtml->closeTd();

/*
 * --------------------------------
 * Entête des colomne de tour
 * --------------------------------
 */
$nb_max = $myactivite->get_nb_max();
for ($i = 1; $i < $nb_max + 1; $i++) {
    $myhtml->openTd('titre_time');
    echo 'Tour' . $i;
    $myhtml->closeTd();
}
/*
 * --------------------------------
 * Entête des colomne du résultat final
 * --------------------------------
 */
$myhtml->openTd('total');
echo 'Temps final';
$myhtml->closeTd();

$myhtml->closeTr();

/*
 * --------------------------------
 * Parcour des participants
 * --------------------------------
 */
$participants = $myactivite->get_participants();
if (!empty($participants)) {
    foreach ($participants as $participant) {
        $myhtml->openTr();

        /*
         * ----------------------------------------------------------------
         * affichage première cellule pour déterminer la hauteur de la ligne
         * ----------------------------------------------------------------
         */
        $myhtml->openTd('', 'height=22px');
        $nbr = substr_count($participant["nom"], '<br/>');
        echo str_repeat("<br/>&nbsp;", $nbr);
        $myhtml->closeTd();

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
        
        foreach ($datas as $data) {
            if($i < $nb_max + 1){
                $myhtml->openTd('data');
                if ($data['temps']) {

                    /*
                     * ----------------------------------------------------------------
                     * la première data défini l'heure de départ
                     * ----------------------------------------------------------------
                     */
                    if ($i == 0) {
                        $dt_start = new DateTimeImmutable($data['temps']);
                        echo $dt_start->format('H:i:s');
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
                        echo $interval->format("%I:%S");
                    }
                    
                }
                $myhtml->closeTd();
            }
            $i++;
        }
        for($j = $i ;$j < $nb_max + 1; $j++ ){
            $myhtml->openTd('data');
            $myhtml->closeTd();
        }
        /*
         * ----------------------------------------------------------------
         * la dernière colomne défini le temps total sur l'épreuve
         * ----------------------------------------------------------------
         */
        $myhtml->openTd('total');
        if (!empty($dt_start) and !empty($dt2)) {
            $interval = $dt_start->diff($dt2);
            echo $interval->format("%I:%S");
        }
        $myhtml->closeTd();

        $myhtml->closeTr();
    }
}
$myhtml->closeTable();
$myhtml->closeDiv();

/*
 * ------------------------------------------------------
 * Fermeture de la page
 * ------------------------------------------------------
 */

$myhtml->closeBody();
$myhtml->closeHtml();
