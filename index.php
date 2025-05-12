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
require_once 'class/VueClass.php';

connect_db();

$myform = new Form;
$myhtml = new Html;
$myactivite = new Activite();
$myusers = new Users();
$myimport = new Import();


/*
 * ------------------------------------------------------
 * on vérifie l'authentification
 * ------------------------------------------------------
 */
$auth=0;
if( isset($_SESSION['chronoVDR']) ) {
    if($_SESSION['chronoVDR']=="all"){
        $auth=1;
    }
}


$message_password = "Mot de passe : ";
$format_password = "";

if(filter_has_var(INPUT_POST, 'auth')){
    $password = filter_input(INPUT_POST, 'auth', FILTER_SANITIZE_STRING);
    if( $password  ==  "none" ) {
        session_destroy();
        $auth=0;
    }
    if( $password  ==  $admin_password ) {
        $_SESSION['chronoVDR'] = "all";
        $auth=1;
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

if($auth){// si pas d'authentiifcation, pas d'enregistrement ni de choix
    
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
        $myactivite->infos['flag'] = filter_input(INPUT_POST, 'flag', FILTER_SANITIZE_NUMBER_INT);
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
    if(filter_has_var(INPUT_POST, 'lstexport')){
        $myvue = new Vue;
        $myvue->export(filter_input(INPUT_POST, 'lstexport', FILTER_SANITIZE_STRING));
    }
}

/*
 * ------------------------------------------------------
 * Ajouter les participants à l'activité depuis la liste
 * de checkbox avec l'id des users
 * ------------------------------------------------------
 */
if(isset($_POST['AjouterParticipants'])){
    if(isset($_POST['chkUsers'])){
        $chkUsers = $_POST['chkUsers'];
        if (is_array($chkUsers)){
            foreach ($chkUsers as $chkUser){
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
if(isset($_POST['makeq'])){
    if(isset($_POST['chkParts'])){
        $chkParts = $_POST['chkParts'];
        if (is_array($chkParts)){
            $myactivite->create_equipe($chkParts);
        }
    }
    if(isset($_POST['chkAssocs'])){
        $chkAssocs = $_POST['chkAssocs'];
        if (is_array($chkAssocs)){
            $myactivite->delete_equipe($chkAssocs);
        } 
    }
}
/*
 * ------------------------------------------------------
 * Supprimer des participants si ils n'ont pas d'enregistrements
 * ------------------------------------------------------
 */

if(isset($_POST['delParts'])){
    if(isset($_POST['chkParts'])){
        $chkParts = $_POST['chkParts'];
        if (is_array($chkParts)){
            $myactivite->delete_participant($chkParts);
        }
    }
}
/*
 * ------------------------------------------------------
 * Supprimer les données
 * ------------------------------------------------------
 */
if(isset($_POST['del_activites'])){
    $myactivite->delete();
}
/*
 * ------------------------------------------------------
 * Supprimer l'activité
 * ------------------------------------------------------
 */
if(isset($_POST['del_datas'])){
    $myactivite->delete_all_datas();
}
/*
 * ------------------------------------------------------
 * Supprimer les users qui sont dans la corbeille
 * 
 * ------------------------------------------------------
 */
if(isset($_POST['del_users'])){
    $myusers->deleteUsersBin();
}
/*
 * ------------------------------------------------------
 * Retrouver l---a classe sélectionnée dans le formulaire
 * ---------------------------------------------------
 */
if(filter_has_var(INPUT_POST, 'selclasse')){
    $selectedClasse = filter_input(INPUT_POST, 'selclasse', FILTER_SANITIZE_STRING);
    $eleves_classe = $myusers->getUsersFromClasse($selectedClasse);
}else{
    $selectedClasse = '';
    $eleves_classe = array();
}
/*
 * ------------------------------------------------------
 * Retrouver la classe sélectionnée dans le formulaire
 * D'ajout de participants à une activité
 * ------------------------------------------------------
 */
if(filter_has_var(INPUT_POST, 'selclasseParticipants')){
    $selectedClassePartcipants = filter_input(INPUT_POST, 'selclasseParticipants', FILTER_SANITIZE_STRING);
    $eleves_participants = $myactivite->get_participantsToAdd($selectedClassePartcipants);
    if(empty($eleves_participants)){
        $eleves_participants = array();
    }
}else{
    $selectedClassePartcipants = '';
    $eleves_participants = array();
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
echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8">';
echo '<script type="text/javascript" src="script.js"></script>';
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
if(!isset($_POST['show_menu'])){$_POST['show_menu'] = "close";}
$myform->hidden('show_menu', $_POST["show_menu"]);
$myform->hidden('selVue');

if(!isset($_POST['show_logs'])){$_POST['show_logs'] = "none";}
$myform->hidden('show_logs', $_POST["show_logs"]);

if(!isset($_POST['etat'])){$_POST['etat'] = $myactivite->infos['etat'];}
$myform->hidden('etat', $_POST["etat"]);

/*
 * ------------------------------------------------------
 * Fenètre d''authentification par mot de passe pour l'activité
 * ------------------------------------------------------
 */
$myhtml->openDiv('password');
    $myhtml->openDialog('propriete_password', $_POST["show_password"]);
        $myhtml->openDiv('','proprieteclose');
            $myform->button('cancel_activite', " X ",'onclick="return cancel_dialog_password()"');
        $myhtml->closeDiv();
        $myhtml->openDiv('','titre_propriete');
            echo 'Mot de passe admin';
        $myhtml->closeDiv();
        $myhtml->openDiv('','propriete');
            $myform->label('auth',$message_password,$format_password);
            $myform->password('auth','','onkeydown="touche(event.key);"');
        $myhtml->closeDiv(); 
        $myhtml->openDiv('','proprietebtn');
            $myform->button('valider_PW', "Valider");
        $myhtml->closeDiv();   
    $myhtml->closeDialog();
$myhtml->closeDiv();  

/*
 * ------------------------------------------------------
 * Fenètre de paramétrage de l'activité
 * Le bouton btnaction prend les valeurs créer ou enregistrer
 * en fonction de l'utilisation -> javascript
 * ------------------------------------------------------
 */
if($_POST['show_logs'] == "none"){
    $visible="style='display: none;'";
}else{
    $visible="style='display: normal;'";
}
$myhtml->openDiv('logs','',$visible);

$myhtml->closeDiv();    

if($auth){// si pas d'authentiifcation, pas d'enregistrement ni de choix
/*
 * ------------------------------------------------------
 * Fenètre de paramétrage de l'activité
 * Le bouton btnaction prend les valeurs créer ou enregistrer
 * en fonction de l'utilisation -> javascript
 * ------------------------------------------------------
 */
$myhtml->openDiv('activite');
    $myhtml->openDialog('propriete_acivite', $_POST["show_activite"]);
        $myhtml->openDiv('','proprieteclose');
            $myform->button('cancel_activite', " X ",'onclick="return cancel_dialog_activite()"');
        $myhtml->closeDiv();
        $myhtml->openDiv('proptitle','titre_propriete');
            echo 'titre à modifié en fonction de l\'usage';
        $myhtml->closeDiv();
        $myhtml->openDiv('','propriete');
            if(isset($message)){echo '<br/><div style="color:#FF0000">'.$message.'</div>';}
            $myform->label('title_activite','Nom de l\'activité');
            $myform->text('title_activite', $myactivite->infos['nom']);
        $myhtml->closeDiv();
        $myhtml->openDiv('','propriete');
            $myform->label('organisateur','Organisateur');
            $myform->text('organisateur', $myactivite->infos['organisateur']);
        $myhtml->closeDiv();   

        $myhtml->openDiv('','propriete');
            $myform->label('flag','Type d\'activité');
            $myform->openSelect('flag','flag','onchange="save_type_activite(this);"');
            foreach($myactivite->type_activite as $flag => $titre_activite){
                if($myactivite->infos["flag"] == $flag){$select = true;}else{$select = false;}
                $myform->option($flag,$titre_activite,$select);
            }
            $myform->closeSelect();
            $myform->hidden("change_activite");
        $myhtml->closeDiv(); 
 
        $myhtml->openDiv('','propriete');
            if($myactivite->infos["flag"] & IS_LIMIT_LAP){
                $myform->label('nb_max','Nombre maximum d\'enregistrements');
                $myform->text('nb_max', $myactivite->infos['nb_max']);
            }
            if($myactivite->infos["flag"] & IS_LIMIT_TIME){
                $myform->label('temps_max','Temps maximum (en minutes)');
                $myform->text('temps_max', $myactivite->infos['temps_max']);
            }
        $myhtml->closeDiv();  

        $myhtml->openDiv('','propriete');
            $myform->label('delais_min','Temps minimum entre deux enregistrements (seconde)');
            $myform->text('delais_min', $myactivite->infos['delais_min']."");
        $myhtml->closeDiv();  
        
        if($myactivite->infos["flag"] & PERSONAL_CONFIG){$disabled = "";}else{$disabled = 'disabled="true"';}
        $myhtml->openDiv('','propriete');
            $myform->label('flag_ID','Authentification des participants par');
            $myform->openSelect('flag_ID','flag_ID',$disabled);
            if($myactivite->infos["flag"] & BY_IDMAT){$select = true;}else{$select = false;}
            $myform->option(BY_IDMAT,'Id_matériel', $select);
            if($myactivite->infos["flag"] & BY_RFID){$select = true;}else{$select = false;}
            $myform->option(BY_RFID,'RFID', $select);
            $myform->closeSelect();
        $myhtml->closeDiv(); 
        
        $myhtml->openDiv('','propriete');
            $myform->label('flag_SHOW_TIME','Afficher l\'heure de départ');
            if($myactivite->infos["flag"] & SHOW_TIME){$select = "checked ";}else{$select = "";}
            $myform->checkbox('flag_SHOW_TIME',SHOW_TIME,$select . $disabled." id='flag_SHOW_TIME'");
        $myhtml->closeDiv();  

        $myhtml->openDiv('','propriete');
            $myform->label('flag_PERLAP','Afficher pour chaque enregistrement');
            $myform->openSelect('flag_PERLAP','flag_PERLAP',$disabled);
            if($myactivite->infos["flag"] & HOUR_PER_LAP){$select = true;}else{$select = false;}
            $myform->option(HOUR_PER_LAP,'Heure', $select);
            if($myactivite->infos["flag"] & TIME_PER_LAP){$select = true;}else{$select = false;}
            $myform->option(TIME_PER_LAP,'Temps en min.', $select);
            if($myactivite->infos["flag"] & DATA_PER_TEST){$select = true;}else{$select = false;}
            $myform->option(DATA_PER_TEST,'Donnée', $select);
            $myform->closeSelect();
        $myhtml->closeDiv(); 
        
        $myhtml->openDiv('','propriete');
            $myform->label('flag_TIME','Afficher le temps total');
            if($myactivite->infos["flag"] & SHOW_FINAL_TIME){$select = "checked ";}else{$select = "";}
            $myform->checkbox('flag_TIME',SHOW_FINAL_TIME,$select . $disabled." id='flag_TIME'");
        $myhtml->closeDiv();  
        
        $myhtml->openDiv('','propriete');
            $myform->label('flag_NBLAPS','Afficher le nombre de tours');
            if($myactivite->infos["flag"] & SHOW_NUMBER_LAPS){$select = "checked ";}else{$select = "";}
            $myform->checkbox('flag_NBLAPS',SHOW_NUMBER_LAPS,$select . $disabled." id='flag_NBLAPS'");
        $myhtml->closeDiv();  
        
        $myhtml->openDiv('','proprietebtn');
            $myform->button('enregistrer_activite', "Enregistrer");
        $myhtml->closeDiv();   
    $myhtml->closeDialog();
$myhtml->closeDiv();

/*
 * ------------------------------------------------------
 * Fenètre de nettoyage/suppression de données
 * ------------------------------------------------------
 */

$myhtml->openDiv('nettoyage');
    $myhtml->openDialog('propriete_nettoyage', $_POST["show_nettoyage"]);
        $myhtml->openDiv('','net');
            $myhtml->openDiv('','proprieteclose');
                $myform->button('cancel_activite', " X ",'onclick="return cancel_dialog_nettoyage()"');
            $myhtml->closeDiv();
            $myhtml->openDiv('','titre_propriete');
                echo 'Supprimer les données de l\'activité';
            $myhtml->closeDiv();
            $myhtml->openDiv('','proprietebtn');
                $myform->button('del_datas', "Supprimer");
            $myhtml->closeDiv();   
            $myhtml->openDiv('','titre_propriete');
                echo 'Supprimer l\'activité';
            $myhtml->closeDiv();
            $myhtml->openDiv('','propriete');
            echo 'Supprimer l\'activitée et toutes<br/>les données qu\'elle contient';
            $myhtml->closeDiv();  
            $myhtml->openDiv('','proprietebtn');
                $myform->button('del_activites', "Supprimer");
            $myhtml->closeDiv();  
            $myhtml->openDiv('','titre_propriete');
                echo 'Supprimer les élèves non classés';
            $myhtml->closeDiv();
            $myhtml->openDiv('','propriete');
            echo 'Supprimer définitivement les élèves<br/>qui ne n\'ont plus de classe et qui ne sont<br/>concernés par aucun enregistrement';
            $myhtml->closeDiv();  
            $myhtml->openDiv('','proprietebtn');
                $myform->button('del_users', "Supprimer");
            $myhtml->closeDiv();  
        $myhtml->closeDiv(); 
    $myhtml->closeDialog();
$myhtml->closeDiv();      

/*
 * ------------------------------------------------------
 * Exportation des données de l'activité
 * ------------------------------------------------------
 */

$myhtml->openDiv('exportation');
    $myhtml->openDialog('propriete_exportation', $_POST["show_exportation"]);
        $myhtml->openDiv('','proprieteclose');
            $myform->button('cancel_activite', " X ",'onclick="return cancel_dialog_exportation()"');
        $myhtml->closeDiv();
        $myhtml->openDiv('','titre_propriete');
            echo 'Exportation des données au format csv';
        $myhtml->closeDiv();
        $myhtml->openDiv('','comment');
        echo "Exporter les données pour l'activité en cours<br/>modèle d\'exportation";
        $myhtml->closeDiv();  
        $myhtml->openDiv('propriete');
        //$myform->label('lstexport','modèle d\'export.');
        $myform->textarea('lstexport', "nom;prenom;classe;nais;sexe;temps;datas",2,50,false);
        $myhtml->closeDiv();   
        $myhtml->openDiv('','proprietebtn');
            $myform->button('btn_exportation', "Exporter");
        $myhtml->closeDiv();   
    $myhtml->closeDialog();
$myhtml->closeDiv();  

/*
 * ------------------------------------------------------
 * Fenètre de d'impotation des noms/classe à partir de fichiers CSV
 * Depuis pronote ou depuis des listes gromatées divers
 * ------------------------------------------------------
 */
$myhtml->openDiv('add-users');
    $myhtml->openDialog('add_users', $_POST["show_users"]);
        $myhtml->openDiv('','proprieteclose');
            $myform->button('cancel_activite', " X ",'onclick="return cancel_dialog_users()"');
        $myhtml->closeDiv();
        $myhtml->openDiv('','titre_propriete');
            echo 'Importer des classes';
        $myhtml->closeDiv();
        $myhtml->openDiv('','propriete');
            if($myimport->get_erreur()){
                echo '<br/><div style="color:#FF0000">'.$myimport->get_erreur_message().'</div>';
            }
            $myform->label('fileImport','fichier csv');
            $myform->file('fileImport');
        $myhtml->closeDiv();  
        $myhtml->openDiv('','propriete');
            $myform->label('importClasse','Classe');
            $myform->text('importClasse', '');
        $myhtml->closeDiv();
        $myhtml->openDiv('','proprietebtn');
            $myform->button('readcsvfile', "Lire le fichier");
        $myhtml->closeDiv();
        
        if(count($eleves_read)>0){
            $myhtml->openDiv('','titre_propriete');
                echo 'Liste des élèves à importer';
            $myhtml->closeDiv();
            $myhtml->openDiv('propriete');
            $lstusers = '';
            foreach ($eleves_read as $eleve){
                $lstusers .= $eleve['nom'].','.$eleve['prenom'].','.$eleve['classe'].','.$eleve['nais'].','.$eleve['sexe']."\n";
            }
            $myform->textarea('lstusers', $lstusers,40,35);
            $myhtml->closeDiv();
            $myhtml->openDiv('','proprietebtn');
                $myform->button('importer', "Importer");
            $myhtml->closeDiv();  
            
        }else{

            $myhtml->openDiv('','titre_propriete');
                echo 'Liste des classes';
            $myhtml->closeDiv();
            $myhtml->openDiv('propriete');
                $myform->openSelect('selclasse', 'selclasse', 'onchange="this.form.submit();"');
                $myform->option('rien', 'Choisir une classe');
                $classes = $myusers->getClasses();
                foreach($classes as $classe){
                    if($selectedClasse == $classe['classe']){$select = true;}else{$select = false;}
                    $myform->option($classe['classe'], 'Classe de '.$classe['classe'], $select);
                }
                $myform->closeSelect();
            $myhtml->closeDiv();  
            $myhtml->openDiv('propriete');
            $myhtml->openTable();
                foreach ($eleves_classe as $eleve){
                    $myhtml->openTr();
                    $myhtml->openTd('', 'style="width:150px;"');
                        echo $eleve['nom'];
                    $myhtml->closeTd();
                    $myhtml->openTd('','style="width:150px;"');
                        echo $eleve['prenom'];
                    $myhtml->closeTd();
                    $myhtml->closeTr();
                }
                $myhtml->closeTable();
            $myhtml->closeDiv();
        }
    $myhtml->closeDialog();
$myhtml->closeDiv();

/*
 * ------------------------------------------------------
 * Fenètre d'ajout de participants
 * ------------------------------------------------------
 */

$myhtml->openDiv('add-participants');
    $myhtml->openDialog('add_participants', $_POST["show_participants"]);
        $myhtml->openDiv('','proprieteclose');
            $myform->button('cancel_activite', " X ",'onclick="return cancel_dialog_participants()"');
        $myhtml->closeDiv();
        $myhtml->openDiv('','titre_propriete');
            echo 'Ajouter des participants';
        $myhtml->closeDiv();
        
        $myhtml->openDiv('propriete');
            $myhtml->openTable();
            $myhtml->openTr();
            $myhtml->openTd('', 'colspan="2" width="300px"');
            $myform->openSelect('selclasseParticipants', 'selclasseParticipants', 'onchange="this.form.submit();"');
            $myform->option('rien', 'Choisir une classe');
            $classes = $myusers->getClasses();
            foreach($classes as $classe){
                if($selectedClassePartcipants == $classe['classe']){$select = true;}else{$select = false;}
                $myform->option($classe['classe'], 'Classe de '.$classe['classe'], $select);
            }
            $myform->closeSelect();
            $myhtml->closeTd();
            $myhtml->openTd('','class="tdchk"');
                $myform->checkbox('chkUsers', "all", "onchange='toutcocher(this)'");
            $myhtml->closeTd();
            $myhtml->closeTr();
            foreach ($eleves_participants as $eleve){
                $myhtml->openTr();
                $myhtml->openTd('', 'style="width:150px;"');
                    echo $eleve['nom'];
                $myhtml->closeTd();
                $myhtml->openTd('','style="width:150px;"');
                    echo $eleve['prenom'];
                $myhtml->closeTd();
                $myhtml->openTd('','class="tdchk"');
                    $myform->checkbox('chkUsers[]', $eleve["id"],"class='chkUsers'");
                $myhtml->closeTd();
                $myhtml->closeTr();
            }
            $myhtml->closeTable();
        $myhtml->closeDiv();
            
        $myhtml->openDiv('','proprietebtn');
            $myform->button('AjouterParticipants', "ajouter");
        $myhtml->closeDiv();  
    $myhtml->closeDialog();
$myhtml->closeDiv();

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

$myhtml->openDiv('dialog-menu');
    $myhtml->openDialog('dialog_menu', $_POST["show_menu"]);
        $myhtml->openDiv('','titre_propriete');
            echo 'Menu';
        $myhtml->closeDiv();
            
        $myhtml->openDiv('','propriete_menu','onclick="show_dialog_menu();show_dialog_users();"');
        $myhtml->openDiv('','iconemenu');
        echo '<img src="img/users.png" title="Importer des classes"/>';
        $myhtml->closeDiv(); 
        $myhtml->openDiv('',"label_menu");
        echo('Importation de noms.');
        $myhtml->closeDiv();  
        $myhtml->closeDiv(); 
        
        $myhtml->openDiv('','propriete_menu','onclick="creer_activite();"');
        $myhtml->openDiv('','iconemenu');
        echo '<img src="img/plus.png" title="Créer une nouvelle activité."/>';
        $myform->button('btn_creer_activite',"&nbsp;",'style="display:none"');
        $myhtml->closeDiv();  
        $myhtml->openDiv('',"label_menu");
        echo('Créer une nouvelle activité.');
        $myhtml->closeDiv(); 
        $myhtml->closeDiv(); 
        
        $myhtml->openDiv('','propriete_menu','onclick="show_dialog_menu();show_dialog_activite();"');
        $myhtml->openDiv('','iconemenu');
        echo '<img src="img/propriete.png" title="Paramètres de l\'activité" />';
        $myhtml->closeDiv(); 
        $myhtml->openDiv('',"label_menu");
        echo('Paramètres de l\'activité activité.');
        $myhtml->closeDiv(); 
        $myhtml->closeDiv(); 

        $myhtml->openDiv('','propriete_menu','onclick="show_dialog_menu();show_dialog_exportation();"');
        $myhtml->openDiv('','iconemenu');
            echo '<img src="img/export.png" title="Exporter les données" />';
        $myhtml->closeDiv(); 
        $myhtml->openDiv('',"label_menu");
        echo('Expoter les données.');
        $myhtml->closeDiv(); 
        $myhtml->closeDiv(); 
        
        $myhtml->openDiv('','propriete_menu','onclick="show_dialog_menu();show_dialog_nettoyage();"');
        $myhtml->openDiv('','iconemenu');
        echo '<img src="img/supprimer.png" title="Nettoyage"/>';
        $myhtml->closeDiv();
        $myhtml->openDiv('',"label_menu");
        echo('Nettoyage...');
        $myhtml->closeDiv(); 
        $myhtml->closeDiv();
        
    $myhtml->closeDialog();
$myhtml->closeDiv();

 /*
 * ------------------------------------------------------
 * Affichage de la barre d'outils
 * Un bouton propiété activité est ajouté sur la ligne sélectionnée -> javascript
 * ------------------------------------------------------
 */ 

$myhtml->openDiv('menu');
    
if($auth){// si pas d'authentiifcation, pas d'enregistrement ni de choix
    
    $myhtml->openDiv('','iconemenu');
        echo '<img src="img/menu.png" '.   
            'title="Menu" ';
        if($enabled){echo 'onclick="show_dialog_menu();"';}
        echo '/>';
    $myhtml->closeDiv(); 


    $myhtml->openDiv('','barre_menu');
    $myhtml->openDiv('','titre');
        echo "Activité : ";
    $myhtml->closeDiv(); 
    
    $myform->openSelect('selActivite', 'selActivite', $disabled.' onchange="this.form.submit();"');
    $myform->option('0', 'Choisir une activite');
     foreach ($myactivite->get_list() as $activite){
        if($activite['id'] == $myactivite->get_id()){$select = true;}else{$select = false;}
        $myform->option($activite['id'], $activite['nom'], $select);
    }
    $myform->closeSelect();

    if($myactivite->get_id()>0){
        
        $myhtml->openDiv('','iconemenu');
            if(intval($myactivite->infos['etat'])==2){
                $img = 'stop1.png';
                $action = 1;
                $title = "Arréter l'enregistrement";
            }else{
                $img = 'start1.png';
                $action = 2;
                $title = "Démarrer l'enregistrement";
            }
            echo '<img src="img/'.$img.'" '.   
                'title="'.$title.'" '.
                ' onclick="starting('.$action.');"/>';           
        $myhtml->closeDiv();
        
        $myhtml->closeDiv(); 

        $myhtml->openDiv('','barre_menu');
        $myhtml->openDiv('','titre');
            echo "Vues : ";
        $myhtml->closeDiv(); 

        $myhtml->openDiv('','iconemenu');
            if($myactivite->infos['vue']=="newtableau.php"){
                $img = 'graphique.png';
                $action = "graphique.php";
                $title = "Vue graphique";
            }else{
                $img = 'tableau.png';
                $action = "newtableau.php";
                $title = "Vue tableau";
            }
            echo '<img src="img/'.$img.'" '.   
                'title="'.$title.'" '.
                ' onclick="setVue(\''.$action.'\');"/>';           
        $myhtml->closeDiv();
        
        /*
         * 
        $myform->openSelect('selVue', 'selVue', 'onchange="this.form.submit();"');
            $myform->option('', 'Choisir une vue');
            $directory = './vues';
            $scanned_directory = array_diff(scandir($directory), array('..', '.'));
            foreach ($scanned_directory as $file){
                if(substr($file, 0, 1)!='_'){
                    if($file == $myactivite->infos['vue']){$select = true;}else{$select = false;}
                    $myform->option($file, $file, $select);
                }
            }
        $myform->closeSelect();
         * 
         */
    }
    $myhtml->closeDiv(); 
    
    } 
        $myhtml->openDiv('','iconemenu');
        echo '<img src="img/logs.png" '.   
            'title="Exporter les données" '.
            'onclick="show_dialog_logs();"/>';  
        $myhtml->closeDiv(); 
        
     
        $myhtml->openDiv('','iconemenu');
            if($auth){
                $img = 'unlock1.png';
                $action = 'password_disconnect(this)';
                $title = "Se connecter";
            }else{
                $img = 'lock1.png';
                $action = 'show_dialog_password()';
                $title = "Se déconnecter";
            }
            echo '<img src="img/'.$img.'" '.   
                'title="'.$title.'" '.
                'onclick="'.$action.';"/>';                
        $myhtml->closeDiv(); 
        
        $myhtml->openDiv('','iconemenu');
            if($auth){
                $img = 'unlock.png';
                $action = 'password_disconnect(this)';
                $title = "Se connecter";
            }else{
                $img = 'lock.png';
                $action = 'show_dialog_password()';
                $title = "Se déconnecter";
            }
            echo '<img src="img/refresh.png" '.   
                'title="actualiser" '.
                'onclick="window.location.href = window.location.href;"/>';                
        $myhtml->closeDiv(); 
$myhtml->closeDiv(); 


 /*
 * ------------------------------------------------------
 * Affichage de la liste des participants et de leur résultats
 * utilisation d'une ligne div par participant
 * utilisation du colonne div par enregistrement 
 * ------------------------------------------------------
 */ 
$myhtml->openDiv('participants');
    $myhtml->openTable('style="width:100%;background-color: #eff3f5 ;"');
    $myhtml->openTr();
    $myhtml->openTd();
        if($myactivite->get_id()>0){echo 'Organisateur : '.$myactivite->infos['organisateur'];}
    $myhtml->closeTd();
    $myhtml->closeTr();
    $myhtml->closeTable();
    
    $myhtml->openTable('id="parts" width="100%"');
    $myhtml->openTr();
    $myhtml->openTd('titre_participants');
        if($myactivite->get_id()>0){
            echo 'Partipants';
            if($auth){
                $myhtml->openDiv('','iconedroite');
                $myform->button("delParts", " -- ");
                $myhtml->closeDiv();
                $myhtml->openDiv('','iconedroite');
                echo '<img src="img/plus.png" '.   
                    'title="Ajouter des participants" '.
                    'style="width:20px; height:20px;" '.
                    'onclick="show_dialog_participants()"/>';
                $myhtml->closeDiv();
            }
        }
    $myhtml->closeTd(); 
    $myhtml->openTd();
        if($myactivite->get_id()>0){
            if($auth){
                $myform->button("makeq", "E");
            }else{
                echo '<div style="text-align:center;font:14px Arial;">E</div>';
            }
        }
    $myhtml->closeTd();
    $myhtml->openTd('titre_id');
        if($myactivite->infos['flag'] & BY_IDMAT){
            echo 'ID mat.';
        }elseif($myactivite->infos['flag'] & BY_RFID){
            echo 'Tag RFID';
        }
    $myhtml->closeTd();
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
            $myhtml->openTr();
            //on détermine la hauteur de la ligne

            $nblineNom = substr_count($participant["nom"],'<br/>')+1;
            if($nbdatas < $nblineNom){
                $nbline = $nblineNom;
            } else {
                $nbline = $nbdatas;
            }
            if($nbline==1){
                $myhtml->openTd('','height='.($nbline*23).'px');
            }else{
                $myhtml->openTd('','height='.($nbline*23+2.75).'px');
            }
            echo $participant["nom"];
            $myhtml->closeTd();
            $myhtml->openTd();
                if($nblineNom>1){
                    $myform->checkbox('chkAssocs[]', $participant["id_participant"]);
                }else{
                    $myform->checkbox('chkParts[]', $participant["id_participant"]);
                }
            $myhtml->closeTd();
            if($myactivite->infos['flag'] & BY_IDMAT){
                if($auth){
                    $myhtml->openTd('','onclick="tdedit(this);"');
                }else{
                    $myhtml->openTd();
                }
                echo $participant['ref_id'];
                $myhtml->closeTd();  
            }elseif($myactivite->infos['flag'] & BY_RFID){
                if($auth){
                    $myhtml->openTd('tag','onclick="tdclick(this);"');
                }else{
                    $myhtml->openTd();
                }
                echo $participant['ref_id'];
                $myhtml->closeTd();   
            }
            $myhtml->closeTr();
        }
    }  
    $myhtml->closeTable();
    
    $myhtml->openTable('style="width:100%;background-color:#eff3f5 ; margin-top:20px; padding-left:20px;"');
        $myhtml->openTr();
        $myhtml->openTd();
            echo "Informations réseau : ";
        $myhtml->closeTd(); 
        $myhtml->closeTr();
        $interfaces = net_get_interfaces();
        if ($interfaces){
 
            foreach($interfaces as $nom=>$interface){
                
                $unicasts = $interface['unicast'];
                if (isset($unicasts[1]['address'])) {
                    $address = $unicasts[1]['address'];
                    $address = substr($address,strpos($address,"<br"));
                }
                if (isset($unicasts[1]['netmask'])) {
                    $netmask = $unicasts[1]['netmask'];
                    $netmask = substr($netmask,strpos($netmask,"<br"));
                }
 
                if(strpos($address,'127')!==0){
                $myhtml->openTr();
                $myhtml->openTd();
                    echo $nom." : ".$address." / ".$netmask;
                $myhtml->closeTd(); 
                $myhtml->closeTr();
                }

            }
        }
   $myhtml->closeTable();
    
    
    
$myhtml->closeDiv();

if(!empty($myactivite->infos['vue'])){
echo '<iframe '.
  'id="datas" '.
  'src="./vues/'.$myactivite->infos['vue'].'">'.
  '</iframe>';
}
 /*
 * ------------------------------------------------------
 * Fermeture de la page
 * ------------------------------------------------------
 */ 

$myform->closeForm();
$myhtml->closeBody();
$myhtml->closeHtml();




