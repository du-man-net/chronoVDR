<?php

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
require_once "db.php";

define("BY_RFID", 0x1);
define("BY_IDMAT", 0x2);

define( "SHOW_TIME", 0x4 );
define( "SHOW_DATA", 0x8 );
define( "SHOW_START", 0x10 );

define("TIME_SINCE_START", 0x20);
define("HOUR_PER_LAP", 0x40);
define("TIME_PER_LAP", 0x80);
define("DATA_PER_TEST", 0x100);

define("IS_LIMIT_TIME", 0x200);
define("IS_LIMIT_LAP", 0x400);
define("IS_LIMIT", 0x800);

define("SHOW_FINAL_TIME", 0x1000);
define("SHOW_NUMBER_LAPS", 0x2000);
define("SHOW_TOTAL_DATA", 0x4000);

define ("PERSONAL_CONFIG", 0x10000);

class Activite {

// déclaration d'une propriété
    private $_id = 0;
    private $_db = NULL;
    public $infos = array('nom' => '',
        'organisateur' => '',
        'vue' => '',
        'flag' => 0,
        'nb_max' => '',
        'temps_max' => '',
        'delais_min' => 0,
        'etat' => '');
    public $type_activite = array(
        0x1C95 => "Endurance tours limités", //001 110 0100 101 01
        0x2A95 => "Endurance temps limité",  //010 101 0100 101 01 
        0x3D3E => "Course d'orientation",    //011 110 1010 111 10
        0x4B3E => "Match",                   //100 101 1001 111 10
        0x14E => "Données phys. ou sportive" //000 000 1010 011 10
    );

    public function __construct() { // or any other method
        global $mysqli;
        $this->_db = $mysqli;
        $this->get_id();
    }

    /*
     * ------------------------------------------------------
     * Gestion des paramètres de l'activité
     * ------------------------------------------------------
     */

    public function get_id() {
        $result = $this->_db->query("SELECT id FROM activites WHERE etat>0");
        if ($result->num_rows > 0) {
            $ep = $result->fetch_assoc();
            $this->_id = $ep['id'];
        }
        if ($this->_id == 0) {
            $result = $this->_db->query("SELECT id FROM activites");
            if ($result->num_rows > 0) {
                $ep = $result->fetch_assoc();
                $this->_id = $ep['id'];
            }
        }
        return $this->_id;
    }

    public function set_id($id, $force = false) {
        if ($id > 0) {
            $curent_activite = 0;
            $result = $this->_db->query("SELECT id FROM activites WHERE etat='2'");
            if ($result->num_rows > 0) {
                $ep = $result->fetch_assoc();
                $curent_activite = $ep['id'];
            }
            //on ne pas changer la visualisation que si aucune activité n'edt en enregistrement
            if ($curent_activite == 0 || $force == true) {
                //on s'assure que toute les autres activitée sont à l'état désactivée
                $this->_db->query("UPDATE activites SET etat = '0' WHERE id != '" . $id . "'");
                //on mémorise l'activité selectionnée
                $this->_db->query("UPDATE activites SET etat = '1' WHERE id = '" . $id . "'");
                $this->_id = $id;
            }
        }
    }

    public function get_nb_max() {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT nb_max FROM activites WHERE id='" . $this->_id . "'");
            if ($result->num_rows > 0) {
                $ep = $result->fetch_assoc();
                return $ep['nb_max'];
            }
        }
    }

    public function get_flag() {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT flag FROM activites WHERE id='" . $this->_id . "'");
            if ($result->num_rows > 0) {
                $ep = $result->fetch_assoc();
                return $ep['flag'];
            }
        }
    }
    
    public function get_temps_max() {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT temps_max FROM activites WHERE id='" . $this->_id . "'");
            if ($result->num_rows > 0) {
                $ep = $result->fetch_assoc();
                return $ep['temps_max'];
            }
        }
    }

    public function get_list() {
        $result = $this->_db->query("SELECT id,nom FROM activites");
        if ($result->num_rows > 0) {
            return $result;
        }
        return array();
    }

    public function set_vue($vue) {
        if ($this->_id > 0) {
            //echo on enregistre le fichier de visualisation à utiliser
            $this->_db->query("UPDATE activites SET vue = '" . $vue . "' WHERE id = '" . $this->_id . "'");
        }
    }

    /*
     * ------------------------------------------------------
     * Gestion des méthodes de l'activité
     * ------------------------------------------------------
     */

    public function select() {
        if ($this->_id > 0) {
            $this->_db->query("UPDATE activites SET etat = '0' WHERE id != '" . $this->_id . "'");
            $this->_db->query("UPDATE activites SET etat = '1' WHERE id = '" . $this->_id . "'");
        }
    }

    public function start() {
        if ($this->_id > 0) {
            $this->_db->query("UPDATE activites SET etat = '2' WHERE id = '" . $this->_id . "'");
        }
    }

    public function stop() {
        if ($this->_id > 0) {
            $this->_db->query("UPDATE activites SET etat = '1' WHERE id = '" . $this->_id . "'");
        }
    }

    public function create() {
        $this->_db->query("INSERT INTO activites (nom,organisateur,flag,nb_max,temps_max,delais_min,vue) " .
                "VALUES ('Sans nom','Sans nom','334','10','30','0','tableau.php')");
        //on récupère l'ID
        $this->set_id($this->_db->insert_id, true);
    }

