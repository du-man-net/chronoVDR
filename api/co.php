
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

if (isset($_GET['data'])) {

    /*
     * ***************************************************
     * récupération des donnée de la course
     * ***************************************************
     */
    if ($_GET['data'] == "datas") {

        if (isset($_GET['idx'])) {
            $last_index = $_GET['idx'];
        } else {
            $last_index = 0;
        }
        $t_datas = [];
        $myDatas = [];

        if ($last_index == 0) {
            $datas = $myparcours->get_all_datas($myactivite->id());
        } else {
            $datas = $myparcours->get_last_datas($myactivite->id(), $last_index);
        }

        //echo 'id_participant,id_co,idparcours,id_data,tag,nom<br/>';

        foreach ($datas as $data) {
            $myData = [];
            $myData['temps'] = $data['temps'];
            $myData['tag'] = $data['tag'];
            $myData['nom'] = $data['nom'];

            if (!array_key_exists($data['id_participant'], $myDatas)) {
                $myDatas[$data['id_participant']] = [];
            }

            if (!array_key_exists($data['id_co'], $myDatas[$data['id_participant']])) {
                $myDatas[$data['id_participant']][$data['id_co']] = [];
            }

            $myDatas[$data['id_participant']][$data['id_co']]["balises"][] = $myData;
            $myDatas[$data['id_participant']][$data['id_co']]["id_parcours"] = $data['id_parcours'];
            $myDatas[$data['id_participant']][$data['id_co']]["t_start"] = $data['t_start'];
            $myDatas[$data['id_participant']][$data['id_co']]["t_end"] = $data['t_end'];

//            echo $data['id_participant'] . ',' .
//            $data['id_co'] . ',' .
//            $data['id_parcours'] . ',' .
//            $data['id_data'] . ',' .
//            $data['tag'] . ',' .
//            $data['nom'] . "<br/>";


            if ($data['id_data'] > $last_index) {
                $last_index = $data['id_data'];
            }
        }
        
        $lstp=[];
        foreach ($myDatas as $idp => $participants) {
            $lstco = [];
            foreach ($participants as $co) {
                $lstco[] = ["id_parcours" => $co["id_parcours"],
                    "t_start" => $co["t_start"],
                    "t_end" => $co["t_end"],
                    "balises" => $co["balises"]];
            }
            $lstp[] = ["id_participant" => $idp, 'lstco' => $lstco];
        }

        $t_datas["last_index"] = $last_index;
        $t_datas["datas"] = $lstp;
        echo json_encode($t_datas);

        /*
         * ***************************************************
         * récupération de la liste des parcours et balises
         * ***************************************************
         */
    } elseif ($_GET['data'] == "parcours") {

        if (isset($_GET['idx'])) {
            $last_index = $_GET['idx'];
        } else {
            $last_index = 0;
        }
        $i_parcours = [];
        $l_parcours = [];
        $t_parcours = [];

        if ($last_index == 0) {
            $liste_parcours = $myparcours->get_all_parcours($myactivite->id());
            $t_parcours["flag"] = $myactivite->get_flag();
        } else {
            $liste_parcours = $myparcours->get_last_parcours($myactivite->id(), $last_index);
        }

        foreach ($liste_parcours as $parcours) {
            $i_parcours = [];
            $t_balise = [];
            $i_parcours['id'] = $parcours['id'];
            $i_parcours['nom'] = $parcours['nom'];
            $i_parcours['ordre'] = $parcours['ordre'];
            if ($parcours['id'] > $last_index) {
                $last_index = $parcours['id'];
            }
            $myparcours->set_id($parcours['id']);
            $balises = $myparcours->get_liste_balises();

            $i_balise = [];
            if ($balises) {
                foreach ($balises as $balise) {
                    $i_balise["id"] = $balise["id"];
                    $i_balise["nombalise"] = $balise["nombalise"];
                    $i_balise["nom"] = $balise["nom"];
                    $i_balise["value"] = $balise["value"];
                    $i_balise["ordre"] = $balise["ordre"];
                    $i_balise["tag"] = $balise["tag"];
                    $t_balise[] = $i_balise;
                }
                $i_parcours['balises'] = $t_balise;
            } else {
                $i_parcours['balises'] = [];
            }
            $l_parcours[] = $i_parcours;
        }
        $t_parcours["last_index"] = $last_index;
        $t_parcours["parcours"] = $l_parcours;
        echo json_encode($t_parcours);

        /*
         * ***************************************************
         * récupération de la liste des balises a ajouter
         * ***************************************************
         */
    } elseif ($_GET['data'] == "balises") {
        if (isset($_GET['id'])) {
            $id_parcours = $_GET['id'];

            $myparcours->set_id($id_parcours);
            $balises = $myparcours->get_balisesToAdd();
            $i_balise = [];
            $l_balise = [];
            $t_balises = [];
            foreach ($balises as $balise) {
                $i_balise["nom"] = $balise["nom"];
                $i_balise["tag"] = $balise["tag"];
                $i_balise["id"] = $balise["id"];
                $l_balise[] = $i_balise;
            }
            $t_balises["balises"] = $l_balise;
            echo json_encode($t_balises);
        }

        /*
         * ***************************************************
         * récupération de la liste des parcours non terminés
         * ***************************************************
         */
    } elseif ($_GET['data'] == "curent") {
        $i_parcours = [];
        $l_parcours = [];
        $t_parcours = [];

        $liste_parcours = $myparcours->get_curent_co();
        foreach ($liste_parcours as $parcours) {
            $i_parcours["id_co"] = $parcours["id_co"];
            $i_parcours["idp"] = $parcours["id_parcours"];
            $i_parcours["nom"] = $parcours["nom"];
            $i_parcours["etat"] = $parcours["etat"];
            $i_parcours["id_participant"] = $parcours["id_participant"];
            $l_parcours[] = $i_parcours;
        }
        $t_parcours["parcours"] = $l_parcours;
        echo json_encode($t_parcours);
    }
}

/*
 * ***************************************************
 * Création d'une course vide pour un participant
 * Ajout de l'id de parcours si connu
 * ***************************************************
 */

if (isset($_GET['id_participant'])) {
    $id_participant = $_GET['id_participant'];
    $t_datas = [];

    if (!isset($_GET['id_co'])) {
        $id_co = $myparcours->create_co($id_participant);
    } else {
        $id_co = $_GET['id_co'];
    }

    if (isset($_GET['idp'])) {
        $idp = $_GET['idp'];
        $myparcours->setCoParcours($id_co, $idp);

        $t_datas["id_participant"] = $id_participant;
        $t_datas["id_co"] = $id_co;
        $t_datas["idp"] = $idp;
    }
    echo json_encode($t_datas);
}