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
 * Description of ExportClass
 *
 * @author gleon
 */
class Export {

// déclaration d'une propriété
    private $_id = 0;
    private $_db = NULL;
    private $unite_diff = '';
    private $unite_time = '';
    private $infos = array(
        'repetition' => '',
        'identification' => '',
        'nb_max' => '',
        'temps_max' => '');
    
    public function __construct($mysqli) { // or any other method
        $this->_db = $mysqli;
        $this->get_id();
        $this->_last_update = strtotime(date('Y-m-d H:i:s'));
    }

 /*
 * ------------------------------------------------------
 * Initialisation de l'activité
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

    public function refresh() {
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT repetition, identification, nb_max, temps_max "
                    . "FROM activites WHERE id = '" . $this->_id . "'");
            if ($result->num_rows > 0) {
                $ep = $result->fetch_assoc();
                $this->infos['repetition'] = $ep['repetition'];
                $this->infos['identification'] = $ep['identification'];
                $this->infos['temps_max'] = $ep['temps_max'];
                $this->infos['nb_max'] = $ep['nb_max'];
                return true;
            }
        }
        return false;
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
    
 /*
 * ------------------------------------------------------
 * Liste les participants de l'activité
 * ------------------------------------------------------
 */
    private function get_participants_to_export(){
        if ($this->_id > 0) {
            $result = $this->_db->query("SELECT participants.id, nom, prenom, classe, nais, sexe FROM users,participants"
                    . " WHERE users.id = participants.id_user AND id_activite = '".$this->_id."' ORDER BY nom");
            if ($result->num_rows > 0) {
                return $result;
            }
        }
        return array();
    }

 /*
 * ------------------------------------------------------
 * fabbrique les entetes du fichier csv
 * ------------------------------------------------------
 */
    private function format_time_headers($delimiter){
        $header='';
        if($this->infos['repetition']=='passages'){
            for($i=0;$i<intval($this->infos['nb_max'])+1;$i++){
                if ($i==0){
                    $header .= "Départ".$delimiter;
                }else{
                    $header .= "P".$i.$delimiter;
                }
            }
            $header .= "Temps final";
        }elseif($this->infos['repetition']=='essais'){
            for($i=1;$i<intval($this->infos['nb_max'])+1;$i++){
                $header .= "essais".$i.$delimiter;
            }
        }
        return $header;
    }

/*
 * ------------------------------------------------------
 * Format l'exportation du mot temps (nombre d'enregistrements)
 * ------------------------------------------------------
 */ 
    private function format_time($datas, $delimiter){
        $strTime='';
        $dt_start = null;
        $dt1 = null;
        $dt2 = null;
        $i=0;
        
        if($this->infos['repetition']=='passages'){
            foreach($datas as $data){
                if ($i==0){
                    //mis en forme du temps de départ
                    $dt_start = new DateTimeImmutable($data['temps']);
                    $dt1 = $dt_start;
                    $strTime .= $dt_start->format('H:i:s').$delimiter;
                }else{
                    //mise en forme des temps intermédiaires
                    $dt2 = new DateTimeImmutable($data['temps']);
                    if(!empty($dt1) && !empty($dt2)){
                        $interval = $dt1->diff($dt2);
                        $dt1 = $dt2;
                        $strTime .= $interval->format($this->unite_diff).$delimiter;
                    }else{
                        $strTime .= "".$delimiter;
                    }
                }
                $i++;
                if($i == intval($this->infos['nb_max'])+1){
                    break;
                }
            }
            
            //On complète avec des cellule vide si besoin
            for($j = $i ;$j < intval($this->infos['nb_max'])+1 ; $j++ ){
                $strTime .= "".$delimiter;
            }
            if (!empty($dt_start) and !empty($dt2)) {
                $interval = $dt_start->diff($dt2);
                $strTime .= $interval->format($this->unite_diff);
            }else{
                $strTime .= '';
            }
        }elseif($this->infos['repetition']=='essais'){
            foreach($datas as $data){
                $dt = new DateTimeImmutable($data['temps']);
                $st = $dt->format($this->unite_time);
                //lors de la covertion, affichage des millième
                //tronquage du dernier chraractère pour avoir les 1000e
                if(strpos($st,".")>0){
                    $strTime .= substr($st,0,-1).$delimiter;
                }else{
                    $strTime .= $st.$delimiter;
                }
                $i++;
                if($i == intval($this->infos['nb_max'])){
                    break;
                }
            }
            //On complète avec des cellule vide si besoin
            for($j = $i ;$j < intval($this->infos['nb_max']) ; $j++ ){
                $strTime .= "".$delimiter;
            }
            
        }
        return $strTime;
    }

 /*
 * ------------------------------------------------------
 * Trouve le caractère qui délimite les champs si il existe
 * ------------------------------------------------------
 */ 
    private function find_delimiter($modele){
        $demlimiters = array(",",";","|",":","/");
        foreach($demlimiters as $delimiter){
            if(substr_count($modele,$delimiter)>2){
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
    public function export($modele, $unit="s"){
        //on cherche le délimiteur
        $this->refresh();
        //echo $unit;
        switch ($unit) {
            case "s":
                $this->unite_diff = "%S";
                $this->unite_time = "s.v";
                break;
            case "m":
                $this->unite_diff = "%I:%S";
                $this->unite_time = "i:s";
                break;
            case "h":
                $this->unite_diff = "%h:%I:%S";
                $this->unite_time = "H:i:s";
                break;
        }

        $delimiter = $this->find_delimiter($modele);
        $lines = [];
        
        //affichage des colomnes liées au temps
        $line = $modele;
        if($delimiter){
            $line=str_replace("temps",$this->format_time_headers($delimiter),$line);
        }
        $lines[] = $line;

        foreach($this->get_participants_to_export() as $participant){
            $line = $modele;
            $line=str_replace("prenom",$participant["prenom"], $line);
            $line=str_replace("nom",$participant["nom"], $line);
            $line=str_replace("classe",$participant["classe"], $line);
            $line=str_replace("nais",$participant["nais"], $line);
            $line=str_replace("sexe",$participant["sexe"], $line);
            if($delimiter){
                $data = $this->get_datas($participant["id"]);
                $line=str_replace("temps",$this->format_time($data,$delimiter,$unit),$line);
            }
            
            $lines[] = $line;
        }
        
        //foreach($lines as $line){
        //    echo $line."<br/>";
        //}
        $this->Array2CSVDownload($lines,"export.csv",$delimiter);
    }

/*
 * ------------------------------------------------------
 * Met en place les headers pour le téléchargement du fichier
 * ------------------------------------------------------
 */ 
    
    private function Array2CSVDownload($array, $filename = "export.csv", $delimiter=";") {
        
        $data = [];
        foreach ($array as $item) {
            $values = array_values((array) $item);
            array_push($data, implode($delimiter, $values)); 
        }
        // flush buffer
        ob_flush();
        // mixing items
        $csvData = join("", $data);
        //setup headers to download the file
        header('Content-Disposition: attachment; filename="'.$filename.'";');
        //setup utf8 encoding
        header('Content-Type: application/csv; charset=UTF-8');
        //header('Content-Type: application/vnd.ms-excel; charset=UTF-8;');
        // showing the results
        die($csvData);
    }  
    
}