//DATE_FORMAT(date_activite, '%W %d %M %Y à %Hh%i')
    public function refresh() {
        if ($this->_id > 0) {
            $this->_db->query("SET lc_time_names = 'fr_FR'");
            $result = $this->_db->query("SELECT  "
                    . "nom, organisateur, flag, etat, vue, nb_max, temps_max, delais_min "
                    . "FROM activites WHERE id = '" . $this->_id . "'");
            if ($result->num_rows > 0) {
                $ep = $result->fetch_assoc();
                $this->infos['nom'] = $ep['nom'];
                $this->infos['organisateur'] = $ep['organisateur'];
                $this->infos['flag'] = intval($ep['flag']);
                $this->infos['nb_max'] = $ep['nb_max'];
                $this->infos['temps_max'] = intval($ep['temps_max']);
                $this->infos['delais_min'] = intval($ep['delais_min']);
                $this->infos['vue'] = $ep['vue'];
                $this->infos['etat'] = intval($ep['etat']);
                return $this->infos;
            }
        }
        return false;
    }

    public function delete() {
        $this->delete_all_datas();
        $this->delete_all_participants();
        $temp_id = $this->_id;
        $result = $this->_db->query("SELECT id FROM activites WHERE id != '".$this->_id."'");
        if ($result->num_rows > 0) {
            $ep = $result->fetch_assoc();
            $this->set_id($ep['id']);
        }
        $this->_db->query("DELETE FROM activites WHERE id = '" . $temp_id . "'");
        
    }

    public function save() {
        if ($this->_id > 0) {
            
            $query = "UPDATE activites SET ";
            if(!empty($this->infos["nom"]))         {$query .= "nom = '" . $this->infos["nom"]."' ,";}
            if(!empty($this->infos["organisateur"])){$query .= "organisateur = '" . $this->infos["organisateur"]."' ,";}
            if(!empty($this->infos["nb_max"]))      {$query .= "nb_max = '" . $this->infos["nb_max"]."' ,";}
            if(!empty($this->infos["temps_max"]))   {$query .= "temps_max = '" . $this->infos["temps_max"]."' ,";}
            $query .= "delais_min = '" . $this->infos["delais_min"]."' ,";
            if(!empty($this->infos["flag"]))        {$query .= "flag = '" . $this->infos["flag"]."' ";}
            $query .= " WHERE id = '" . $this->_id . "'";
            $this->_db->query($query);
            return true;
        }
        return false;
    }

    /*
     * ------------------------------------------------------
     * Gestion des participants
     * ------------------------------------------------------
     */

    public function set_ref_id($id_participant, $ref_id) {
        if ($id_participant > 0) {
            $this->_db->query("UPDATE participants SET ref_id = '" . $ref_id . "' "
                    . "WHERE id = '" . $ref_id . "'");
            return true;
        }

        return false;
    }

    public function add_participants($id_user) {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT id FROM participants WHERE " .
                    "id_activite = '" . $this->_id . "' AND " .
                    "id_user = '" . $id_user . "'");
            if ($result->num_rows == 0) {
                $this->_db->query("INSERT INTO participants (id_user, id_activite) VALUES ('" . $id_user . "','" . $this->_id . "')");
                return true;
            }
        }
        return false;
    }

    public function get_participants() {
        if ($this->_id > 0) {

            $result = $this->_db->query(
                    "SELECT * FROM"
                    . "("
                    . "SELECT association as id_participant,ref_id,"
                    . "("
                    . "SELECT GROUP_CONCAT(users.classe,' - ',users.nom,' ',users.prenom SEPARATOR '<br/>') "
                    . "FROM participants, users WHERE "
                    . "participants.id_user=users.id AND "
                    . "participants.association = id_participant "
                    . "GROUP BY association "
                    . "ORDER BY nom"
                    . ") as nom "
                    . "FROM participants WHERE "
                    . "association = participants.id AND "
                    . "id_activite = '" . $this->_id . "' "
                    . "UNION ALL "
                    . "SELECT participants.id as id_participant,ref_id,"
                    . "CONCAT("
                    . "users.classe,' - ',users.nom,' ',users.prenom"
                    . ") as nom "
                    . "FROM participants, users "
                    . "WHERE "
                    . "participants.id_user=users.id AND "
                    . "participants.association IS NULL AND "
                    . "participants.id_activite = '" . $this->_id . "' "
                    . ") as sel ORDER BY sel.nom"
            );

            if ($result->num_rows > 0) {
                return $result;
            }
        }
        return false;
    }

    public function get_participantsToAdd($classe) {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT id,nom,prenom,classe FROM users WHERE id NOT IN " .
                    "(SELECT id_user as id FROM participants WHERE id_activite = '" . $this->_id . "') " .
                    "AND classe = '" . $classe . "'");
            if ($result->num_rows > 0) {
                return $result;
            }
        }
        return array();
    }

    /*
     * ------------------------------------------------------
     * Liste les participants de l'activité
     * ------------------------------------------------------
     */

    public function get_participants_to_export() {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT participants.id, nom, prenom, classe, nais, sexe, association FROM users,participants"
                    . " WHERE users.id = participants.id_user AND id_activite = '" . $this->_id . "' ORDER BY nom");
            if ($result->num_rows > 0) {
                return $result;
            }
        }
        return array();
    }

    public function delete_participant($id_participants) {
        if (is_array($id_participants)) {
            foreach ($id_participants as $id_participant) {
                $result = $this->_db->query("SELECT id FROM datas WHERE id_participant = '" . $id_participant . "'");
                if ($result->num_rows == 0) {
                    $this->_db->query("DELETE FROM participants WHERE id = '" . $id_participant . "'");
                }
            }
        }
    }

    public function delete_all_participants() {
        $this->_db->query("DELETE FROM participants WHERE id = '" . $this->_id . "'");
    }

    /*
     * ------------------------------------------------------
     * Gestion des datas
     * ------------------------------------------------------
     */

    public function get_max_datas() {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT MAX(y.num) as max_count "
                    . "FROM ( "
                    . "SELECT COUNT(id_participant)as num FROM datas,participants "
                    . "WHERE id_participant = participants.id AND participants.id_activite = '" . $this->_id . "' "
                    . "GROUP BY id_participant) as y");
            $row = $result->fetch_assoc();
            return $row['max_count'];
        }
        return 0;
    }

    public function get_datas($id_participant) {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT id,data,temps FROM datas " .
                    "WHERE id_participant = '" . $id_participant . "'");
            return $result;
        }
        return array();
    }

    //recupération des dernière données à partir d'un index
    //utilisé seulement pour la mise à jour d'une de l'interface en AJAX
    public function get_all_datas() {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT datas.id,id_participant,data,temps "
                    . "FROM datas,participants "
                    . "WHERE id_participant = participants.id "
                    . "AND participants.id_activite = '" . $this->_id . "'");
            return $result;
        }
        return array();
    }
    
    //recupération des dernière données à partir d'un index
    //utilisé seulement pour la mise à jour d'une de l'interface en AJAX
    public function get_last_datas($last_index) {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT datas.id,id_participant,data,temps "
                    . "FROM datas,participants "
                    . "WHERE id_participant = participants.id "
                    . "AND participants.id_activite = '" . $this->_id . "' "
                    . "AND datas.id > '".$last_index."'");
            return $result;
        }
        return array();
    }
    
    /*
     * ------------------------------------------------------
     * Met en place les headers pour le téléchargement du fichier
     * ------------------------------------------------------
     */

    public function get_assoc_parent($id_participant) {
        if ($this->_id > 0) {
            $result = $this->_db->query(
                    "SELECT id from participants "
                    . "WHERE association IN ("
                    . "SELECT association "
                    . "FROM participants "
                    . "WHERE id = '".$id_participant."') "
                    . "AND id=association");
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $id_participant = $row['id'];
            }
        }
        return $id_participant;
    }

    public function create_equipe($participants_id) {
        if (is_array($participants_id)) {
            foreach ($participants_id as $participant_id) {
                $this->_db->query("UPDATE participants SET association = '" . $participants_id[0] . "' WHERE " .
                        "id_activite = '" . $this->_id . "' AND id = '" . $participant_id . "'");
            }
        }
    }

    public function delete_equipe($associations_id) {
        if (is_array($associations_id)) {
            foreach ($associations_id as $association_id) {
                $this->_db->query("UPDATE participants SET association = NULL WHERE " .
                        "id_activite = '" . $this->_id . "' AND association = '" . $association_id . "'");
            }
        }
    }

    public function delete_all_datas() {
        
        $this->_db->query("DELETE FROM datas WHERE id IN "
                ."(SELECT datas.id FROM datas,participants WHERE id_participant = participants.id AND participants.id_activite = '" . $this->_id . "')");
    }
}
