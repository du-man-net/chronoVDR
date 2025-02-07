
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

/*
 * ------------------------------------------------------
 * Déclaration des class et connexion à la base de donnée
 * ------------------------------------------------------
 */
require_once '../class/HtmlClass.php';
require_once '../class/VueClass.php';

$myvue = new Vue();
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
        border-left:1px solid black;
    }
    .dumy{
        font:13px Arial;
        padding:0px; 
    }
    .bandeau{
        font:10px Arial;
        padding:3.5px;
    } 
    .data{
        font:13px Arial;
        border:1px solid #DDDDDD; 
        padding:1px; 
    }

    .total{
        font:13px Arial;
        border:1px solid blue;
        background-color:#EEEEEE;
        text-align:center;
        min-width:80px;
        padding:1px; 
    }

    .titre_time
    {
        padding:4px;
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

//$myhtml->openDiv('lstdatas');

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
 * Entête des colomne de tour
 * --------------------------------
 */

foreach($myvue->make_headers() as $header){
    $myhtml->openTd('titre_time');
    echo $header;
    $myhtml->closeTd();
}

/*
 * --------------------------------
 * Parcour des participants
 * --------------------------------
 */
$participants = $myvue->get_participants();
if (!empty($participants)) {
    foreach ($participants as $participant) {

        /*
         * ----------------------------------------------------------------
         * récupération de la liste des datas pour le participants
         * ----------------------------------------------------------------
         */

        foreach ($myvue->make_datas($participant['id_participant']) as $datas_line) {
            /*
             * ----------------------------------------------------------------
             * affichage première cellule pour déterminer la hauteur de la ligne
             * ----------------------------------------------------------------
             */
            $nbr = count(explode('<br/>',$participant["nom"]));
            if(($myvue->infos['flag'] & SHOW_TIME)&&($myvue->infos['flag'] & SHOW_DATA)){
                if($nbr>2){$nbr = $nbr*6.75+3;}else{$nbr = 24.8;}
            }else{
                if($nbr>1){$nbr = $nbr*14.35+3;}else{$nbr = 24.8;}
            }
            
            $myhtml->openTr();
            $myhtml->openTd('dumy', 'height='.$nbr.'px');
            echo "&nbsp;";
            $myhtml->closeTd();
            /*
             * ----------------------------------------------------------------
             * affichage d'une ligne de data
             * ----------------------------------------------------------------
             */

            $i = 0;
            foreach($datas_line as $data){
                if(($i == count($datas_line)-1) && ($myvue->infos['flag'] & IS_LIMIT)){
                    $myhtml->openTd('total');
                }else{
                    $myhtml->openTd('data');
                }
                if (empty($data)){
                    echo "&nbsp;";
                }else{
                    echo $data;
                }
                $myhtml->closeTd();
                $i++;
            }
            $myhtml->closeTr();
        }
    }
}
$myhtml->closeTable();
//$myhtml->closeDiv();

/*
 * ------------------------------------------------------
 * Fermeture de la page
 * ------------------------------------------------------
 */

$myhtml->closeBody();
$myhtml->closeHtml();
