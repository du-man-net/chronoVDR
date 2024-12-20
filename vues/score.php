
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
require_once '../FormClass.php';
require_once '../HtmlClass.php';
require_once '../ActiviteClass.php';

$myactivite = new Activite($mysqli);
$myform = new Form;
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
$myhtml->openDiv('lstdatas');



/*
 * --------------------------------
 * calcul du nombre de cases max
 * --------------------------------
 */
$tempsMax = $myactivite->get_temps_max();
$scores=[];
$nb_max = 0;
$participants = $myactivite->get_participants();
if (!empty($participants)) {
    foreach ($participants as $participant) {
        $dt_start = null;
        $dt1 = null;
        $dt2 = null;
        $i = 0;
        $score=[];
        $datas = $myactivite->get_datas($participant['id_participant']);
        foreach ($datas as $data) {
            if ($data['temps']) {

                /*
                 * ----------------------------------------------------------------
                 * la première data défini l'heure de départ
                 * ----------------------------------------------------------------
                 */
                if ($i == 0) {
                    $dt_start = new DateTimeImmutable($data['temps']);
                    $score[$i] =  $dt_start->format('H:i:s');
                    $dt1 = $dt_start;
                    $i++;
                    /*
                     * ----------------------------------------------------------------
                     * les données suivantes définissent les temps pour chaque passage
                     * ----------------------------------------------------------------
                     */
                } else {
                    $dt2 = new DateTimeImmutable($data['temps']);
                    $interval = $dt1->diff($dt2);
                    $dt1 = $dt2;
                    $interval_sec = ((($interval->format("%a") * 24) + $interval->format("%H")) * 60 + $interval->format("%i")) * 60 + $interval->format("%s");
                    if($interval_sec<$tempsMax){
                        $score[$i] = $interval->format("%I:%S");
                        $i++;
                    }
                }
            }
        }
        $scores[$participant['id_participant']] = $score;
        if ($i > $nb_max){ $nb_max = $i; }
    }
}

/*
 * --------------------------------
 * Affichage des données
 * --------------------------------
 */
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
for ($i = 1; $i < $nb_max; $i++) {
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
echo 'Score';
$myhtml->closeTd();
$myhtml->closeTr();
/*
 * --------------------------------
 * Parcour des participants
 * --------------------------------
 */

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
        foreach ($scores[$participant['id_participant']] as $score) {
            $myhtml->openTd('data');
            echo $score;
            $myhtml->closeTd();
        }
         
        $nbscores = count($scores[$participant['id_participant']]);
        for($j = $nbscores ;$j < $nb_max; $j++ ){
            $myhtml->openTd('data');
            $myhtml->closeTd();
        }

        
        /*
         * ----------------------------------------------------------------
         * la dernière colomne défini le temps total sur l'épreuve
         * ----------------------------------------------------------------
         */
        $myhtml->openTd('total');
        echo $nbscores;
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
