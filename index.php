<?php
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set("display_errors", 1);
session_start();

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

/*
 * ------------------------------------------------------
 * Déclaration des class et connexion à la base de donnée
 * ------------------------------------------------------
 */
require_once 'class/FormClass.php';
require_once 'class/HtmlClass.php';
require_once 'class/ActiviteClass.php';
require_once 'class/ImportClass.php';
require_once 'class/UsersClass.php';
require_once 'class/ParcoursClass.php';

connect_db();

$myform = new Form;
$myhtml = new Html;
$myactivite = new Activite();
$myusers = new Users();
$myimport = new Import();
$myparcours = new Parcours();

/*
 * ------------------------------------------------------
 * on vérifie l'authentification
 * ------------------------------------------------------
 */
$ACCES_GRANTED=0;
$IDMAT_USER=0;
if( isset($_SESSION['chronoVDR']) ) {
    if($_SESSION['chronoVDR']=="all"){
        $ACCES_GRANTED=1;
    }
}

$message_password = "Mot de passe : ";
$format_password = "";

if(filter_has_var(INPUT_POST, 'auth')){
    $password = filter_input(INPUT_POST, 'auth', FILTER_SANITIZE_STRING);
    if( $password  ==  "none" ) {
        session_destroy();
        $ACCES_GRANTED=0;
    }
    if( $password  ==  $admin_password ) {
        $_SESSION['chronoVDR'] = "all";
        $ACCES_GRANTED=1;
        $_POST["show_password"]='close';
    }else{
        $message_password = 'Mot de passe incorect !';
        $format_password = 'style="color:red;"';
    }
}

/*
 * ------------------------------------------------------
 * Retrouver la l'activité sélectionnée dans le formulaire
 * ------------------------------------------------------
 */
if(filter_has_var(INPUT_POST, 'selActivite')){
    $myIdActivite = filter_input(INPUT_POST, 'selActivite', FILTER_SANITIZE_NUMBER_INT);
    if($myactivite->get_id() != $myIdActivite){
        $myactivite->set_id ($myIdActivite);
    }
}

