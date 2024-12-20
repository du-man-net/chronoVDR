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


class Import {

    private $_file;
    private $_file_array = array();
    private $_eleves = array();

    public function __construct($file) { // or any other method
        $this->_file = $file;
        $this->_file_array = array();
        $this->_eleve = array();
    }

    public function is_csv_file() {
        $allow_type = array(
            'text/csv',
            'text/plain',
            'application/csv',
            'text/comma-separated-values',
            'application/excel',
            'application/vnd.ms-excel',
            'application/vnd.msexcel',
            'text/anytext',
            'application/octet-stream',
            'application/txt',
        );
        if (in_array($this->_file['type'], $allow_type)) {
            return true;
        } else {
            return false;
        }
    }

    public function read_file() {
        //on enregistre le fichier
        move_uploaded_file($this->_file['tmp_name'], "files/" . $this->_file['name']);
        //on ouvre le fichier en lecture
        $tmp_file = fopen("files/" . $this->_file['name'], "r") or die("Unable to open file!");
        //on enregistre les données dans un tableau
        $this->file_array = file('files/' . $this->_file['name']); # read file into array
        fclose($tmp_file); //close the file after read
        unlink("files/" . $this->_file['name']);
    }

    public function getElevesArray($classe = 'n_classé') {
        $csv_from = '';
        if (strpos($this->file_array[0], ';Né(e)') > 0) {
            $csv_from = 'pronote';
        }

        $i = 0;
        foreach ($this->file_array as $row) {
            if ($i > 0) { //on importe pas les entètes
                if ($csv_from == 'pronote'){
                    $eleve = $this->getInfoFromPronoteRow($row);
                    $eleve['classe'] = $classe;
                    $this->_eleves[$i - 1] = $eleve;
                    //var_dump($eleve);
                }
            }
            $i++;
        }
        return $this->_eleves;
    }
      
    private function getInfoFromPronoteRow($row) {
        $eleve = array();
        $infos = explode(";", $row);
        $eleve['nais'] = $infos[2];
        $eleve['nais'] = str_replace('"', '', $eleve['nais']);
        $noms = explode(" ", $infos[0]); //on récupère "prénom nom"
        $j = 0;
        foreach ($noms as $n) {
            $n = str_replace('"', '', $n);
            if ($j == (count($noms) - 1)) {
                $eleve['prenom'] = $n;
            } else {
                if ($j == 0) {
                    $eleve['nom'] = $n;
                } else {
                    $eleve['nom'] .= " " . $n;
                }
            }
            $j++;
        }
        return $eleve;
    }
    
}
