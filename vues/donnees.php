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

/*
 * ------------------------------------------------------
 * Déclaration des class et connexion à la base de donnée
 * ------------------------------------------------------
 */
require_once '../class/HtmlClass.php';
require_once '../class/ActiviteClass.php';

$myactivite = new Activite($mysqli);
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
        width: 99%;
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
 * Affichage des données
 * --------------------------------
 */

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
 * Entête des colomnes 
 * --------------------------------
 */

$nb_max = intval($myactivite->get_nb_max());

for ($i = 1; $i < $nb_max +1; $i++) {
    $myhtml->openTd('titre_time');
    echo 'essais' . $i;
    $myhtml->closeTd();
}

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
        $i = 0;
        
        foreach ($datas as $data) {
            $myhtml->openTd('data');
            echo $data['data'];
            $myhtml->closeTd();
            $i++;
            if($i == $nb_max){
                break;
            }
        }
         
        for($j = $i ;$j < $nb_max+1; $j++ ){
            $myhtml->openTd('data');
            $myhtml->closeTd();
        }


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
