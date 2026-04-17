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
 * Fenètre d'ajout de participants
 * ------------------------------------------------------
 */

$myhtml->openDiv('add-participants');
    $myhtml->openDialog('add_participants', $_POST["show_participants"]);
        $myhtml->openDiv('','proprieteclose');
            $myform->button('cancel_participants', " X ",'data-val="return"');
        $myhtml->closeDiv();
        $myhtml->openDiv('','titre_propriete');
            echo 'Ajouter des participants';
        $myhtml->closeDiv();
        
        $myhtml->openDiv('propriete');
            $myhtml->openTable();
            $myhtml->openTr();
            $myhtml->openTd('','', 'colspan="2" width="300px"');
            $myform->openSelect('selclasseParticipants', 'selclasseParticipants', 'onchange="this.form.submit();"');
            foreach($classes as $classe){
                if($selectedClassePartcipants == $classe['classe']){$select = true;}else{$select = false;}
                $myform->option($classe['classe'], 'Classe de '.$classe['classe'], $select);
            }
            $myform->closeSelect();
            $myhtml->closeTd();
            $myhtml->openTd('','tdchk');
                $myform->checkbox('check_users', "all", " id='toutcocher'");
            $myhtml->closeTd();
            $myhtml->closeTr();
            foreach ($eleves_participants as $eleve){
                $myhtml->openTr();
                $myhtml->openTd('','', 'style="width:150px;"');
                    echo $eleve['nom'];
                $myhtml->closeTd();
                $myhtml->openTd('','','style="width:150px;"');
                    echo $eleve['prenom'];
                $myhtml->closeTd();
                $myhtml->openTd('','tdchk');
                    $myform->checkbox('check_users[]', $eleve["id"],"class='check_users'");
                $myhtml->closeTd();
                $myhtml->closeTr();
            }
            $myhtml->closeTable();
        $myhtml->closeDiv();
            
        $myhtml->openDiv('','proprietebtn');
            $myform->button('AjouterParticipants', "ajouter");
        $myhtml->closeDiv();  
    $myhtml->closeDialog();
$myhtml->closeDiv();