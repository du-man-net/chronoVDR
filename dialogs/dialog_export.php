<?php

/* 
 * Copyright (C) 2026 gleon
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
 * Exportation des données de l'activité
 * ------------------------------------------------------
 */

$myhtml->openDiv('exportation');
    $myhtml->openDialog('propriete_exportation', $_POST["show_exportation"]);
        $myhtml->openDiv('','proprieteclose');
            $myform->button('cancel_exportation', " X ",'data-val="return"');
        $myhtml->closeDiv();
        $myhtml->openDiv('','titre_propriete');
            echo 'Exportation des données au format csv';
        $myhtml->closeDiv();
        $myhtml->openDiv('','comment');
        echo "Exporter les données pour l'activité en cours<br/>modèle d\'exportation";
        $myhtml->closeDiv();  
        $myhtml->openDiv('propriete');
        //$myform->label('liste_exportation','modèle d\'export.');
        $myform->textarea('liste_exportation', "nom;prenom;classe;nais;sexe;temps;datas",2,50,false);
        $myhtml->closeDiv();   
        $myhtml->openDiv('','proprietebtn');
            $myform->button('btn_exportation', "Exporter");
        $myhtml->closeDiv();   
    $myhtml->closeDialog();
$myhtml->closeDiv();  