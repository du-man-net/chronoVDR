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
 * Affichage de la barre d'outils
 * Un bouton propiété activité est ajouté sur la ligne sélectionnée -> javascript
 * ------------------------------------------------------
 */ 

$myhtml->openDiv('menu');
    
if($ACCES_GRANTED){// si pas d'authentiifcation, pas d'enregistrement ni de choix
    
    $myhtml->openDiv('','iconemenu');
        echo '<img id="imgmenu" src="img/menu.png" '.   
            'title="Menu" ';
        if(!$enabled){echo 'data-val="forbidden"';}
        echo '/>';
    $myhtml->closeDiv(); 


    $myhtml->openDiv('','barre_menu');
    $myhtml->openDiv('','titre');
        echo "Activité : ";
    $myhtml->closeDiv(); 
    
    $myform->openSelect('selActivite', 'selActivite', $disabled.' onchange="this.form.submit();"');
    $myform->option('0', 'Choisir une activite');
     foreach ($myactivite->get_list() as $activite){
        if($activite['id'] == $myactivite->get_id()){$select = true;}else{$select = false;}
        $myform->option($activite['id'], $activite['nom'], $select);
    }
    $myform->closeSelect();

    if($myactivite->get_id()>0){
        
        $myhtml->openDiv('','iconemenu');
            if(intval($myactivite->infos['etat'])==2){
                $img = 'stop1.png';
                $action = 1;
                $title = "Arréter l'enregistrement";
            }else{
                $img = 'start1.png';
                $action = 2;
                $title = "Démarrer l'enregistrement";
            }
            echo '<img id="stopstart" src="img/'.$img.'" '.   
                'title="'.$title.'" '.
                'data-val="'.$action.'"/>';           
        $myhtml->closeDiv();
        
        $myhtml->closeDiv(); 

        $myhtml->openDiv('','barre_menu');
        $myhtml->openDiv('','titre');
            echo "Vues : ";
        $myhtml->closeDiv(); 

        $myhtml->openDiv('','iconemenu');
            if($myactivite->infos['vue']=="tableau"){
                $img = 'graphique.png';
                $action = "graphique";
                $title = "Vue graphique";
            }else{
                $img = 'tableau.png';
                $action = "tableau";
                $title = "Vue tableau";
            }
            echo '<img id="vue" src="img/'.$img.'" '.   
                'title="'.$title.'" '.
                'data-val="'.$action.'"/>';           
        $myhtml->closeDiv();
        
    }
    $myhtml->closeDiv(); 
    
    } 
        $myhtml->openDiv('','iconemenu');
        echo '<img id="log" src="img/logs.png" '.   
            'title="Exporter les données"/>';  
        $myhtml->closeDiv(); 
        
     
        $myhtml->openDiv('','iconemenu');
            if($ACCES_GRANTED){
                $id = 'pwd_off';
                $img = 'unlock1.png';
                $action = 'this';
                $title = "Se connecter";
            }else{
                $id = 'pwd_on';
                $img = 'lock1.png';
                $action = '';
                $title = "Se déconnecter";
            }
            echo '<img id="'.$id.'" src="img/'.$img.'" '.   
                'title="'.$title.'" '.
                'data-val="'.$action.'"/>';       
        $myhtml->closeDiv(); 
        
        $myhtml->openDiv('','iconemenu');
            echo '<img id="refresh" src="img/refresh.png" '.   
                'title="actualiser" '.
                'onclick="window.location.href = window.location.href;"/>';                
        $myhtml->closeDiv(); 

       
        $myhtml->openDiv('','barre_menu_g');
        $myhtml->openDiv('','titre');
            echo "Réseau : ";
        $myhtml->closeDiv(); 

        $myhtml->openDiv('','iconemenug');
            $interfaces = net_get_interfaces();
            if ($interfaces){
                foreach($interfaces as $nom=>$interface){
                    $unicasts = $interface['unicast'];
                    if (isset($unicasts[1]['address'])) {
                        $address = $unicasts[1]['address'];
                        $address = substr($address,strpos($address,"<br"));
                    }
                    if (isset($unicasts[1]['netmask'])) {
                        $netmask = $unicasts[1]['netmask'];
                        $netmask = substr($netmask,strpos($netmask,"<br"));
                    }
                    if($nom=="eth0"){
                        $nom = "filaire";
                    }else if($nom=="wlan0"){
                        $nom = "wifi";
                    }
                    if(strpos($address,'127')!==0){
                        echo $nom." : ".$address." / ".$netmask."<br/>";
                    }

                }
            }
        $myhtml->closeDiv(); 
        
        $myhtml->openDiv('divParcoursParticipant','','style="visibility:hidden;font-size:0px ;"');
            $myform->openSelect('selParcoursParticipant', 'selParcoursParticipant'); //
            foreach ($myparcours->get_list() as $parcours){
                $myform->option($parcours['id'], $parcours['nom']);
            }
            $myform->closeSelect();
        $myhtml->closeDiv(); 
$myhtml->closeDiv();