<?php

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set("display_errors", 1);
/*
 * Copyright (C) 2025 gleon
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
 * Description of VueClass
 *
 * @author gleon
 */
require_once 'ActiviteClass.php';

/*
 * ------------------------------------------------------
 * Class fille de la class Activite
 * On utilise que le parcours des données et des participants
 * ------------------------------------------------------
 */
class Vue extends Activite {

    private $_id = 0;
    private $_flag = 0;
    private $_nb_max = 0;
    //private $_temps_max = 0;

    public function __construct() { // or any other method
        parent::__construct();
        $this->_id = parent::get_id();
        $infos = parent::refresh();
        $this->_flag = intval($infos['flag']);
        //echo $infos['flag'];

        if ($this->_flag & IS_LIMIT_LAP) {
            $this->_nb_max = $infos['nb_max'];
        } else if ($this->_flag & IS_LIMIT_TIME) {
            //pas trouvé mieux pour l'instant
            $this->_nb_max = parent::get_max_datas();
        } else {
            $this->_nb_max = parent::get_max_datas();
        }
        if($this->_nb_max < 5){$this->_nb_max = 5;}
    }

    /*
     * ------------------------------------------------------
     * construction des descripteurs
     * ------------------------------------------------------
     */
    private function get_start_prefix() {
        if ($this->_flag & DATA_PER_TEST) {
            return "data";
        }
        if ($this->_flag & TIME_PER_LAP) {
            return "temps";
        }
        if ($this->_flag & TIME_SINCE_START) {
            return "temps";
        }
        if ($this->_flag & HOUR_PER_LAP) {
            return "heure";
        }
    }

    /*
     * ------------------------------------------------------
     * construction des descripteurs
     * ------------------------------------------------------
     */     
    private function get_end_prefix() {
        if ($this->_flag & SHOW_FINAL_TIME) {
            return "Temps";
        }
        if ($this->_flag & SHOW_NUMBER_LAPS) {
            return "Nb tours";
        }
        if ($this->_flag & SHOW_TOTAL_DATA) {
            return "Total";
        }
    }
    
     /*
     * ------------------------------------------------------
     * Vérificateur de flag
     * ------------------------------------------------------
     */

    private function check($flag) {
        //echo $this->_flag.' & '.$flag.' = '.($this->_flag & $flag)."<br/>";
        if (($this->_flag & $flag) == $flag) {
            return true;
        }
        return false;
    }

     /*
     * ------------------------------------------------------
     * constructuer d'entête de tableau
     * ------------------------------------------------------
     */
    
    public function make_headers() {

        $index = 1;
        $line = [];
        
        //DEBUT
        //La colomne début compte pour l'index 0 les données
        $prefix = $this->get_start_prefix();
        if ($this->_flag & SHOW_START) {
            $line[$index] = "Départ";
        }else{
            $line[$index] = $prefix . $index;
        }
  
        //MILIEU
        // nombre de tours limités : de 1 à nb_max
        
        for($i = 2; $i <= $this->_nb_max ; $i++){
            $line[$i] = $prefix . $i;
        }
        
        //FIN
        // colomne bilan à nb_max
        $endtitle = $this->get_end_prefix();
        if ($this->_flag & IS_LIMIT) {
            $line[$this->_nb_max+1] = $endtitle;
        }
        return $line;
    }

    /*
     * ------------------------------------------------------
     * construction des données
     * On commence à l'index 1
     * On lit les données jusqu'au bout
     * On complete jusqu'a nb_max
     * On rajoute la colomne de résultats éventuels
     * ------------------------------------------------------
     */