if($ACCES_GRANTED){// si pas d'authentiifcation, pas d'enregistrement ni de choix
    
/*
 * ------------------------------------------------------
 * création et/ou enregistrement des paramètres de l'activitée 
 * ------------------------------------------------------
 */

if(isset($_POST['btn_creer_activite'])){
    $myactivite->create();
    $_POST["show_activite"]='open';
}

function save_activite(){
    global $myactivite;
    if(filter_has_var(INPUT_POST, 'title_activite')){
        $myactivite->infos['nom'] = filter_input(INPUT_POST, 'title_activite', FILTER_SANITIZE_STRING);
    }
    if(filter_has_var(INPUT_POST, 'organisateur')){
        $myactivite->infos['organisateur'] = filter_input(INPUT_POST, 'organisateur', FILTER_SANITIZE_STRING);
    }
    if(filter_has_var(INPUT_POST, 'nb_max')){
        $myactivite->infos['nb_max'] = filter_input(INPUT_POST, 'nb_max', FILTER_SANITIZE_NUMBER_INT);
    }
    if(filter_has_var(INPUT_POST, 'temps_max')){
        $myactivite->infos['temps_max'] = filter_input(INPUT_POST, 'temps_max', FILTER_SANITIZE_NUMBER_INT);
    }
    if(filter_has_var(INPUT_POST, 'delais_min')){
        $myactivite->infos['delais_min'] = filter_input(INPUT_POST, 'delais_min', FILTER_SANITIZE_NUMBER_INT);
    }
    if(filter_has_var(INPUT_POST, 'flag')){
        $newFlag = filter_input(INPUT_POST, 'flag', FILTER_SANITIZE_NUMBER_INT); 
        $myactivite->infos['flag'] = $newFlag;
    }
    $myactivite->save();
}

if(isset($_POST['enregistrer_activite'])){
    global $myactivite;
    save_activite();
    $_POST["show_activite"]='open';
    if(empty($myactivite->get_participants())){
        $_POST["show_participants"]='open';
    }
    
}

if(isset($_POST['change_activite'])){
    if($_POST['change_activite']=="ok"){
        save_activite();
        $_POST["show_activite"]='open';
    }
}


/*
 * ------------------------------------------------------
 * Lire un fichier CSV pour importation
 * ------------------------------------------------------
 */

$eleves_read = array();
if(isset($_POST['readcsvfile'])){
    if(isset($_FILES['fileImport'])){
        $myimport->set_file($_FILES['fileImport']);
        if($myimport->is_csv_file()){
            $myimport->read_file(); 
            if(filter_has_var(INPUT_POST, 'importClasse')){
                $classe = filter_input(INPUT_POST, 'importClasse', FILTER_SANITIZE_STRING);
            }
            $eleves_read = $myimport->getElevesArray($classe);
        }
    }
}


/*
 * ------------------------------------------------------
 * Importer la liste des users à partir du texte mis en forme
 * ------------------------------------------------------
 */
if(isset($_POST['importer'])){
    if(filter_has_var(INPUT_POST, 'lstusers')){
        $myusers->importUsers(filter_input(INPUT_POST, 'lstusers', FILTER_SANITIZE_STRING));
    }
}

/*
 * ------------------------------------------------------
 * Importer la liste des users à partir du texte mis en forme
 * ------------------------------------------------------
 */
if(isset($_POST['btn_exportation'])){
    if(filter_has_var(INPUT_POST, 'liste_exportation')){
        $myactivite->refresh();
        $myactivite->export(filter_input(INPUT_POST, 'liste_exportation', FILTER_SANITIZE_STRING));
    }
}

/*
 * ------------------------------------------------------
 * Ajouter les participants à l'activité depuis la liste
 * de checkbox avec l'id des users
 * ------------------------------------------------------
 */
if(isset($_POST['AjouterParticipants'])){
    if(isset($_POST['check_users'])){
        $check_users = $_POST['check_users'];
        if (is_array($check_users)){
            foreach ($check_users as $chkUser){
                $myactivite->add_participants($chkUser);
            }
        }
    }
}

/*
 * ------------------------------------------------------
 * Ajouter les participants à l'activité depuis la liste
 * de checkbox avec l'id des users
 * ------------------------------------------------------
 */
if(isset($_POST['create_equipe'])){
    if(isset($_POST['check_participants'])){
        $check_participants = $_POST['check_participants'];
        if (is_array($check_participants)){
            $myactivite->create_equipe($check_participants);
        }
    }
    if(isset($_POST['check_associations'])){
        $check_associations = $_POST['check_associations'];
        if (is_array($check_associations)){
            $myactivite->delete_equipe($check_associations);
        } 
    }
}
/*
 * ------------------------------------------------------
 * Supprimer des participants si ils n'ont pas d'enregistrements
 * ------------------------------------------------------
 */

if(isset($_POST['delete_participants'])){
    if(isset($_POST['check_participants'])){
        $check_participants = $_POST['check_participants'];
        if (is_array($check_participants)){
            $myactivite->delete_participant($check_participants);
        }
    }
}
/*
 * ------------------------------------------------------
 * Supprimer les données
 * ------------------------------------------------------
 */
if(isset($_POST['delete_activite'])){
    $myactivite->delete();
}
/*
 * ------------------------------------------------------
 * Supprimer l'activité
 * ------------------------------------------------------
 */
if(isset($_POST['delete_datas'])){
    $myactivite->delete_all_datas();
}
/*
 * ------------------------------------------------------
 * Supprimer les users qui sont dans la corbeille
 * 
 * ------------------------------------------------------
 */
if(isset($_POST['delete_users'])){
    $myusers->deleteUsersBin();
}
/*
 * ------------------------------------------------------
 * Retrouver l---a classe sélectionnée dans le formulaire
 * ---------------------------------------------------
 */ 

$selectedClasse = '';
if(filter_has_var(INPUT_POST, 'selclasse')){
    $selectedClasse = filter_input(INPUT_POST, 'selclasse', FILTER_SANITIZE_STRING);
    $eleves_classe = $myusers->getUsersFromClasse($selectedClasse);
}
$classes = $myusers->getClasses();
if ($selectedClasse == ''){
    if ($classes->num_rows==0){
        $selectedClasse = array('classe'=>'Aucune classe');
        $eleves_classe = array();
    }else{
        $classe = $classes->fetch_assoc();
        $selectedClasse = $classe['classe'];
        $eleves_classe = $myusers->getUsersFromClasse($selectedClasse);
        if(empty($eleves_classe)){
            $eleves_classe = array();
        }
    }
}
/*
 * ------------------------------------------------------
 * Retrouver la classe sélectionnée dans le formulaire
 * D'ajout de participants à une activité
 * ------------------------------------------------------
 */
$selectedClassePartcipants='';
if(filter_has_var(INPUT_POST, 'selclasseParticipants')){
    $selectedClassePartcipants = filter_input(INPUT_POST, 'selclasseParticipants', FILTER_SANITIZE_STRING);
    $eleves_participants = $myactivite->get_participantsToAdd($selectedClassePartcipants);
    if(empty($eleves_participants)){
        $eleves_participants = array();
    }
}
$classes->data_seek(0);
if ($selectedClassePartcipants == ''){
    if ($classes->num_rows==0){
        $selectedClassePartcipants = array('classe'=>'Aucune classe');
        $eleves_participants = array();
    }else{
        $classe = $classes->fetch_assoc();
        $selectedClassePartcipants = $classe['classe'];
        $eleves_participants = $myactivite->get_participantsToAdd($selectedClassePartcipants);
        if(empty($eleves_participants)){
            $eleves_participants = array();
        }
    }
}


/*
 * ------------------------------------------------------
 * Retrouver la classe sélectionnée dans le formulaire
 * D'ajout de participants à une activité
 * ------------------------------------------------------
 */
$selectedParcours=0;
if(filter_has_var(INPUT_POST, 'selparcours')){

    $selectedParcours = filter_input(INPUT_POST, 'selparcours', FILTER_SANITIZE_NUMBER_INT);
    $myparcours->set_id($selectedParcours);

    if(filter_has_var(INPUT_POST, 'create_parcours')){
        $selectedParcours = $myparcours->create();
    }

    $id_balise = filter_input(INPUT_POST, 'id_parcours_balise', FILTER_SANITIZE_NUMBER_INT);
    
    if(filter_has_var(INPUT_POST, 'parcours_ordre_value')){
        $ordre = filter_input(INPUT_POST, 'parcours_ordre_value', FILTER_SANITIZE_NUMBER_INT);
        $myparcours->set_ordre($ordre);
    }

    if(filter_has_var(INPUT_POST, 'add_parcours_balise')){
        $id_addBalise = filter_input(INPUT_POST, 'id_parcours_balise', FILTER_SANITIZE_NUMBER_INT);
        if($id_addBalise){
            $myparcours->add_balise($id_addBalise);
        }
    }
    
    if(filter_has_var(INPUT_POST, 'delete_parcours_balise')){
        if(isset($_POST['chkBalises'])){
            $chkbalise = $_POST['chkBalises'];
            if (is_array($chkbalise)){
                $myparcours->remove_balise($chkbalise);
            }
        }
    }

        if(filter_has_var(INPUT_POST, 'set_balise_value')){
        $selectedBaliseValue = filter_input(INPUT_POST, 'parcours_balise_value', FILTER_SANITIZE_NUMBER_INT);
        if ($selectedBaliseValue){
            $myparcours->set_info($id_balise," ",$selectedBaliseValue);
        }
    }
    
    if(filter_has_var(INPUT_POST, 'saveParcours')){
        $nomparcours = filter_input(INPUT_POST, 'nomparcours', FILTER_SANITIZE_STRING);
        $myparcours->set_nom($nomparcours);
        
        
        if(filter_has_var(INPUT_POST, 'nombalise')){
            $sel_balise_ordre = $_POST['sel_balise_ordre'];
            $nombalise = $_POST['nombalise'];
            $sel_balise_value = $_POST['sel_balise_value'];
            foreach($nombalise as $id_balise=>$nom){
                $myparcours->set_info($id_balise,$nom,$sel_balise_ordre[$id_balise],$sel_balise_value[$id_balise]);
            }
        }
    }

    if(filter_has_var(INPUT_POST, 'delete_balise')){
        echo $_POST['id_parcours_balise'];
        $id_balise = filter_input(INPUT_POST, 'id_parcours_balise', FILTER_SANITIZE_NUMBER_INT);
        if($id_balise){
            $myparcours->delete_balise($id_balise);
        }
    }
        
    if(filter_has_var(INPUT_POST, 'delete_parcours')){
        if(!$myparcours->delete($selectedParcours)){
            echo "Erreur, le parcours n'est pas vide";
        }else{
            $selectedParcours=0;
        }
    }

    if(filter_has_var(INPUT_POST, 'add_scaned_balise')){
        $nom = filter_input(INPUT_POST, 'nom_scan_rfid', FILTER_SANITIZE_STRING);
        $tag = filter_input(INPUT_POST, 'tag_scan_rfid', FILTER_SANITIZE_STRING);
        $myparcours->create_balise($tag,$nom);
    }
    
    $parcours_balises = $myparcours->get_liste_balises();
}

$liste_parcours = $myparcours->get_list();

if ($selectedParcours==0){
    if ($liste_parcours->num_rows==0){
        $parcours = array('id'=>0,'nom'=>'Aucun parcours');
        $parcours_balises = false;
    }else{
        $parcours = $liste_parcours->fetch_assoc();
        $selectedParcours = $parcours['id'];
        $myparcours->set_id($selectedParcours);
        $parcours_balises = $myparcours->get_liste_balises();
    }
}

/*
 * ------------------------------------------------------
 * Retrouver la vue selectionnée
 * ------------------------------------------------------
 */
if(!empty($_POST['selVue'])){
    $myactivite->set_vue(filter_input(INPUT_POST, 'selVue', FILTER_SANITIZE_STRING));
}
/*
 * ------------------------------------------------------
 * Gestion de la possibilité de recevoir des datas
 * ------------------------------------------------------
 */

if(isset($_POST['etat'])){
    $myEtat = filter_input(INPUT_POST, 'etat', FILTER_SANITIZE_NUMBER_INT);
    if($myEtat==2){
        $myactivite->start();
    }else{
        $myactivite->stop();
    }
}

}//fin restriction auth
/*
 * ------------------------------------------------------
 * Une fois les données enregistrée, on relit les paramètres de l'activité
 * pour mettre à jour l'innterface.
 * ------------------------------------------------------
 */


