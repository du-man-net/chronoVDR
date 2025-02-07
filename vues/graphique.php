
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
echo '<meta http-equiv="content-type" content="application/javascript;charset=utf-8">';

echo '
<script type="importmap">
  {
      "imports": {
        "@kurkle/color": "../node_modules/@kurkle/color/dist/color.esm.js",
        "chart.js/auto": "../node_modules/chart.js/auto/auto.js",
        "chart.js": "../node_modules/chart.js/dist/chart.js",
        "chartjs-adapter-moment": "../node_modules/chartjs-adapter-moment/dist/chartjs-adapter-moment.min.js",
        "moment": "../node_modules/moment/dist/moment.js",
        "chartjs-adapter-date-fns": "../node_modules/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js",
        "chartjs-adapter-luxon": "../node_modules/chartjs-adapter-luxon/dist/chartjs-adapter-luxon.esm.js",
        "luxon": "../node_modules/luxon/src/luxon.js"
      }
  }
</script>
';



//<script type="module" src="../node_modules/chartjs-adapter-luxon/dist/chartjs-adapter-luxon.umd.js"></script> 
//<script type="module" src="../node_modules/chart.js/dist/chart.umd.js"></script> 
echo'
<script type="module" src="_graphique.js"></script> 
';

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

$myhtml->closeHead();

$myhtml->openBody();

/*
 * ------------------------------------------------------
 * La variable id de l'activitée est la seule passée par la méthode GET. 
 * Initialisation de la class activité avec l'id passée en GET
 * ------------------------------------------------------
 */

$myhtml->openDiv('lstdatas');
$myhtml->openTable('id="'.$myactivite->get_id().'" width="100%"');

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
        $names_array = explode('<br/>',$participant["nom"]);
        $names = '';
        foreach($names_array as $clsname){
            $name = explode(' - ', $clsname);
            $names .= trim($name[1]).", ";
        }
        $nbr = substr_count($participant["nom"], '<br/>');
        echo str_repeat("<br/>&nbsp;", $nbr);
        $myhtml->closeTd();
        
        $myhtml->openTd();
        echo '<canvas id="'.$participant["id_participant"].'" style="height:150px;width:100%;max-width:1200px"></canvas>';
        echo '<input type="hidden" value="' . $names . '"/>';
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
