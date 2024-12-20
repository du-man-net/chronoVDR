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

/**
 * Description of UsersClass
 *
 * @author gleon
 */
require_once "db.php";

class Users {

    //put your code here
    public function __construct($mysqli) { // or any other method
        $this->_db = $mysqli;
    }

    public function findUser($userInfos, &$classe) {
        $result = $this->_db->query("SELECT id,classe FROM users WHERE " .
                "nom = '" . $userInfos[0] . "' AND " .
                "prenom = '" . $userInfos[1] . "' AND " .
                "nais = '" . $userInfos[2] . "'");
        $usr = $result->fetch_assoc();
        if (is_array($usr)) {
            $classe  = $usr['classe'];
            return $usr['id'];
        }
        return false;
    }

    public function insertUser($userInfo) {
        $oldClasse = '';
        $newClasse = $userInfo[3];
        $idUser = $this->findUser($userInfo, $oldClasse);
        
        if ($idUser) {
            if ($oldClasse != $newClasse) {
                $this->_db->query("UPDATE users SET classe = '" . $newClasse . "' WHERE id = '" . $idUser . "'");
            }
        } else {
            $this->_db->query("INSERT INTO users (nom, prenom, nais, classe) VALUES " .
                    "('" . $userInfo[0] . "','" . $userInfo[1] . "','" . $userInfo[2] . "','" . $newClasse . "')");
        }
    }
    
    public function getUsersFromClasse($classe){
        $result = $this->_db->query("SELECT * FROM users WHERE classe = '" . $classe . "'");
        if(!empty($result)){
            return $result;
        }
        return array();
    }
    
    public function cleanUsersFromClasse($users, $classe){
        $usrs = $this->getUsersFromClasse($classe);
        foreach ($usrs as $usr) {
            $find = false;
            foreach ($users as $user) {
                if($this->getUserInfos($user)){
                    if ($user[0] == $usr['nom'] &&
                        $user[1] == $usr['prenom'] &&
                        $user[2] == $usr['nais']) {
                        $find = true;
                    }
                }
            }
            if(!$find){
                cleanUser($usr["id"]);
            }
        }
    }  
    
    public function cleanUser($userId){
        $this->_db->query("UPDATE users SET classe = 'corbeille' WHERE id = '" . $userId . "'");
    }
    
    public function getUserInfos(&$user){
        $user = substr($user, 0, -1);
        if (strlen($user) > 8) {
            $user = explode(",", $user);  
            return $user;
        }
        return false;
    }
    
    public function importUsers($lstusers) {
        $users = explode("\n", $lstusers);
        foreach ($users as $user) {
            if($this->getUserInfos($user)){
                $classe = $user[3];
                $this->insertUser($user);
            }
        }
        $this->cleanUsersFromClasse($users,$classe);
    }

    public function getClasses() {
        $result = $this->_db->query("SELECT DISTINCT classe FROM users");
        if ($result->num_rows > 0) {
            return $result;
        }
        return array();
    }
    
    public function deleteUsersBin(){
        $this->_db->query("DELETE * FROM users Where classe = 'corbeille' "
                                    . "AND users.id NOT IN (SELECT id_users FROM participants))");
    }
}
