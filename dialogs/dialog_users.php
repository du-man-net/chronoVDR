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
 * Fenètre de d'impotation des noms/classe à partir de fichiers CSV
 * Depuis pronote ou depuis des listes gromatées divers
 * ------------------------------------------------------
 */
$myhtml->openDiv('add-users');
    $myhtml->openDialog('add_users', $_POST["show_users"]);
        $myhtml->openDiv('','proprieteclose');
            $myform->button('cancel_users', " X ",'data-val="return"');
        $myhtml->closeDiv();
        $myhtml->openDiv('','titre_propriete');
            echo 'Importer des classes';
        $myhtml->closeDiv();
        $myhtml->openDiv('','propriete');
            if($myimport->get_erreur()){
                echo '<br/><div style="color:#FF0000">'.$myimport->get_erreur_message().'</div>';
            }
            $myform->label('fileImport','fichier csv');
            $myform->file('fileImport');
        $myhtml->closeDiv();  
        $myhtml->openDiv('','propriete');
            $myform->label('importClasse','Classe');
            $myform->text('importClasse', '');
        $myhtml->closeDiv();
        $myhtml->openDiv('','proprietebtn');
            $myform->button('readcsvfile', "Lire le fichier");
        $myhtml->closeDiv();
        
        if(count($eleves_read)>0){
            $myhtml->openDiv('','titre_propriete');
                echo 'Liste des élèves à importer';
            $myhtml->closeDiv();
            $myhtml->openDiv('propriete');
            $lstusers = '';
            foreach ($eleves_read as $eleve){
                $lstusers .= $eleve['nom'].','.$eleve['prenom'].','.$eleve['classe'].','.$eleve['nais'].','.$eleve['sexe']."\n";
            }
            $myform->textarea('lstusers', $lstusers,40,35);
            $myhtml->closeDiv();
            $myhtml->openDiv('','proprietebtn');
                $myform->button('importer', "Importer");
            $myhtml->closeDiv();  
            
        }else{

            $myhtml->openDiv('','titre_propriete');
                echo 'Liste des classes';
            $myhtml->closeDiv();
            $myhtml->openDiv('propriete');
                $myform->openSelect('selclasse', 'selclasse', 'onchange="this.form.submit();"');
                foreach($classes as $classe){
                    if($selectedClasse == $classe['classe']){$select = true;}else{$select = false;}
                    $myform->option($classe['classe'], 'Classe de '.$classe['classe'], $select);
                }
                $myform->closeSelect();
            $myhtml->closeDiv();  
            $myhtml->openDiv('propriete');
            $myhtml->openTable();
                foreach ($eleves_classe as $eleve){
                    $myhtml->openTr();
                    $myhtml->openTd('','', 'style="width:150px;"');
                        echo $eleve['nom'];
                    $myhtml->closeTd();
                    $myhtml->openTd('','','style="width:150px;"');
                        echo $eleve['prenom'];
                    $myhtml->closeTd();
                    $myhtml->closeTr();
                }
                $myhtml->closeTable();
            $myhtml->closeDiv();
        }
    $myhtml->closeDialog();
$myhtml->closeDiv();