    public function make_datas($id_participant,$force_all = false) {

        $index = 1;
        $line = [];
        $dt_start = null;
        $dt1 = null;
        $dt2 = null;
        $nb_laps = 0;
        $total_data = 0;
        $idy_time = 0;
        
        //si les donnée et le temps sont présents,
        //on place les donées de temps dans une seconde ligne
        if ($this->check(SHOW_DATA) || $force_all) {
            if ($this->check(SHOW_TIME) || $force_all) {
                $idy_time = 1;
            }
        }
       
        $datas = parent::get_datas($id_participant);
        foreach ($datas as $data) {

            if ($this->check(SHOW_DATA) || $force_all) {
                $line[0][$index] = $data['data'];
                $total_data += intval($data['data']);
            }
 
            if ($this->check(SHOW_TIME) || $force_all) {
                if ($index == 1) {
                    $dt_start = new DateTimeImmutable($data['temps']);
                    $dt1 = $dt_start;
                    $line[$idy_time][$index] = $dt_start->format('H:i:s');
                } else {
                    if ($this->check(TIME_PER_LAP)) {
                        if($data['temps']){
                            $dt2 = new DateTimeImmutable($data['temps']);
                            $interval = $dt1->diff($dt2);
                            $dt1 = $dt2;
                            $line[$idy_time][$index] = $interval->format("%I:%S");
                        }else{
                            $line[$idy_time][$index] = '';
                        }
                    }
                    if ($this->check(TIME_SINCE_START)) {
                        if($data['temps']){
                            $dt2 = new DateTimeImmutable($data['temps']);
                            $interval = $dt_start->diff($dt2);
                            $line[$idy_time][$index] = $interval->format("%I:%S");
                        }else{
                            $line[$idy_time][$index] = '';
                        }
                    }
                    if ($this->check(HOUR_PER_LAP)) {
                        $dt_temps = new DateTimeImmutable($data['temps']);
                        $line[$idy_time][$index] = $dt_temps->format('H:i:s');
                    }
                    $nb_laps++;
                }
            }
            $index++;
            if ($index > $this->_nb_max) {
                break;
            }
        }
        //------------------------------------------------
        //on complète les données avec des cases vides
        //------------------------------------------------
        for ($i = $index  ; $i <= $this->_nb_max; $i++) {
            if ($this->check(SHOW_DATA) || $force_all) {
                $line[0][$i] = "";
            }
            if ($this->check(SHOW_TIME) || $force_all) {
                $line[$idy_time][$i] = "";
            }
        }
        //------------------------------------------------
        //on ajoute les la colomne de résultas
        //------------------------------------------------
        if ($this->check(SHOW_TOTAL_DATA)) {
            $line[0][$this->_nb_max+1] = $total_data;
            if($this->check(SHOW_TIME) || $force_all){
                $line[$idy_time][$this->_nb_max+1] = '';
            }
        }
        if ($this->check(SHOW_NUMBER_LAPS)) {
            $line[0][$this->_nb_max+1] = $nb_laps;
            if($this->check(SHOW_TIME) || $force_all){
                $line[$idy_time][$this->_nb_max+1] = '';
            }
        }
        if ($this->check(SHOW_FINAL_TIME)) {
            if (!empty($dt_start) and !empty($dt2)) {
                $interval = $dt_start->diff($dt2);
                $line[$idy_time][$this->_nb_max+1] = $interval->format("%I:%S");
            }else{
                $line[$idy_time][$this->_nb_max+1] = '';
            }
        }
        return $line;
    }
    
    /*
     * ------------------------------------------------------
     * Création des entête pour le téléchargement du fichier csv 
     * pour exporter les données.
     * ------------------------------------------------------
     */
    private function Array2CSVDownload($array, $filename = "export.csv", $delimiter = ";") {

//        $data = [];
//        foreach ($array as $item) {
//            $values = explode($item,$delimiter);
//            var_dump($values);
//            array_push($data, implode($delimiter, $values));
//        }
        // flush buffer
        ob_flush();
        // mixing items
        $csvData = join("", $array);
        
        //setup headers to download the file
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        //setup utf8 encoding
        header('Content-Type: application/csv; charset=UTF-8');
        //header('Content-Type: application/vnd.ms-excel; charset=UTF-8;');
        // showing the results
        die($csvData);
    }

    /*
     * ------------------------------------------------------
     * Trouve le caractère qui délimite les champs si il existe
     * ------------------------------------------------------
     */

