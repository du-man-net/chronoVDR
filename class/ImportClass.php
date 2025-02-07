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


class Import {

    private $_file;
    private $_file_array = array();
    private $_eleves = array();
    private $_erreur = false;
    private $_erreur_message = '';
    
    public function __construct() { // or any other method

    }
    
    public function set_file($file){
        $this->_file = $file;
    }
    
    public function get_erreur(){
        return $this->_erreur;
    }

    public function get_erreur_message(){
        return $this->_erreur_message;
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

    public function getElevesArray($classe = '') {
        //on vide le tableau
        $this->_eleves=array();
        $this->_erreur_message="";
        $this->_erreur=false;
        
        //si on trouve une caractéristique pronote dans l'entête
        if (strpos($this->file_array[0], ';Né(e)')!==false) {
            if(!empty($classe)){
                $this->importFromPronote($classe);
                return $this->_eleves;
            }else{
                $this->_erreur_message = 'Vous devez choisir une classe.';
                $this->_erreur = true; 
                return array();
            }
        }
        /*
         * Importation depuis toutatice car manque d'information
        //si on trouve une caractéristique toutatice dans l'entête
        if (strpos($this->file_array[0], 'de l\'établissement')!==false) {
            $this->ImportFromToutatice();
            return $this->_eleves;
        }
         */
        
        //sinom on essaye de trouver les champs nom et prénom
        $firstLine = trim(strtolower($this->no_accent($this->file_array[0]))) ;
        if (strpos($firstLine, 'prenom')!==false && 
                strpos($firstLine, 'nom')!==false) {
            $colomnes = explode(",",$firstLine);
            $i=0; $titles=array();
            foreach($colomnes as $colomne){
                if($colomne == 'nom'){$titles['nom']=$i;}
                if($colomne == 'prenom'){$titles['prenom']=$i;}
                if($colomne == 'classe'){$titles['classe']=$i;}
                if($colomne == 'nais'){$titles['nais']=$i;}
                if($colomne == 'sexe'){$titles['sexe']=$i;}
                $i++;
            }
            //si les champs nais et sexe ne sont pas renseignés, on se débrouille
            if(empty($titles['sexe'])){
                if(empty($titles['nais'])){
                    $titles['nais']=3;
                    $titles['sexe']=4;  
                }else{
                    $titles['sexe']=4;
                }
            }              
            
            //sinom il y a un champ classe
            if(strpos($firstLine, 'classe')!==false){
                $this->importFromCSV($titles);
                return $this->_eleves;
                
            //si la case classe est remplie
            }elseif(!empty($classe)){
                $this->importFromCSV($titles,$classe);
                return $this->_eleves;
                
            //sinon, il manque la classe
            }else{
                $this->_erreur_message = 'Vous devez choisir une classe.';
                $this->_erreur = true; 
                return array();
            }
        }
        //Pas de solution
        $this->_erreur_message = 'Erreur de lecture du fichier';
        $this->_erreur = true; 
        return array();
    }
    
    private function importFromCSV($titles,$classe=""){
        $i=0;
        foreach ($this->file_array as $row) {
            if ($i > 0) { //on importe pas les entètes
                $eleve = $this->getEleveFromCsv(trim($row));
                $this->_eleves[$i]['nom'] = $eleve[$titles['nom']];
                $this->_eleves[$i]['prenom'] = $eleve[$titles['prenom']];
                if(empty($eleve[$titles['nais']])){
                    $this->_eleves[$i]['nais'] = "01/01/2000";
                }else{
                    $this->_eleves[$i]['nais'] = $eleve[$titles['nais']];
                }
                if(empty($eleve[$titles['sexe']])){
                    $this->_eleves[$i]['sexe'] = "M";
                }else{
                    $this->_eleves[$i]['sexe'] = $eleve[$titles['sexe']];
                }
                if(empty($classe)){
                    $this->_eleves[$i]['classe'] = $eleve[$titles['classe']];
                }else{
                    $this->_eleves[$i]['classe'] = $classe;
                }
            }
            $i++;
        }
    }
    
    private function getEleveFromCsv($row) {
        $eleve = explode(",", $row);
        return $eleve;
    }
    
    private function importFromPronote($classe){
        $i = 0;
        foreach ($this->file_array as $row) {
            if ($i > 0) { //on importe pas les entètes
                $eleve = $this->getEleveFromPronote(trim($row));
                $eleve['classe'] = $classe;
                $this->_eleves[$i - 1] = $eleve;
            }
            $i++;
        }
    }
    
    private function getEleveFromPronote($row) {
        $eleve = array();
        $infos = explode(";", str_replace('"', '', $row));
        $eleve['nais'] = str_replace('"', '', $infos[2]);
        $eleve['sexe'] = str_replace('"', '', strtoupper($infos[3]));
        $noms = explode(" ", $infos[0]); //on récupère "prénom nom"
        $j = 0;
        foreach ($noms as $n) {
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
        /*
         * on vire le filtrage nécessaire à la compatiblité avec toutatice
        $eleve['prenom'] = $this->no_accent($eleve['prenom']);
        $eleve['nom'] = str_replace('--',' ',$eleve['nom']);
        $eleve['nom'] = str_replace('-',' ',$eleve['nom']);
        $eleve['nom'] = $this->no_accent($eleve['nom']);
         */
        return $eleve;
    }
    
    /*
    private function ImportFromToutatice(){
        $i = 0;
        foreach ($this->file_array as $row) {
            $eleve = $this->getEleveFromToutatice(trim($row));
            $this->_eleves[$i] = $eleve;
            $i++;
        }
    }
    
    private function getEleveFromToutatice($row) {
        $eleve = array();
        $infos = explode(";", $row);
        $eleve['nom'] = $infos[0];
        $eleve['prenom'] = $infos[1];
        $eleve['nom'] = str_replace('--',' ',$eleve['nom']);
        $eleve['nom'] = str_replace('-',' ',$eleve['nom']);
        $classe = explode(" ", $infos[3]);
        $eleve['classe'] = $classe[1];
        return $eleve;
    }
    */ 
    
    private function no_accent($str){
        $accents = array('À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'Ā' => 'A', 'ā' => 'a', 'Ă' => 'A', 'ă' => 'a', 'Ą' => 'A', 'ą' => 'a', 'Ç' => 'C', 'ç' => 'c', 'Ć' => 'C', 'ć' => 'c', 'Ĉ' => 'C', 'ĉ' => 'c', 'Ċ' => 'C', 'ċ' => 'c', 'Č' => 'C', 'č' => 'c', 'Ð' => 'D', 'ð' => 'd', 'Ď' => 'D', 'ď' => 'd', 'Đ' => 'D', 'đ' => 'd', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'Ē' => 'E', 'ē' => 'e', 'Ĕ' => 'E', 'ĕ' => 'e', 'Ė' => 'E', 'ė' => 'e', 'Ę' => 'E', 'ę' => 'e', 'Ě' => 'E', 'ě' => 'e', 'Ĝ' => 'G', 'ĝ' => 'g', 'Ğ' => 'G', 'ğ' => 'g', 'Ġ' => 'G', 'ġ' => 'g', 'Ģ' => 'G', 'ģ' => 'g', 'Ĥ' => 'H', 'ĥ' => 'h', 'Ħ' => 'H', 'ħ' => 'h', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'Ĩ' => 'I', 'ĩ' => 'i', 'Ī' => 'I', 'ī' => 'i', 'Ĭ' => 'I', 'ĭ' => 'i', 'Į' => 'I', 'į' => 'i', 'İ' => 'I', 'ı' => 'i', 'Ĵ' => 'J', 'ĵ' => 'j', 'Ķ' => 'K', 'ķ' => 'k', 'ĸ' => 'k', 'Ĺ' => 'L', 'ĺ' => 'l', 'Ļ' => 'L', 'ļ' => 'l', 'Ľ' => 'L', 'ľ' => 'l', 'Ŀ' => 'L', 'ŀ' => 'l', 'Ł' => 'L', 'ł' => 'l', 'Ñ' => 'N', 'ñ' => 'n', 'Ń' => 'N', 'ń' => 'n', 'Ņ' => 'N', 'ņ' => 'n', 'Ň' => 'N', 'ň' => 'n', 'ŉ' => 'n', 'Ŋ' => 'N', 'ŋ' => 'n', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'Ō' => 'O', 'ō' => 'o', 'Ŏ' => 'O', 'ŏ' => 'o', 'Ő' => 'O', 'ő' => 'o', 'Ŕ' => 'R', 'ŕ' => 'r', 'Ŗ' => 'R', 'ŗ' => 'r', 'Ř' => 'R', 'ř' => 'r', 'Ś' => 'S', 'ś' => 's', 'Ŝ' => 'S', 'ŝ' => 's', 'Ş' => 'S', 'ş' => 's', 'Š' => 'S', 'š' => 's', 'ſ' => 's', 'Ţ' => 'T', 'ţ' => 't', 'Ť' => 'T', 'ť' => 't', 'Ŧ' => 'T', 'ŧ' => 't', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'Ũ' => 'U', 'ũ' => 'u', 'Ū' => 'U', 'ū' => 'u', 'Ŭ' => 'U', 'ŭ' => 'u', 'Ů' => 'U', 'ů' => 'u', 'Ű' => 'U', 'ű' => 'u', 'Ų' => 'U', 'ų' => 'u', 'Ŵ' => 'W', 'ŵ' => 'w', 'Ý' => 'Y', 'ý' => 'y', 'ÿ' => 'y', 'Ŷ' => 'Y', 'ŷ' => 'y', 'Ÿ' => 'Y', 'Ź' => 'Z', 'ź' => 'z', 'Ż' => 'Z', 'ż' => 'z', 'Ž' => 'Z', 'ž' => 'z');
        return strtr($str, $accents);
    }
}