$myactivite->refresh();
        
/*
 * ------------------------------------------------------
 * Mise en place des headers de la page 
 * isertion du fichier css et du fichier javascript
 * ------------------------------------------------------
 */
$myhtml->openHtml();
$myhtml->openHead();
echo '<title>ChronoVDR</title>';
echo '<meta http-equiv="content-type" content="application/javascript; charset=UTF-8">';
echo '<script type="importmap">
  {
      "imports": {
        "chart.js": "./node_modules/chart.js/dist/chart.js",
        "@kurkle/color": "./node_modules/@kurkle/color/dist/color.esm.js",
        "chartjs-adapter-luxon": "./node_modules/chartjs-adapter-luxon/dist/chartjs-adapter-luxon.esm.js",
        "luxon": "./node_modules/luxon/src/luxon.js"
      }
  }
</script>';
echo '<script type="module">
import * as chronovdr from "./script.js";
</script>';

echo '<link rel="stylesheet" href="style.css" type="text/css" />';
header("Cache-Control: no-cache, must-revalidate");
setlocale (LC_ALL, 'fr_FR.utf8');
date_default_timezone_set('Europe/Paris');
$myhtml->closeHead();

$myhtml->openBody();

function logout() {
    session_destroy();
}

/*
 * ------------------------------------------------------
 * on utilise un seul formulaire pour toute l'interface.
 * ------------------------------------------------------
 */
