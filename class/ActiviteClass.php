<?php

/* 
 * Copyright (C) 2024 gleon
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

class Activite {

// déclaration d'une propriété
    private $_id = 0;
    private $_log = "";
    private $_db = NULL;
    private $_participants = array();
    private $_new_participants = array();
    private bool $_archived = false;
    public $infos = array('nom' => '',
        'organisateur' => '',
        'date_activite' => '',
        'date_fr' => '',
        'vue' => '',
        'repetition' => '',
        'identification' => '',
        'nb_max' => '',
        'temps_max' => '',
        'archived' => '',
        'etat' => '');

    public function __construct($mysqli) { // or any other method
        $this->_db = $mysqli;
        $this->get_id();
    }

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

    public function set_id($id) {
        if($id>0){
            $curent_activite=0;
            $result = $this->_db->query("SELECT id FROM activites WHERE etat='2'");
            if ($result->num_rows > 0) {
                $ep = $result->fetch_assoc();
                $curent_activite = $ep['id'];
            }
            //on ne pas changer la visualisation que si aucune activité n'edt en enregistrement
            if($curent_activite==0){
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

    public function get_temps_max() {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT TIME_TO_SEC(temps_max) as secondes FROM activites WHERE id='" . $this->_id . "'");
            if ($result->num_rows > 0) {
                $ep = $result->fetch_assoc();
                return $ep['secondes'];
            }
        }
    }

    public function set_vue($vue) {
        if ($this->_id > 0) {
            //echo on enregistre le fichier de visualisation à utiliser
            $this->_db->query("UPDATE activites SET vue = '" . $vue . "' WHERE id = '" . $this->_id . "'");
        }
    }
    
    public function set_password($password) {
        if ($this->_id > 0) {
            //echo on enregistre le fichier de visualisation à utiliser
            $this->_db->query("UPDATE activites SET password = '" . $password . "' WHERE id = '" . $this->_id . "'");
        }
    }
    public function get_password() {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT password FROM activites WHERE id='" . $this->_id . "'");
            if ($result->num_rows > 0) {
                $ep = $result->fetch_assoc();
                return $ep['password'];
            }
        }
    }
    
    public function set_archived(bool $value) {
        if ($this->_id > 0) {
//echo 'set archieved';
            $this->_archived = $value;
            $this->_db->query("UPDATE activites SET archived = '" . $this->_archived . "' WHERE id = '" . $this->_id . "'");
        }
    }

    public function create(): bool {
        $this->_db->query("INSERT INTO activites (nom,organisateur,date_activite,repetition,identification,nb_max,temps_max) " .
                "VALUES ('" .
                $this->infos['nom'] . "','" .
                $this->infos['organisateur'] . "','" .
                $this->infos['date_activite'] . "','" .
                $this->infos['repetition'] . "','" .
                $this->infos['identification'] . "','" .
                $this->infos['nb_max'] . "','" .
                $this->infos['temps_max'] . "')");
//on récupère l'ID
        $this->_id = $this->_db->insert_id;
        return $this->_id;
    }

    public function list() {
        $result = $this->_db->query("SELECT id,nom FROM activites");
        if ($result->num_rows > 0) {
            return $result;
        }
        return array();
    }

//DATE_FORMAT(date_activite, '%W %d %M %Y à %Hh%i')
    public function refresh() {
        if ($this->_id > 0) {
            $this->_db->query("SET lc_time_names = 'fr_FR'");
            $result = $this->_db->query("SELECT  "
                    . "DATE_FORMAT(date_activite, '%d/%m/%Y') as date_fr,"
                    . "nom, organisateur, repetition, identification, etat, archived, date_activite, vue, nb_max, temps_max "
                    . "FROM activites WHERE id = '" . $this->_id . "'");
            if ($result->num_rows > 0) {
                $ep = $result->fetch_assoc();
                $this->infos['nom'] = $ep['nom'];
                $this->infos['organisateur'] = $ep['organisateur'];
                $this->infos['date_fr'] = $ep['date_fr'];
                $this->infos['date_activite'] = $ep['date_activite'];
                $this->infos['repetition'] = $ep['repetition'];
                $this->infos['archived'] = $ep['archived'];
                $this->infos['identification'] = $ep['identification'];
                $this->infos['temps_max'] = $ep['temps_max'];
                $this->infos['nb_max'] = $ep['nb_max'];
                $this->infos['vue'] = $ep['vue'];
                $this->infos['etat'] = $ep['etat'];
                $this->archived = $ep['archived'];
                return true;
            }
        }
        return false;
    }

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

    public function save() {
        if ($this->_id > 0) {
            $this->_db->query("UPDATE activites SET nom = '" . $this->infos["nom"] .
                    "', organisateur = '" . $this->infos["organisateur"] .
                    "', date_activite = '" . $this->infos["date_activite"] .
                    "', repetition = '" . $this->infos["repetition"] .
                    "', identification = '" . $this->infos["identification"] .
                    "', temps_max = '" . $this->infos["temps_max"] .
                    "', nb_max = '" . $this->infos["nb_max"] .
                    "' WHERE id = '" . $this->_id . "'");
            return true;
        }
        return false;
    }

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
                    "("
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
                    . "id_activite = '" . $this->_id . "'"
                    . ") UNION ALL("
                    . "SELECT participants.id as id_participant,ref_id,"
                    . "CONCAT(users.classe,' - ',users.nom,' ',users.prenom) as nom "
                    . "FROM participants, users "
                    . "WHERE "
                    . "participants.id_user=users.id AND "
                    . "participants.association IS NULL AND "
                    . "participants.id_activite = '" . $this->_id . "' "
                    . "ORDER BY nom"
                    . ")"
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

    public function get_max_datas() {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT MAX(y.num) as max_count "
                    . "FROM ( "
                    . "SELECT COUNT(id_participant)as num FROM datas "
                    . "WHERE id_activite = '" . $this->_id . "' "
                    . "GROUP BY id_participant) y");
            $row = $result->fetch_assoc();
            return $row['max_count'];
        }
        return 0;
    }
    
    public function get_datas($id_participant) {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT data,temps FROM datas " .
                    "WHERE id_activite = '" . $this->_id . "' AND " .
                    "id_participant = '" . $id_participant . "'");
            return $result;
        }
        return array();
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
    
    public function delete_all_datas(){
        $this->_db->query("DELETE FROM datas WHERE id_activite = '".$this->_id."'");
    }
    
    public function delete_participant($id_participants){
        if (is_array($id_participants)) {
            foreach ($id_participants as $id_participant) {
                
                $result = $this->_db->query("SELECT id FROM datas WHERE id_participant = '".$id_participant."' "
                                . "AND id_activite = '".$this->_id."'");
                if ($result->num_rows == 0) {
                    $this->_db->query("DELETE FROM participants WHERE id_activite = '".$this->_id."' "
                                    . "AND id = '".$id_participant."'");
                }
            }
        }
    }
    
    public function delete_all_participants(){
        $this->_db->query("DELETE FROM participants WHERE id = '".$this->_id."'");
    }
    
    public function delete(){
        $this->delete_all_datas();
        $this->delete_all_participants();
        $this->_db->query("DELETE FROM activite WHERE id = '".$this->_id."'");
    }  
    
    
}
