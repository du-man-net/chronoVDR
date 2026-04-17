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
 * Fenètre de paramétrage de l'activité
 * Le bouton btnaction prend les valeurs créer ou enregistrer
 * en fonction de l'utilisation -> javascript
 * ------------------------------------------------------
 */
$myhtml->openDiv('activite');
    $myhtml->openDialog('propriete_activite', $_POST["show_activite"]);
        $myhtml->openDiv('','proprieteclose');
            $myform->button('cancel_activite', " X ",'data-val="return"');
        $myhtml->closeDiv();
        $myhtml->openDiv('proptitle','titre_propriete');
            echo 'titre à modifié en fonction de l\'usage';
        $myhtml->closeDiv();
        $myhtml->openDiv('','propriete');
            if(isset($message)){echo '<br/><div style="color:#FF0000">'.$message.'</div>';}
            $myform->label('title_activite','Nom de l\'activité');
            $myform->text('title_activite', $myactivite->infos['nom']);
        $myhtml->closeDiv();
        $myhtml->openDiv('','propriete');
            $myform->label('organisateur','Organisateur');
            $myform->text('organisateur', $myactivite->infos['organisateur']);
        $myhtml->closeDiv();   

        $myhtml->openDiv('','propriete');
            $myform->label('flag','Type d\'activité');
            $myform->openSelect('flag','flag');
            foreach($myactivite->type_activite as $flag => $titre_activite){
                if($myactivite->infos["flag"] == $flag){$select = true;}else{$select = false;}
                $myform->option($flag,$titre_activite,$select);
            }
            $myform->closeSelect();
            $myform->hidden("change_activite");
        $myhtml->closeDiv(); 
 
        $myhtml->openDiv('','propriete');
            if($myactivite->infos["flag"] & IS_LIMIT_LAP){
                $myform->label('nb_max','Nombre maximum d\'enregistrements');
                $myform->text('nb_max', $myactivite->infos['nb_max']);
            }
            if($myactivite->infos["flag"] & IS_LIMIT_TIME){
                $myform->label('temps_max','Temps maximum (en minutes)');
                $myform->text('temps_max', $myactivite->infos['temps_max']);
            }
        $myhtml->closeDiv();  

        $myhtml->openDiv('','propriete');
            $myform->label('delais_min','Temps minimum entre deux enregistrements (seconde)');
            $myform->text('delais_min', $myactivite->infos['delais_min']."");
        $myhtml->closeDiv();  
        
        if($myactivite->infos["flag"] & PERSONAL_CONFIG){$disabled = "";}else{$disabled = 'disabled="true"';}
        $myhtml->openDiv('','propriete');
            $myform->label('flag_ID','Authentification des participants par');
            $myform->openSelect('flag_ID','flag_ID',$disabled);
            if($myactivite->infos["flag"] & BY_IDMAT){$select = true;}else{$select = false;}
            $myform->option(BY_IDMAT,'Id_matériel', $select);
            if($myactivite->infos["flag"] & BY_RFID){$select = true;}else{$select = false;}
            $myform->option(BY_RFID,'RFID', $select);
            $myform->closeSelect();
        $myhtml->closeDiv(); 
        
        $myhtml->openDiv('','propriete');
            $myform->label('flag_SHOW_TIME','Afficher l\'heure de départ');
            if($myactivite->infos["flag"] & SHOW_TIME){$select = "checked ";}else{$select = "";}
            $myform->checkbox('flag_SHOW_TIME',SHOW_TIME,$select . $disabled." id='flag_SHOW_TIME'");
        $myhtml->closeDiv();  

        $myhtml->openDiv('','propriete');
            $myform->label('flag_PERLAP','Afficher pour chaque enregistrement');
            $myform->openSelect('flag_PERLAP','flag_PERLAP',$disabled);
            if($myactivite->infos["flag"] & HOUR_PER_LAP){$select = true;}else{$select = false;}
            $myform->option(HOUR_PER_LAP,'Heure', $select);
            if($myactivite->infos["flag"] & TIME_PER_LAP){$select = true;}else{$select = false;}
            $myform->option(TIME_PER_LAP,'Temps en min.', $select);
            if($myactivite->infos["flag"] & DATA_PER_TEST){$select = true;}else{$select = false;}
            $myform->option(DATA_PER_TEST,'Donnée', $select);
            $myform->closeSelect();
        $myhtml->closeDiv(); 
        
        $myhtml->openDiv('','propriete');
            $myform->label('flag_TIME','Afficher le temps total');
            if($myactivite->infos["flag"] & SHOW_FINAL_TIME){$select = "checked ";}else{$select = "";}
            $myform->checkbox('flag_TIME',SHOW_FINAL_TIME,$select . $disabled." id='flag_TIME'");
        $myhtml->closeDiv();  
        
        $myhtml->openDiv('','propriete');
            $myform->label('flag_NBLAPS','Afficher le nombre de tours');
            if($myactivite->infos["flag"] & SHOW_NUMBER_LAPS){$select = "checked ";}else{$select = "";}
            $myform->checkbox('flag_NBLAPS',SHOW_NUMBER_LAPS,$select . $disabled." id='flag_NBLAPS'");
        $myhtml->closeDiv();  
        
        $myhtml->openDiv('','proprietebtn');
            $myform->button('enregistrer_activite', "Enregistrer");
        $myhtml->closeDiv();   
    $myhtml->closeDialog();
$myhtml->closeDiv();