$myform->openForm("form_activity", "index.php","POST");
if(!isset($_POST['show_activite'])){$_POST['show_activite'] = "close";}
$myform->hidden('show_activite', $_POST["show_activite"]);
if(!isset($_POST['show_users'])){$_POST['show_users'] = "close";}
$myform->hidden('show_users', $_POST["show_users"]);
if(!isset($_POST['show_participants'])){$_POST['show_participants'] = "close";}
$myform->hidden('show_participants', $_POST["show_participants"]);
if(!isset($_POST['show_password'])){$_POST['show_password'] = "close";}
$myform->hidden('show_password', $_POST["show_password"]);
if(!isset($_POST['show_nettoyage'])){$_POST['show_nettoyage'] = "close";}
$myform->hidden('show_nettoyage', $_POST["show_nettoyage"]);
if(!isset($_POST['show_exportation'])){$_POST['show_exportation'] = "close";}
$myform->hidden('show_exportation', $_POST["show_exportation"]);
if(!isset($_POST['show_parcours'])){$_POST['show_parcours'] = "close";}
$myform->hidden('show_parcours', $_POST["show_parcours"]);
if(!isset($_POST['show_menu'])){$_POST['show_menu'] = "close";}
$myform->hidden('show_menu', $_POST["show_menu"]);

