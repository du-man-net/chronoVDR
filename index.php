<?php
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set("display_errors", 1);
session_start();

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

connect_db();

$myactivite = new Activite($mysqli);
$myusers = new Users($mysqli);
$myform = new Form;
$myhtml = new Html;

/*
 * ------------------------------------------------------
 * Mise en place des headers de la page 
 * isertion du fichier css et du fichier javascript
 * ------------------------------------------------------
 */
$myhtml->openHtml();
echo '<title>ChronoVDR</title>';
echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8">';
echo '<script type="text/javascript" src="script.js"></script>';
echo '<link rel="stylesheet" href="style.css" type="text/css" />';
setlocale (LC_ALL, 'fr_FR.utf8');
date_default_timezone_set('Europe/Paris');
$myhtml->closeHead();

$myhtml->openBody();


function logout() {
    session_destroy();
}

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
if(isset($_POST['auth'])){
    if( -1  ==  $_POST['auth'] ) {
        session_destroy();
        $auth=0;
    }
    if( $admin_password  ==  $_POST['auth'] ) {
        $_SESSION['chronoVDR'] = "all";
        $auth=1;
    }
    $_POST["show_password"]='close';
}

/*
 * ------------------------------------------------------
 * Retrouver la l'activité sélectionnée dans le formulaire
 * ------------------------------------------------------
 */
if(!empty($_POST['selActivite'])){
    if($myactivite->get_id() != $_POST['selActivite']){
        $myactivite->set_id ($_POST['selActivite']);
    }
}

if($auth){// si pas d'authentiifcation, pas d'enregistrement ni de choix
/*
 * ------------------------------------------------------
 * création et/ou enregistrement des paramètres de l'activitée 
 * ------------------------------------------------------
 */
if(isset($_POST['creer_activite']) or
   isset($_POST['enregistrer_activite'])){ 
    
    if(empty($_POST['nom_activite']) or 
        empty($_POST['organisateur']) or 
        empty($_POST['date_activite']) or 
        empty($_POST['date_activite'])){
            if(empty($_POST['nom_activite'])) {$message = 'Remplir nom !';}
            if(empty($_POST['organisateur'])) {$message = 'Remplir organisateur !';}
            if(empty($_POST['date_activite'])){$message = 'Remplir date_activite !';}  
    }else{
        $myactivite->infos['nom']            = $_POST['nom_activite'];
        $myactivite->infos['organisateur']   = $_POST['organisateur'];
        $myactivite->infos['date_activite']  = $_POST['date_activite']." ".$_POST['heure_activite'];
        $myactivite->infos['repetition']     = $_POST['repetition'];
        $myactivite->infos['identification'] = $_POST['identification'];
        $myactivite->infos['nb_max']         = $_POST['nb_max'];
        $myactivite->infos['temps_max']      = $_POST['temps_max'];
        
        //if(!empty($_POST['password'])){
        //    $myactivite->set_password(sha1($_POST['password']));
        //}
        if(isset($_POST['creer_activite'])){
            $myactivite->create();
        }
        if(isset($_POST['enregistrer_activite'])){
            $myactivite->save();
        }
        $_POST["show_activite"]='close';
    }
}

/*
 * ------------------------------------------------------
 * Lire un fichier CSV pour importation
 * ------------------------------------------------------
 */
if(isset($_POST['readcsvfile'])){
    if(isset($_POST['importClasse'])){
        $classe = $_POST['importClasse'];}
        $myimport = new Import($_FILES['fileImport']);
        if($myimport->is_csv_file()){
            $myimport->read_file();
            $eleves_read = $myimport->getElevesArray($classe);
            //var_dump($eleves_read);
        }
    }
}

/*
 * ------------------------------------------------------
 * Importer la liste des users à partir du texte mis en forme
 * ------------------------------------------------------
 */
