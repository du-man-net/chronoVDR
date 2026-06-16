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

/**
 * Description of parcours
 *
 * @author gleon
 */
require_once "db.php";

class Parcours {

    private $_db = NULL;
    Private $_id = 0;

    public function __construct() { // or any other method
        global $mysqli;
        $this->_db = $mysqli;
    }

//********************************************************
//********************************************************
// Gestion des courses d'orientation à chaque participants
//********************************************************
//********************************************************

    public function create_co($id_participant) {
        $result = $this->_db->query("SELECT id FROM activites WHERE etat>0");
        if ($result->num_rows > 0) {
            $ep = $result->fetch_assoc();
            $id_activite = $ep['id'];

            $this->_db->query("INSERT INTO co (id_participant,id_activite) "
                    . "VALUES ('" . $id_participant . "','" . $id_activite . "')");
            return $this->_db->insert_id;
        }
    }

    public function setCoParcours($id_co, $id_parcours) {
        if ($id_parcours < 0) {
            $this->_db->query("UPDATE co SET id_parcours = NULL WHERE id = '" . $id_co . "'");
        } else {
            $this->_db->query("UPDATE co SET id_parcours = '" . $id_parcours . "' WHERE id = '" . $id_co . "'");
        }
    }

//********************************************************
//********************************************************
// Gestion des balises
//********************************************************
//********************************************************

    public function create_balise($tag, $nom = "") {
        if (!$this->find_balise_tag($tag)) {
            if (strlen($nom) == 0) {
                $nom = 'vdr';
            }
            $idx = 0;
            $last_nom = $this->find_last_nom_balise($nom, true);
            if ($last_nom) {
                $endstr = substr($last_nom, strlen($nom));
                if (strlen($endstr) > 0) {
                    $idx = $endstr + 1;
                } else {
                    $idx = 2;
                }
            } else {
                $idx = "";
            }
            $this->_db->query("INSERT INTO balises (tag, nom) VALUES ('" . $tag . "','" . $nom . $idx . "')");
            return $this->_db->insert_id;
        }
    }

    public function set_balise_nom($id_balise, $nom) {
        $idb = $this->get_bailse_nom($id_balise);
        if (!$idb) {
            $this->_db->query("UPDATE balises SET nom = '" . $nom . "' WHERE id = '" . $id_balise . "'");
            return true;
        }
        return false;
    }

    public function get_bailse_nom($id_balise) {
        $query = "SELECT nom FROM balises WHERE id = '" . $id_balise . "'";
        $result = $this->_db->query($query);
        $r = $result->fetch_assoc();
        if (is_array($r)) {
            return $r['nom'];
        }
    }

    public function get_balise_tag($id_balise) {
        $result = $this->_db->query("SELECT tag FROM balises WHERE id = '" . $id_balise . "'");
        $r = $result->fetch_assoc();
        if (is_array($r)) {
            return $r['tag'];
        }
    }

    public function find_balise_tag($tag) {
        $result = $this->_db->query("SELECT id FROM balises WHERE tag = '" . $tag . "'");
        if ($result->num_rows > 0) {
            return true;
        }
        return false;
    }

    public function set_balise_used($id_balise, $used) {
        $this->_db->query("UPDATE balises SET used = '" . $used . "' WHERE id = '" . $id_balise . "'");
    }

    public function delete_balise($id_balise) {
        $tag = $this->get_balise_tag($id_balise);
        echo "SELECT tag FROM co_datas WHERE tag = '" . $tag . "'";
        $result = $this->_db->query("SELECT tag FROM co_datas WHERE tag = '" . $tag . "'");
        if ($result->num_rows == 0) {
            //on ne détruit la balise que si le tag n'est pas utilisée
            echo "DELETE FROM balises WHERE id = '" . $id_balise . "'";
            $this->_db->query("DELETE FROM balises WHERE id = '" . $id_balise . "'");
            return true;
        } else {
            //sinon, on la masque
            echo "UPDATE balises SET used = '0' WHERE id_balise = '" . $id_balise . "'";
            $this->_db->query("UPDATE balises SET used = '0' WHERE id_balise = '" . $id_balise . "'");
        }
        return false;
    }

    public function find_last_nom_balise($nom, $used = false) {
        $query = "SELECT nom FROM balises WHERE nom LIKE '" . $nom . "%'";
        if ($used) {
            $query .= " AND used = '" . $used . "'";
        }
        $query .= " ORDER BY nom DESC";
        $result = $this->_db->query($query);
        $r = $result->fetch_assoc();
        if (is_array($r)) {
            return $r['nom'];
        }
        return false;
    }

//********************************************************
//********************************************************
// Gestion des paramètres du parcours
//********************************************************
//********************************************************