$myform->hidden('selVue');
$myform->hidden('Vue',$myactivite->infos['vue']);
    
if(!isset($_POST['show_logs'])){$_POST['show_logs'] = "none";}
$myform->hidden('show_logs', $_POST["show_logs"]);

if(!isset($_POST['etat'])){$_POST['etat'] = $myactivite->infos['etat'];}
$myform->hidden('etat', $_POST["etat"]);



/*
 * ------------------------------------------------------
 * Fenètre de logs
 * ------------------------------------------------------
 */
if($_POST['show_logs'] == "none"){
    $visible="style='display: none;'";
}else{
    $visible="style='display: normal;'";
}
$myhtml->openDiv('logs','',$visible);
$myhtml->closeDiv();    

include 'dialogs/dialog_password.php';
if($ACCES_GRANTED){// si pas d'authentiifcation, pas d'enregistrement ni de choix

    include 'dialogs/dialog_activite.php';
    include 'dialogs/dialog_nettoyage.php';
    include 'dialogs/dialog_export.php';
    include 'dialogs/dialog_users.php';
    include 'dialogs/dialog_participants.php';
    include 'dialogs/dialog_co.php';

}//fin restriction auth


/*
 * ------------------------------------------------------
 * Fenètre de menu
 * ------------------------------------------------------
 */

if($myactivite->infos["etat"]==2){
    $disabled = 'disabled="true"';
    $enabled = false;
}else{
    $disabled = "";
    $enabled = true;
}

include 'dialogs/dialog_menu.php';
include 'dialogs/barre_menu.php';

 /*
 * ------------------------------------------------------
 * Affichage de la liste des participants et de leur résultats
 * utilisation d'une ligne div par participant
 * utilisation du colonne div par enregistrement 
 * ------------------------------------------------------
 */ 