if(isset($_POST['importer'])){
    if(isset($_POST['lstusers'])){
        $myusers->importUsers($_POST['lstusers']);
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
 * Retrouver la classe sélectionnée dans le formulaire
 * ------------------------------------------------------
 */
if(!empty($_POST['selclasse'])){
    $selectedClasse = $_POST['selclasse'];
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
if(!empty($_POST['selclasseParticipants'])){
    $selectedClassePartcipants = $_POST['selclasseParticipants'];
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
    $myactivite->set_vue($_POST['selVue']);
}
/*
 * ------------------------------------------------------
 * Gestion de la possibilité de recevoir des datas
 * ------------------------------------------------------
 */

if(isset($_POST['etat'])){
    if(intval($_POST['etat'])==2){
        $myactivite->start();
    }else{
        $myactivite->stop();
    }
}
/*
 * ------------------------------------------------------
 * Une fois les données enregistrée, on relit les paramètres de l'activité
 * pour mettre à jour l'innterface.
 * ------------------------------------------------------
 */



$myactivite->refresh();

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

if(!isset($_POST['etat'])){$_POST['etat'] = "0";}
$myform->hidden('etat', $_POST["etat"]);

/*
 * ------------------------------------------------------
 * Fenètre d''authentification par mot de passe pour l'activité
 * ------------------------------------------------------
 */
$myhtml->openDiv('div-password');
    $myhtml->openDialog('propriete_password', $_POST["show_password"], ' style = "left:400px;top:40px;"');
        $myhtml->openDiv('','titre_propriete');
            echo 'Mdp pour '.$myactivite->infos['nom'];
        $myhtml->closeDiv();
        $myhtml->openDiv('','propriete');
            $myform->label('auth','Mot de passe');
            $myform->password('auth','','onkeydown="touche(event.key);"');
        $myhtml->closeDiv(); 
        $myhtml->openDiv('','proprietebtn');
            $myform->button('cancel_password', "Annuler",'onclick="return cancel_dialog_password()"');
            $myform->button('valider_PW', "Valider");
        $myhtml->closeDiv();   
    $myhtml->closeDialog();
$myhtml->closeDiv();   





if($auth){// si pas d'authentiifcation, pas d'enregistrement ni de choix
/*
 * ------------------------------------------------------
 * Fenètre de paramétrage de l'activité
 * Le bouton btnaction prend les valeurs créer ou enregistrer
 * en fonction de l'utilisation -> javascript
 * ------------------------------------------------------
 */
$myhtml->openDiv('div-activite');
    $myhtml->openDialog('propriete_acivite', $_POST["show_activite"], ' style = "left:400px;top:40px;"');
        $myhtml->openDiv('proptitle','titre_propriete');
            echo 'titre à modifié en fonction de l\'usage';
        $myhtml->closeDiv();
        $myhtml->openDiv('','propriete');
            $myform->label('nom_activite','Nom de l\'activité');
            $myform->text('nom_activite', $myactivite->infos['nom']);
        $myhtml->closeDiv();
        $myhtml->openDiv('','propriete');
            $myform->label('organisateur','Organisateur');
            $myform->text('organisateur', $myactivite->infos['organisateur']);
        $myhtml->closeDiv();  
        //$myhtml->openDiv('','propriete');
         //   $myform->label('password','Mot de passe');
         //   $myform->password('password','');
        //$myhtml->closeDiv();  
        $myhtml->openDiv('','propriete');
            $myform->label('date_activite','Date de l\'activité');
            $myform->date('date_activite', date('Y-m-d', strtotime($myactivite->infos["date_activite"])));
        $myhtml->closeDiv();  
        $myhtml->openDiv('','propriete');
            $myform->label('heure_activite','Heure de l\'activité');
            $myform->time('heure_activite', date('H:i', strtotime($myactivite->infos["date_activite"])));
        $myhtml->closeDiv();  
        $myhtml->openDiv('','propriete');
            $myform->label('identification','Identification');
            $myform->openSelect('identification','identification');
            if($myactivite->infos["identification"] == 'materiel'){$select = true;}else{$select = false;}
            $myform->option('materiel','Id_matériel', $select);
            if($myactivite->infos["identification"] == 'dossard'){$select = true;}else{$select = false;}
            $myform->option('dossard','Dossard', $select);
            if($myactivite->infos["identification"] == 'rfid'){$select = true;}else{$select = false;}
            $myform->option('rfid','RFID', $select);
            $myform->closeSelect();
        $myhtml->closeDiv(); 
        $myhtml->openDiv('','propriete');
            $myform->label('repetition','repetition');
            $myform->openSelect('repetition','repetition');
            if($myactivite->infos["repetition"] == 'essais'){$select = true;}else{$select = false;}
            $myform->option('essais','Plusieurs essais', $select);
            if($myactivite->infos["repetition"] == 'passages'){$select = true;}else{$select = false;}
            $myform->option('passages','Plusieurs passage', $select);
            $myform->closeSelect();
        $myhtml->closeDiv();  
        $myhtml->openDiv('','propriete');
            $myform->label('nb_max','Nombre maximum d\'enregistrements');
            $myform->text('nb_max', $myactivite->infos['nb_max']);
        $myhtml->closeDiv();  
        $myhtml->openDiv('','propriete');
            $myform->label('temps_max','Temps maximum');
            $myform->time('temps_max', date('H:i:s', strtotime($myactivite->infos["temps_max"])));
        $myhtml->closeDiv();  
        $myhtml->openDiv('','proprietebtn');
            $myform->button('cancel_activite', "Annuler",'onclick="return cancel_dialog_activite()"');
            $myform->button('btnaction', "Enregistrer");
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

$myhtml->openDiv('div-nettoyage');
    $myhtml->openDialog('propriete_nettoyage', $_POST["show_nettoyage"], ' style = "left:400px;top:40px;"');
        $myhtml->openDiv('','net');
            $myhtml->openDiv('','titre_propriete');
                echo 'Supprimer les données de l\'activité';
            $myhtml->closeDiv();
            $myhtml->openDiv('','proprietebtn');
                $myform->button('del_datas', "Supprimer");
            $myhtml->closeDiv();   
            $myhtml->openDiv('','titre_propriete');
                echo 'Supprimer l\'activité';
            $myhtml->closeDiv();
            echo 'Supprimer l\'activitée et toutes<br/>les données qu\'elle contient';
            $myhtml->openDiv('','proprietebtn');
                $myform->button('del_activites', "Supprimer");
            $myhtml->closeDiv();  
            $myhtml->openDiv('','titre_propriete');
                echo 'Supprimer les élèves non classés';
            $myhtml->closeDiv();
            echo 'Supprimer définitivement les élèves<br/>qui ne n\'ont plus de classe et qui ne sont<br/>concernés par aucun enregistrement';
            $myhtml->openDiv('','proprietebtn');
                $myform->button('del_users', "Supprimer");
            $myhtml->closeDiv();  

        $myhtml->closeDiv(); 
        $myhtml->openDiv('','proprietebtn');
            $myform->button('cancel_nettoyage', "Annuler",'onclick="return cancel_dialog_nettoyage()"');
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
    $myhtml->openDialog('add_users', $_POST["show_users"], ' style = "left:400px;top:40px;"');
        $myhtml->openDiv('','titre_propriete');
            echo 'Importer des classes';
        $myhtml->closeDiv();
        $myhtml->openDiv('','propriete');
            $myform->label('importClasse','Classe');
            $myform->text('importClasse', '');
        $myhtml->closeDiv();
        $myhtml->openDiv('','propriete');
            $myform->label('fileImport','fichier csv');
            $myform->file('fileImport');
        $myhtml->closeDiv();  
        $myhtml->openDiv('','proprietebtn');
            $myform->button('cancel_users1', "Annuler",'onclick="return cancel_dialog_users()"');
            $myform->button('readcsvfile', "Importer");
        $myhtml->closeDiv();
               
        if(isset($_POST['readcsvfile'])){

            $myhtml->openDiv('','titre_propriete');
                echo 'Liste des élèves à importer';
            $myhtml->closeDiv();
            $myhtml->openDiv('propriete');
            $lstusers = '';
            foreach ($eleves_read as $eleve){
                $lstusers .= $eleve['nom'].','.$eleve['prenom'].','.$eleve['nais'].','.$eleve['classe']."\n";
            }
            $myhtml->textarea('lstusers', $lstusers);
            $myhtml->closeDiv();
            $myhtml->openDiv('','proprietebtn');
                $myform->button('cancel_users', "Annuler",'onclick="return cancel_dialog_users()"');
                $myform->button('importer', "Valider");
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
    $myhtml->openDialog('add_participants', $_POST["show_participants"], ' style = "left:400px;top:40px;"');
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
            $myform->button('cancel_participant', "Annuler",'onclick="return cancel_dialog_participants()"');
            $myform->button('AjouterParticipants', "ajouter");
        $myhtml->closeDiv();  
    $myhtml->closeDialog();
$myhtml->closeDiv();

}//fin restriction auth

 /*
 * ------------------------------------------------------
 * Affichage de la barre d'outils
 * Un bouton propiété activité est ajouté sur la ligne sélectionnée -> javascript
 * ------------------------------------------------------
 */ 

$myhtml->openDiv('menu');
    
if($auth){// si pas d'authentiifcation, pas d'enregistrement ni de choix
    $myhtml->openDiv('','iconemenu');
        echo '<img src="img/users.png" '.   
            'title="Gérer les élèves" '.
            'style="width:30px; height:30px;" '.
            'onclick="show_dialog_users();"/>';
    $myhtml->closeDiv(); 
    
    $myhtml->openDiv('','iconemenu');
        echo '<img src="img/supprimer.png" '.   
            'title="Nettoyage" '.
            'style="width:30px; height:30px;" '.
            'onclick="show_dialog_nettoyage();"/>';
    $myhtml->closeDiv(); 

    $myhtml->openDiv('','iconemenu');
        echo '<img src="img/plus.png" '.   
            'title="Crée une nouvelle activité" '.
            'style="width:30px; height:30px;" '.
            'onclick="show_dialog_activite(true)"/>';
    $myhtml->closeDiv(); 

    $myform->openSelect('selActivite', 'selActivite', 'onchange="this.form.submit();"');
    $myform->option('0', 'Choisir une activite');
     foreach ($myactivite->list() as $activite){
        if($activite['id'] == $myactivite->get_id()){$select = true;}else{$select = false;}
        $myform->option($activite['id'], $activite['nom'], $select);
    }
    $myform->closeSelect();
}
    if($myactivite->get_id()>0){
        $myhtml->openDiv('','proprietemenu');
            echo 'Org. : '.$myactivite->infos['organisateur'];
        $myhtml->closeDiv(); 

        $myhtml->openDiv('','proprietemenu');
            echo ''.$myactivite->infos['date_fr'];
        $myhtml->closeDiv(); 
         
        
if($auth){// si pas d'authentiifcation, pas d'enregistrement ni de choix
        $myhtml->openDiv('','iconemenu');
            echo '<img src="img/propriete.png" '.
                'title="Paramètres de l\'activité" '.
                'style="width:30px; height:30px;" '.
                'onclick="show_dialog_activite(false)"/>';
        $myhtml->closeDiv(); 


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

        $myhtml->openDiv('','iconemenu');
            if(intval($myactivite->infos['etat'])==2){
                $img = 'stop.png';
                $action = 1;
            }else{
                $img = 'start.png';
                $action = 2;
            }
            echo '<img src="img/'.$img.'" '.   
                'title="Démarrer" '.
                'style="width:30px; height:30px;" '.
                'onclick="starting('.$action.');"/>';            
        $myhtml->closeDiv();
}
}
        $myhtml->openDiv('','iconemenu');
            if($auth==1){
                $img = 'unlock.png';
                $action = 'password_disconnect(this)';
            }else{
                $img = 'lock.png';
                $action = 'show_dialog_password()';
            }
            echo '<img src="img/'.$img.'" '.   
                'title="Authentifer" '.
                'style="width:30px; height:30px;" '.
                'onclick="'.$action.';"/>';                
        $myhtml->closeDiv(); 

        $myhtml->openDiv("cartes_reseaux");
            $interfaces = net_get_interfaces();
            if ($interfaces){
                foreach($interfaces as $interface){
                    $unicasts = $interface['unicast'];
                    if (isset($unicasts[1]['address'])) {
                        $address = $unicasts[1]['address'];
                        $address = substr($address,strpos($address,"<br"));
                    }
                    if (isset($unicasts[1]['netmask'])) {
                        $netmask = $unicasts[1]['netmask'];
                        $netmask = substr($netmask,strpos($netmask,"<br"));
                    }
                    if(strpos($address,'127')===false){
                        echo $address." / ".$netmask."<br/>";
                    }
                }
            }
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
            if($myactivite->infos['identification']=='materiel'){
                echo 'ID mat.';
            }elseif($myactivite->infos['identification']=='dossard'){
                echo 'Dossard';
            }elseif($myactivite->infos['identification']=='rfid'){
                echo 'Tag RFID';
            }
        $myhtml->closeTd();
    $myhtml->closeTr();
    
    $participants = $myactivite->get_participants();
    if (!empty($participants)){
        $last_id_assoc = 0; $drawline = true;
        foreach ($participants as $participant) {
            $myhtml->openTr();
            $myhtml->openTd('','height=22');
            echo $participant["nom"];
            $myhtml->closeTd();
            $myhtml->openTd();
                if(str_contains($participant["nom"],'<br/>')){
                    $myform->checkbox('chkAssocs[]', $participant["id_participant"]);
                }else{
                    $myform->checkbox('chkParts[]', $participant["id_participant"]);
                }
            $myhtml->closeTd();
            if($myactivite->infos['identification']=='materiel'){
                if($auth){
                    $myhtml->openTd('','contenteditable="true" onblur="tdedit(this);"');
                }else{
                    $myhtml->openTd();
                }
                echo $participant['ref_id'];
                $myhtml->closeTd();  
            }elseif($myactivite->infos['identification']=='dossard'){
                $myhtml->openTd();
                echo $participant['ref_id'];
                $myhtml->closeTd();   
            }elseif($myactivite->infos['identification']=='rfid'){
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
$myhtml->closeDiv();

if(!empty($myactivite->infos['vue'])){
echo '<iframe '.
  'id="datas" '.
  'src="./vues/'.$myactivite->infos['vue'].'?id='.$myactivite->get_id().'">'.
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


