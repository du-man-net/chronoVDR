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
 * Fenètre de nettoyage/suppression de données
 * ------------------------------------------------------
 */

$myhtml->openDiv('nettoyage');
    $myhtml->openDialog('propriete_nettoyage', $_POST["show_nettoyage"]);
        $myhtml->openDiv('','net');
            $myhtml->openDiv('','proprieteclose');
                $myform->button('cancel_nettoyage', " X ", " X ",'data-val="return"');
            $myhtml->closeDiv();
            $myhtml->openDiv('','titre_propriete');
                echo 'Supprimer les données de l\'activité';
            $myhtml->closeDiv();
            $myhtml->openDiv('','proprietebtn');
                $myform->button('delete_datas', "Supprimer");
            $myhtml->closeDiv();   
            $myhtml->openDiv('','titre_propriete');
                echo 'Supprimer l\'activité';
            $myhtml->closeDiv();
            $myhtml->openDiv('','propriete');
            echo 'Supprimer l\'activitée et toutes<br/>les données qu\'elle contient';
            $myhtml->closeDiv();  
            $myhtml->openDiv('','proprietebtn');
                $myform->button('delete_activite', "Supprimer");
            $myhtml->closeDiv();  
            $myhtml->openDiv('','titre_propriete');
                echo 'Supprimer les élèves non classés';
            $myhtml->closeDiv();
            $myhtml->openDiv('','propriete');
            echo 'Supprimer définitivement les élèves<br/>qui ne n\'ont plus de classe et qui ne sont<br/>concernés par aucun enregistrement';
            $myhtml->closeDiv();  
            $myhtml->openDiv('','proprietebtn');
                $myform->button('delete_users', "Supprimer");
            $myhtml->closeDiv();  
        $myhtml->closeDiv(); 
    $myhtml->closeDialog();
$myhtml->closeDiv();   