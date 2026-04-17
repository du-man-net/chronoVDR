
<?php
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set("display_errors", 1);
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


require_once '../class/ActiviteClass.php';
require_once '../class/ParcoursClass.php';

$myactivite = new Activite();
$myparcours = new Parcours();


function recursive_array_search($champ, $valeur, $tableau) {
    return array_values(array_filter($tableau, function ($item) use($champ, $valeur) {
        return isset($item[$champ]) && $item[$champ] === $valeur;
    }));
}

if (isset($_GET['data'])) {
    
/*
 ****************************************************
 * récupération des donnée de la course
 ****************************************************
 */
    if($_GET['data'] == "datas"){
        
        if (isset($_GET['idx'])) {
            $last_index = $_GET['idx'];
        }else{
            $last_index = 0;
        }
        $t_datas = []; $myDatas = [];$lstp = [];

        if ($last_index==0){
            $datas = $myparcours->get_all_datas($myactivite->id());
        }else{
            $datas = $myparcours->get_last_datas($myactivite->id(),$last_index);
        }
        
        foreach ($datas as $data) {
            $myData['temps'] = $data['temps'];
            $myData['tag'] = $data['tag'];
            $myDatas[$data['id_participant']][$data['id_co']]["balises"][]=$myData;
            $myDatas[$data['id_participant']][$data['id_co']]["id_parcours"]=$data['id_parcours'];
            $myDatas[$data['id_participant']][$data['id_co']]["t_start"]=$data['t_start'];
            $myDatas[$data['id_participant']][$data['id_co']]["t_end"]=$data['t_end'];
            
            if($data['id']>$last_index){
                $last_index = $data['id'];
            } 
        }
        foreach ($myDatas as $idp=>$participants) {
            $lstco=[];
            foreach ($participants as $co) {
                $lstco[] = ["id_parcours"=>$co["id_parcours"],
                            "t_start"=>$co["t_start"],
                            "t_end"=>$co["t_end"],
                            "balises"=>$co["balises"]];
            }
            $lstp[] = ["id_participant"=>$idp,'lstco'=>$lstco];
        }
        
        $t_datas["last_index"]=$last_index;
        $t_datas["datas"]=$lstp;
        echo json_encode($t_datas);

/*
 ****************************************************
 * récupération de la liste des parcours et balises
 ****************************************************
 */
    }elseif($_GET['data'] == "parcours"){

        if (isset($_GET['idx'])) {
            $last_index = $_GET['idx'];
        }else{
            $last_index = 0;
        }
        $i_parcours = [];$l_parcours = [];$t_parcours = [];
        
        
        if ($last_index==0){
            $liste_parcours = $myparcours->get_all_parcours($myactivite->id());
            $t_parcours["flag"]=$myactivite->get_flag();
        }else{
            $liste_parcours = $myparcours->get_last_parcours($myactivite->id(),$last_index);
        }
        
        foreach ($liste_parcours as $parcours) {
            $i_parcours = [];$t_balise=[];
            $i_parcours['id'] = $parcours['id'];
            $i_parcours['nom'] = $parcours['nom'];
            if($parcours['id']>$last_index){
                $last_index = $parcours['id'];
            }
            $myparcours->set_id($parcours['id']);
            $balises = $myparcours->get_liste_balises();
            
            $i_balise = [];
            foreach ($balises as $balise) {
                if ($balise["nom"]==""){
                    $i_balise["nom"] = $balise["nombalise"];
                }else{
                    $i_balise["nom"] = $balise["nom"];
                }
                $i_balise["value"] = $balise["value"];
                $i_balise["ordre"] = $balise["ordre"];
                $i_balise["tag"] = $balise["tag"];
                $t_balise[] = $i_balise;
            }
            $i_parcours['balises'] = $t_balise;
            $l_parcours[]=$i_parcours;

        }
        $t_parcours["last_index"]=$last_index;
        $t_parcours["parcours"]=$l_parcours;
        echo json_encode($t_parcours);

/*
 ****************************************************
 * récupération de la liste des courses inscrite, mais non terminées
 ****************************************************
 */
   }elseif($_GET['data'] == "curent"){
       $i_parcours = [];$l_parcours = [];$t_parcours = [];
       
       $liste_parcours = $myparcours->get_curent_co();
       foreach ($liste_parcours as $parcours) {
           $i_parcours["id_co"] = $parcours["id_co"];
           $i_parcours["id_parcours"] = $parcours["id_parcours"];
           $i_parcours["nom"] = $parcours["nom"];
           $i_parcours["etat"] = $parcours["etat"];
           $l_parcours[$parcours["id_participant"]]=$i_parcours;
       }
       $t_parcours["parcours"]=$l_parcours;
       echo json_encode($t_parcours);
   }
    
}

/*
 ****************************************************
 * Création d'une course vide pour un participant
 * Ajout de l'id de parcours si connu
 ****************************************************
 */

if (isset($_GET['id_participant'])) {
    $id_participant = $_GET['id_participant'];
    
    if (!isset($_GET['id_co'])) {
        $id_co = $myparcours->create_co($id_participant);
    }else{
        $id_co = $_GET['id_co'];
    }
    
    if (isset($_GET['idp'])) {
        $idp = $_GET['idp'];
        $myparcours->setCoParcours($id_co,$idp);
        echo $id_participant.','.$id_co.','.$idp;
    }
    
}