    public function create() {
        $defName = "Sans nom";
        $query = "SELECT nom,SUBSTRING(nom, 9)as defname FROM parcours ORDER BY (defname+0) DESC";
        $result = $this->_db->query($query);
        $r = $result->fetch_assoc();
        $idx = 0;
        if (is_array($r)) {
            $endstr = substr($r['nom'], strlen($defName));
            if (strlen($endstr) > 0) {
                $idx = intval($endstr) + 1;
            } else {
                $idx = 2;
            }
        } else {
            $idx = "";
        }
        $this->_db->query("INSERT INTO parcours (nom) VALUES ('" . $defName . $idx . "')");
        $this->_id = $this->_db->insert_id;
        return $this->_id;
    }

    public function set_id($id) {
        if ($id > 0) {
            $result = $this->_db->query("SELECT id FROM parcours WHERE id = '" . $id . "'");
            $r = $result->fetch_assoc();
            if (is_array($r)) {
                $this->_id = $id;
                return true;
            }
        }
        return false;
    }

    public function get_nom() {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT nom FROM parcours WHERE id = '" . $this->_id . "'");
            if ($result->num_rows > 0) {
                $ep = $result->fetch_assoc();
                return $ep['nom'];
            }
        }
    }

    public function set_nom($nom) {
        if ($this->_id > 0) {
            $this->_db->query("UPDATE parcours SET nom = '" . $nom . "' WHERE id = '" . $this->_id . "'");
        }
    }

    public function set_ordre($ordre) {
        if ($this->_id > 0) {
            $this->_db->query("UPDATE parcours SET ordre = '" . $ordre . "' WHERE id = '" . $this->_id . "'");
        }
    }

    public function get_list() {
        $result = $this->_db->query("SELECT id,nom FROM parcours ORDER BY nom");
        return $result;
    }

    public function delete() {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT id_parcours FROM co,co_datas WHERE "
                    . " co.id_parcours = '" . $this->_id . "' AND "
                    . "co.id = co_datas.id_co");
            if ($result->num_rows == 0) {
                //on ne dÃ©truit un parcours que si elle n'est pas utilisÃ©
                //on suprime la liste des balise associÃ©e
                $this->_db->query("DELETE FROM liste_balises WHERE id_parcours = '" . $this->_id . "'");
                $this->_db->query("DELETE FROM co WHERE id_parcours = '" . $this->_id . "'");
                $this->_db->query("DELETE FROM parcours WHERE id = '" . $this->_id . "'");
                return true;
            }
        }
        return false;
    }

    public function add_balise($id_balise, $nom = "", $ordre = 0, $value = 1) {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT id FROM liste_balises WHERE " .
                    "id_parcours = '" . $this->_id . "' AND " .
                    "id_balise = '" . $id_balise . "'");
            if ($result->num_rows == 0) {
                if ($ordre == 0) {
                    $ordre = $this->get_maxOrdreBalise() + 1;
                }
                $this->_db->query("INSERT INTO liste_balises (id_parcours, id_balise, ordre, nom, value) "
                        . "VALUES ('" . $this->_id . "','" . $id_balise . "', '" . $ordre . "', '" . $nom . "', '" . $value . "')");
                return true;
            }
        }
        return false;
    }

    public function set_info($id_balise, $nom = " ", $ordre = 0, $value = 0) {
        if ($this->_id > 0) {

            $query = "";
            $nombalise = "";
            if ($nom != " ") {
                $result = $this->_db->query("SELECT balises.nom FROM balises,liste_balises WHERE "
                        . "liste_balises.id_balise = balises.id AND "
                        . "liste_balises.id = '" . $id_balise . "'");
                if ($result->num_rows > 0) {
                    $res = $result->fetch_assoc();
                    $nombalise = $res['nom'];
                }
                if ($nombalise != $nom) {
                    $query .= "nom = '" . $nom . "' ,";
                }
            }
            if ($ordre != 0) {
                $query .= "ordre = '" . $ordre . "' ,";
            }
            if ($value != 0) {
                $query .= "value = '" . $value . "' ,";
            }
            if (strlen($query) > 0) {
                $query = "UPDATE liste_balises SET " . $query;
                $query = substr($query, 0, -1) . " WHERE "
                        . "id = '" . $id_balise . "'";
                $this->_db->query($query);
                return $query;
            }
        }
        return false;
    }

    public function remove_balise($id_balise) {
        if ($this->_id > 0) {
            $tag = $this->get_baliseTag($id_balise);
            //si des enregistrements utilisent cette balise, on ne peut la supprimer
            $result = $this->_db->query("SELECT tag FROM co, co_datas "
                    . "WHERE co_datas.tag = '" . $tag . "' "
                    . "AND co_datas.id_co = co.id "
                    . "AND co.id_parcours = '" . $this->_id . "'");

            if ($result->num_rows == 0) {
                $this->_db->query("DELETE FROM liste_balises WHERE id = '" . $id_balise . "'");
                return true;
            }
        }
        return false;
    }

    public function get_liste_balises() {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT liste_balises.id as id, "
                    . "balises.tag, "
                    . "liste_balises.nom as nom, "
                    . "balises.nom as nombalise, "
                    . "liste_balises.value, "
                    . "liste_balises.ordre "
                    . "FROM balises,liste_balises WHERE "
                    . "balises.id = liste_balises.id_balise AND "
                    . "liste_balises.id_parcours = '" . $this->_id . "' "
                    . "ORDER by ordre");
            if ($result->num_rows > 0) {
                return $result;
            }
        }
        return false;
    }

    public function get_maxOrdreBalise() {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT ordre FROM liste_balises WHERE "
                    . "liste_balises.id_parcours = '" . $this->_id . "' "
                    . "ORDER by ordre DESC");
            if ($result->num_rows > 0) {
                $res = $result->fetch_assoc();
                return $res['ordre'];
            } else {
                return 1;
            }
        }
        return array();
    }

    public function get_baliseTag($id_balise) {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT balises.tag FROM balises,liste_balises "
                    . "WHERE liste_balises.id='" . $id_balise . "' "
                    . "AND liste_balises.id_balise=balises.id");
            if ($result->num_rows > 0) {
                $res = $result->fetch_assoc();
                return $res['tag'];
            }
        }
        return "";
    }

    public function get_balisesToAdd() {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT id,nom,tag FROM balises WHERE id NOT IN " .
                    "(SELECT id_balise as id FROM liste_balises WHERE id_parcours = '" . $this->_id . "') "
                    . "ORDER BY nom");
            if ($result->num_rows > 0) {
                return $result;
            }
        }
        return array();
    }

    public function get_all_parcours($id_activite) {
        if ($id_activite > 0) {
            $result = $this->_db->query("SELECT id,nom,ordre FROM parcours ORDER BY NOM");
            if ($result->num_rows > 0) {
                return $result;
            }
        }
        return array();
    }

    public function get_last_parcours($id_activite, $last_index) {
        if ($id_activite > 0) {
            $result = $this->_db->query("SELECT id,nom,ordre FROM parcours WHERE parcours.id > '" . $last_index . "'  ORDER BY NOM");
            if ($result->num_rows > 0) {
                return $result;
            }
        }
        return array();
    }

    //recupération de toutes les données du parcours
    public function get_all_datas($id_activite) {



        if ($id_activite > 0) {

            $result = $this->_db->query("SELECT "
                    . "c.t_start, "
                    . "c.t_end, "
                    . "c.id AS id_co, "
                    . "c.id_parcours, "
                    . "c.id_participant, "
                    . "d.id AS id_data, "
                    . "d.tag, "
                    . "d.temps, "
                    . "IF(b.nom IS NULL, d.tag, b.nom) AS nom "
                    . "FROM "
                    . "co AS c, "
                    . "co_datas AS d "
                    . "LEFT JOIN balises AS b "
                    . "ON "
                    . "d.tag = b.tag "
                    . "WHERE "
                    . "c.id = d.id_co AND c.id_activite = '" . $id_activite . "' "
                    . "ORDER BY "
                    . "id_participant,id_co,id_data ");

            if ($result->num_rows > 0) {
                return $result;
            }
        }
        return array();
    }

    //recupération des dernière données à partir d'un index
    //utilisé seulement pour la mise à jour d'une de l'interface en AJAX
    public function get_last_datas($id_activite, $last_index) {
        if ($id_activite > 0) {
            $result = $this->_db->query("SELECT c.t_start, c.t_end, c.id as id_co, c.id_parcours, c.id_participant, "
                    . "d.id as id_data, d.tag, d.temps, "
                    . "(SELECT IF(b.nom='',l.nom, b.nom)) as nom "
                    . "FROM co_datas as d "
                    . "INNER JOIN co as c ON d.id_co = c.id "
                    . "INNER JOIN balises as b ON d.tag = b.tag "
                    . "INNER JOIN liste_balises as l ON b.id = l.id_balise AND l.id_parcours = c.id_parcours "
                    . "WHERE c.id_activite = '" . $id_activite . "' "
                    . "AND d.id > '" . $last_index . "' "
                    . "ORDER BY id_data");
            if ($result->num_rows > 0) {
                return $result;
            }
        }
        return array();
    }

    public function get_curent_co() {
        //co ac
        $result = $this->_db->query("SELECT co.id as id_co, "
                . "co.etat as etat, "
                . "co.id_participant as id_participant, "
                . "parcours.id as id_parcours, "
                . "parcours.nom "
                . "FROM parcours,co,activites "
                . "WHERE parcours.id = co.id_parcours "
                . "AND co.id_activite=activites.id "
                . "AND activites.etat>0 "
                . "AND co.etat<2");
        if ($result->num_rows > 0) {
            return $result;
        }
        return array();
    }
}
