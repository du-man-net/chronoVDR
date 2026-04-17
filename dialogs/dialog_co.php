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
 * Fenètre d'ajout de parcours
 * ------------------------------------------------------
 */

$myhtml->openDiv('add-parcours');
    $myhtml->openDialog('add_parcours', $_POST["show_parcours"]);
        $myhtml->openDiv('','proprieteclose');
            $myform->button('cancel_parcours', " X ",'data-val="return"');
        $myhtml->closeDiv();
        $myhtml->openDiv('','titre_propriete');
            echo 'Gestion des parcours';
        $myhtml->closeDiv();
        
        $myhtml->openDiv('','propriete_parcours1');
            $myhtml->openDiv('','propriete_inline1');
                $myhtml->openDiv('','propriete');
                    if($ACCES_GRANTED){
                        if($selectedParcours>0){
                            $myhtml->openDiv('','iconegauche');
                                $myform->button("delete_parcours", " -- ");
                            $myhtml->closeDiv();
                        }
                    }

                    $myhtml->openDiv('','icone');
                        $myform->openSelect('selparcours', 'selparcours');
                        foreach($liste_parcours as $parcours){
                            if ($selectedParcours==0){$selectedParcours=$parcours['id'];}
                            if($selectedParcours == $parcours['id']){$select = true;}else{$select = false;}
                            $myform->option($parcours['id'], $parcours['nom'], $select);
                        }
                        $myform->closeSelect();
                    $myhtml->closeDiv();

                    if($ACCES_GRANTED){
                        $myhtml->openDiv('','iconegauche');
                            $myform->button("create_parcours", " + ");
                        $myhtml->closeDiv();
                    }
                $myhtml->closeDiv();

                $myhtml->openDiv('','propriete');
                    $myform->text('nomparcours',  $myparcours->get_nom());
                $myhtml->closeDiv();   
            $myhtml->closeDiv();  
            $myhtml->openDiv('','propriete_inline2');
                $myhtml->openDiv('','propriete');
                    $myhtml->openDiv('','title');
                    echo "Scanner une balise : ";
                    $myhtml->closeDiv();  
                    $myform->label('tag_scan_rfid','UUID');
                    $myform->text('tag_scan_rfid','');
                    $myform->label('nom_scan_rfid','Nom');
                    $myform->text('nom_scan_rfid');
                    $myform->button("add_scaned_balise", "Ajouter");
                $myhtml->closeDiv(); 
            $myhtml->closeDiv(); 
        $myhtml->closeDiv();  
        
        $myform->hidden('id_parcours_balise');


        $myhtml->openDiv('','propriete_parcours');
            $myhtml->openDiv('','propriete_inline');
                $myhtml->openDiv('','propriete');
                    $myform->label('ParcoursBalise','Liste des balises');
                $myhtml->closeDiv();  
                $myhtml->openDiv('','propriete');
                    if($myparcours->get_maxOrdreBalise()>0){$select = ' checked="checked" ';}else{$select = "";}
                    $myform->checkbox('chk_ordre',1,'id="chk_ordre"'.$select);
                    echo 'tenir compte de l\'ordre';
                $myhtml->closeDiv();  
                $myhtml->openDiv('','propriete_clear');
                    $myhtml->openTable();
                        $myhtml->openTr();
                        $myhtml->openTd();
                            echo 'Ordre';
                        $myhtml->closeTd();
                        $myhtml->openTd('','', 'style="width:120px;"');
                            echo 'Nom';
                        $myhtml->closeTd();
                        $myhtml->openTd('','', 'style="width:80px;"');
                            echo 'Tag RFID';
                        $myhtml->closeTd();
                        $myhtml->openTd('','','style="width:50px;"');
                            echo 'Points';
                        $myhtml->closeTd();
                        $myhtml->openTd();
                        if($ACCES_GRANTED){
                            $myhtml->openDiv('','iconedroite');
                            $myform->button("delete_parcours_balise", "⮕");
                            $myhtml->closeDiv(); 
                        }
                        $myhtml->closeTd();
                        $myhtml->openTd();
                        if($ACCES_GRANTED){
                            $myhtml->openDiv('','iconedroite');
                            $myform->button("add_parcours_balise", "⬅");
                            $myhtml->closeDiv(); 
                        }
                        $myhtml->closeTd();
                        $myhtml->closeTr();

                        $myform->hidden('parcours_id_balise');
                        $myform->hidden('parcours_balise_value');
                        $myform->hidden('parcours_ordre_value');

                        if($parcours_balises){
                            foreach ($parcours_balises as $balise){
                                $myhtml->openTr();
                                $myhtml->openTd();
                                    if($balise['ordre']==0){
                                        echo "0";
                                    }else{
                                        $myform->openSelect("sel_balise_ordre[" . $balise['id'] . "]",'' , 'class="sel_ordre"');
                                        for($i = 0; $i<$parcours_balises->num_rows+1; $i++){
                                           if($i == $balise['ordre']){$select = true;}else{$select = false;} 
                                           $myform->option($i, $i, $select);
                                        }
                                    }
                                $myhtml->closeTd();
                                $myhtml->openTd();
                                    if ($balise['nom']==""){
                                        $myform->text("nombalise[" . $balise['id'] . "]",  $balise['nombalise'],"class='textgrey'");
                                    }else{
                                        $myform->text("nombalise[" . $balise['id'] . "]",  $balise['nom'],"class='textblue'");
                                    }
                                $myhtml->closeTd();
                                $myhtml->openTd();
                                    echo $balise['tag'];
                                $myhtml->closeTd();
                                $myhtml->openTd();
                                    $myform->openSelect("sel_balise_value[" . $balise['id'] . "]");
                                    for($i=1;$i<11;$i++){
                                        if($i == $balise['value']){$select = true;}else{$select = false;}
                                        $myform->option($i, $i, $select);
                                    }
                                    $myform->closeSelect();
                                $myhtml->closeTd();
                                $myhtml->openTd('',"tdchk");
                                    $myhtml->openDiv('','iconedroite');
                                    $myform->checkbox('chkBalises[]', $balise["id"],"class='chkBalises'");
                                    $myhtml->closeDiv();
                                $myhtml->closeTd();
                                $myhtml->openTd();
                                $myhtml->closeTd();
                                $myhtml->closeTr();
                            }
                        }
                    $myhtml->closeTable();
                $myhtml->closeDiv();
            $myhtml->closeDiv();

            $myhtml->openDiv('','propriete_inline');
                $myhtml->openDiv('','propriete');
                    $myform->label('create_balise','Balises à ajouter');
                $myhtml->closeDiv();  
                $myhtml->openDiv('','propriete_clear');
                    $myform->openSelect('selListeBalise', 'selListeBalise', 'multiple size="19"');
                    $listeBalisesToAdd = $myparcours->get_balisesToAdd();
                     foreach($listeBalisesToAdd as $balise){
                         $myform->option($balise['id'], $balise['tag']." - ".$balise['nom']);
                     }
                     $myform->closeSelect();
                     $myform->button('delete_balise', " -- ");
                $myhtml->closeDiv();
                
            $myhtml->closeDiv();  
        $myhtml->closeDiv();   
        
        $myhtml->openDiv('','proprietebtn');
            $myform->button('saveParcours', "Enregistrer");
        $myhtml->closeDiv();  
    $myhtml->closeDialog();
$myhtml->closeDiv();