    private function find_delimiter($modele) {
        $demlimiters = array(",", ";", "|", ":", "/");
        foreach ($demlimiters as $delimiter) {
            if (substr_count($modele, $delimiter) > 2) {
                return $delimiter;
            }
        }
        return false;
    }

    /*
     * ------------------------------------------------------
     * construction du tableau d'exportation 
     * On remplace les mots clefs d'un modèle pour plus de souplesse
     * ------------------------------------------------------
     */

    public function export($modele) {

        $delimiter = $this->find_delimiter($modele);
        $lines = [];
        $index = 0;

        //affichage des colomnes liées au temps
        $line = $modele;
        $headers = "";
        
        if ($delimiter) {

            $export_data = (strpos($modele,"data") !== false);
            $export_time = (strpos($modele,"temps") !== false);

            if ( $export_time|| $export_data){
                foreach ($this->make_headers() as $header) {
                    $headers .= $header . $delimiter;
                }
            }
            //s'il il y a le mot data on ajoute les headers
            if($export_data){
                $line = str_replace("data", substr($headers, 0, -1), $line);
                if($export_time){
                    $line = str_replace("temps".$delimiter, "", $line);
                    $line = str_replace($delimiter."temps", "", $line);
                }
            }else if($export_time){
                $line = str_replace("temps", substr($headers, 0, -1), $line);
            }
            $lines[$index] = $line;
            $index++;
            
            $participants = $this->get_participants_to_export();
            //if (!empty($participants)) {
                foreach ($participants as $participant) {
                    
                    //construction des données du participants
                    $line = $modele;
                    $line = str_replace("prenom", $participant["prenom"], $line);
                    $line = str_replace("nom", $participant["nom"], $line);
                    $line = str_replace("classe", $participant["classe"], $line);
                    $line = str_replace("nais", $participant["nais"], $line);
                    $line = str_replace("sexe", $participant["sexe"], $line);
                    
                    //on récupère la ou les deux lignes de donnée(data et temps)
                    $line_data = array();
                    $i = 0;
                    $id_participant = $this->get_assoc_parent($participant['id']);
                    foreach ($this->make_datas($id_participant,true) as $datas_line) {
                        $line_data[$i] = "";
                        foreach ($datas_line as $data) {
                            $line_data[$i] .= $data . $delimiter;
                        } 
                        $i++;
                    }
                    
                    //on exporte la ligne de données
                    if($export_data){
                        $line = str_replace("temps".$delimiter, "", $line);
                        $line = str_replace($delimiter."temps", "", $line);
                        $line = str_replace("data", substr($line_data[0], 0, -1), $line);
                        $lines[$index] = $line;
                        $index++;
                        
                        if($export_time){
                            //si il y a du temps on exporte la ligne de temps
                            //on reconstruit une ligne vide avec les delimiters avants et après les temps
                            $line = $modele;
                            $line = str_replace("data".$delimiter, "", $line);
                            $line = str_replace($delimiter."data", "", $line);
                            
                            $splt = explode("temps".$delimiter,$line);
                            if(count($splt)==1){
                                if(strpos($modele,"temps") === 0){
                                    $line = substr($line_data[1], 0, -1).
                                            str_repeat($delimiter,substr_count($splt[1], $delimiter));
                                }else{
                                    $line = str_repeat($delimiter,substr_count($splt[0], $delimiter)).
                                            substr($line_data[1], 0, -1);
                                }
                            }else{
                                $line = str_repeat($delimiter,substr_count($splt[0], $delimiter)).
                                        substr($line_data[1], 0, -1).
                                        str_repeat($delimiter,substr_count($splt[1], $delimiter));
                            }
                            $lines[$index] = $line."\r";
                            $index++;
                        }   
                    }else if($export_time){
                        $line = str_replace("temps", substr($line_data[0], 0, -1), $line);
                        $lines[$index] = $line;
                        $index++;
                    }
                }
            //}
        }
        foreach($lines as $line){
            //echo $line."<br/>";
        }
        
        
        
        $this->Array2CSVDownload($lines, "export.csv", $delimiter);
    }
}