$myhtml->openDiv('participants');
    $myhtml->openTable('id="orga"');
    $myhtml->openTr();
    $myhtml->openTd();
        if($myactivite->id()>0){
            $myhtml->openDiv('','','style="float:left;"');
            echo 'Organisateur : '.$myactivite->infos['organisateur'];
            $myhtml->closeDiv(); 
        }
        $myhtml->openDiv('dt-sys','','style="float:left;margin-left:20px;"');
            echo date("H:i:s");
        $myhtml->closeDiv(); 
    $myhtml->closeTd();
    $myhtml->closeTr();
    $myhtml->closeTable();
    
    $myhtml->openTable('id="parts"');
    $myhtml->openTr('','table_title'); 
    $myhtml->openTh('','titre_participants');
        if($myactivite->get_id()>0){
            echo 'Partipants';
            if($ACCES_GRANTED){
                $myhtml->openDiv('','iconedroite');
                $myform->button("delete_participants", " -- ");
                $myhtml->closeDiv();
                $myhtml->openDiv('','iconedroite');
                echo '<img id="dial_participants" src="img/plus.png" '.   
                    'title="Ajouter des participants" '.
                    'style="width:20px; height:20px;"/>';
                $myhtml->closeDiv();
            }
        }
    $myhtml->closeTh(); 

    
    $myhtml->openTh('','equipe');
        if($myactivite->id()>0){
            if($ACCES_GRANTED){
                $myform->button("create_equipe", "E");
            }else{
                echo '<div style="text-align:center;font:14px Arial;">E</div>';
            }
        }
    $myhtml->closeTh();
    
    $myhtml->openTh('','ref_id');
        if($myactivite->infos['flag'] & BY_IDMAT){
            echo 'ID';
        }elseif($myactivite->infos['flag'] & BY_RFID){
            echo 'Tag RFID';
        }
    $myhtml->closeTh();
    
    if ($myactivite->infos['vue']=="co"){
        $myhtml->openTh('','idmat');
            echo 'Parcours';
        $myhtml->closeTh();
        
        $myhtml->openTh('','empty_th');
        $myhtml->closeTh();
        
    }
    
    if ($myactivite->infos['vue']=="tableau"){
//        foreach($myactivite->make_headers() as $header){
//            $myhtml->openTh('','title');
//            echo $header;
//            $myhtml->closeTh();
//        }
    
        $myhtml->openTh('','empty_th');
        $myhtml->closeTh();
        
    }elseif($myactivite->infos['vue']=="graphique"){
        $myhtml->openTh('','empty_th');
        $myhtml->closeTh();
    }
    
    $myhtml->closeTr();

    if(($myactivite->infos['flag'] & SHOW_TIME)&&($myactivite->infos['flag'] & SHOW_DATA)){
        $nbdatas = 2;
    }else{
        $nbdatas = 1;
    }

    $participants = $myactivite->get_participants();
    if (!empty($participants)){
        $last_id_assoc = 0; $drawline = true;
        foreach ($participants as $participant) {
            $myhtml->openTr($participant['id_participant'],'line');
              
            $myhtml->openTd('','');
            echo $participant["nom"];
            $myhtml->closeTd(); 
            
            $myhtml->openTd('',''); 
            if (strpos($participant["nom"],"<br/>")!==false){
                $myform->checkbox('check_associations[]', $participant["id_participant"]);
            }else{
                $myform->checkbox('check_participants[]', $participant["id_participant"]);
            }
            $myhtml->closeTd();
            
            if($myactivite->infos['flag'] & SHOW_PARCOURS){
                if($ACCES_GRANTED){
                    $myhtml->openTd('','idmat');
                }else{
                    $myhtml->openTd('','');
                }
                echo $participant['ref_id'];
                $myhtml->closeTd();  
                
                if($ACCES_GRANTED){
                    $myhtml->openTd('','idparcours');
                }else{
                    $myhtml->openTd('','');
                }
                $myhtml->closeTd();  
                
                
            }elseif($myactivite->infos['flag'] & BY_IDMAT){
                if($ACCES_GRANTED){
                $myhtml->openTd('','idmat');
                }else{
                    $myhtml->openTd('','');
                }
                echo $participant['ref_id'];
                $myhtml->closeTd();  
            }elseif($myactivite->infos['flag'] & BY_RFID){
                if($ACCES_GRANTED){
                    $myhtml->openTd('','tag');
                }else{
                    $myhtml->openTd('','tag');
                }
                echo $participant['ref_id'];
                $myhtml->closeTd();  
            }
 
            if ($myactivite->infos['vue']=="co"){
                $myhtml->openTd('','');
                    $myhtml->openDiv('','co_left');
                    echo '<';
                    $myhtml->closeDiv();   
                    $myhtml->openDiv('co_n'.$participant['id_participant'],'co_n');
                    echo '0/0';
                    $myhtml->closeDiv();   
                    $myhtml->openDiv('','co_right');
                    echo '>';
                    $myhtml->closeDiv();  
                    $myhtml->openDiv('co'.$participant['id_participant'],'co_tab');
                    $myhtml->closeDiv();   
                $myhtml->closeTd();

            }
            
            $myhtml->closeTr();
        }
    }  
    $myhtml->closeTable();

$myhtml->closeDiv();

 /*
 * ------------------------------------------------------
 * Fermeture de la page
 * ------------------------------------------------------
 */ 

$myform->closeForm();
$myhtml->closeBody();
$myhtml->closeHtml();




