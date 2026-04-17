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

$myhtml->openDiv('dialog-menu');
    $myhtml->openDialog('dialog_menu', $_POST["show_menu"]);
        $myhtml->openDiv('','titre_propriete');
            echo 'Menu';
        $myhtml->closeDiv();
            
        $myhtml->openDiv('dial_users','propriete_menu','data-val="show_dialog_menu"');
        $myhtml->openDiv('','iconemenu');
        echo '<img src="img/users.png" title="Importer des classes"/>';
        $myhtml->closeDiv(); 
        $myhtml->openDiv('',"label_menu");
        echo('Importation de noms.');
        $myhtml->closeDiv();  
        $myhtml->closeDiv(); 
        
        $myhtml->openDiv('dial_activite','propriete_menu','data-val="show_dialog_menu"');
        $myhtml->openDiv('','iconemenu');
        echo '<img src="img/plus.png" title="Créer une nouvelle activité."/>';
        $myform->button('btn_creer_activite',"&nbsp;",'style="display:none;"');
        $myhtml->closeDiv();  
        $myhtml->openDiv('',"label_menu");
        echo('Créer une nouvelle activité.');
        $myhtml->closeDiv(); 
        $myhtml->closeDiv(); 
        
        $myhtml->openDiv('dial_prop','propriete_menu','data-val="show_dialog_menu"');
        $myhtml->openDiv('','iconemenu');
        echo '<img src="img/propriete.png" title="Paramètres de l\'activité" />';
        $myhtml->closeDiv(); 
        $myhtml->openDiv('',"label_menu");
        echo('Paramètres de l\'activité activité.');
        $myhtml->closeDiv(); 
        $myhtml->closeDiv(); 

        $myhtml->openDiv('dial_parcours','propriete_menu','data-val="show_dialog_menu"');
        $myhtml->openDiv('','iconemenu');
        echo '<img src="img/parcours.png" title="Gestion des parcours (CO)" />';
        $myhtml->closeDiv(); 
        $myhtml->openDiv('',"label_menu");
        echo('Gestion des parcours (CO)');
        $myhtml->closeDiv(); 
        $myhtml->closeDiv(); 
        
        $myhtml->openDiv('dial_export','propriete_menu','data-val="show_dialog_menu"');
        $myhtml->openDiv('','iconemenu');
            echo '<img src="img/export.png" title="Exporter les données" />';
        $myhtml->closeDiv(); 
        $myhtml->openDiv('',"label_menu");
        echo('Expoter les données');
        $myhtml->closeDiv(); 
        $myhtml->closeDiv(); 
        
        $myhtml->openDiv('dial_nett','propriete_menu','data-val="show_dialog_menu"');
        $myhtml->openDiv('','iconemenu');
        echo '<img src="img/supprimer.png" title="Nettoyage"/>';
        $myhtml->closeDiv();
        $myhtml->openDiv('',"label_menu");
        echo('Nettoyage...');
        $myhtml->closeDiv(); 
        $myhtml->closeDiv();
        
    $myhtml->closeDialog();
$myhtml->closeDiv();