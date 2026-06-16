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

require_once "db.php";

class Admin {

    public function __construct() { // or any other method
        global $mysqli;
        $this->_db = $mysqli;
    }

    public function create($login, $nom, $password) {
        if (strlen($login) == 0) {
            return (-1); //login non renseigné
        }
        if ($this->isUnique($login)) {
            return (-2); //login existe
        }
        if (strlen($password) == 0) {
            return(-3); //password trop court
        }
        if (strlen($nom) == 0) {
            return(-4); //nom pas renseigné
        }
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $this->_db->query("INSERT INTO admin (login,nom,password) " .
                "VALUES ('" . $login . "','" . $nom . "','" . $hashed . "')");
        //on récupère l'ID
        return $this->_db->insert_id;
    }

    public function changePassword($id, $newPassword) {
        $passwordDecode = urldecode($newPassword);
        $hashed = password_hash($passwordDecode, PASSWORD_DEFAULT);
        $this->_db->query("UPDATE admin set password = '" . $hashed . "' WHERE $id='" . $id . "'");
    }

    public function setNom($id, $newNom) {
        $this->_db->query("UPDATE admin set nom = '" . $newNom . "' WHERE id='" . $id . "'");
    }

    public function setlogin($id, $newLogin) {
        $this->_db->query("UPDATE admin set login = '" . $newLogin . "' WHERE id='" . $id . "'");
    }

    public function isUnique($login) {
        $result = $this->_db->query("SELECT login FROM admin WHERE login = '" . $login . "' ");
        if ($result->num_rows > 0) {
            return true;
        }
        return false;
    }

    public function delete($id) {
        $this->_db->query("DELETE FROM admin WHERE id = '" . $id . "' ");
    }

    public function get_list() {
        $result = $this->_db->query("SELECT id,login,nom FROM admin");
        if ($result->num_rows > 0) {
            return $result;
        }
        return array();
    }

    public function auth($login, $password) {
        $result = $this->_db->query("SELECT id,login,nom,password FROM admin WHERE login='" . $login . "'");
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['id'] = $admin['id'];
                $_SESSION['login'] = $admin['login'];
                $_SESSION['nom'] = $admin['nom'];
                return true;
            }
        }
        return false;
    }
}
