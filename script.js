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

var askStopEditing = false;
var tdInEdition = 0;
var oFrame;
var last_maj=0;
var last_log=0;

/**
 * Gestion dimensionnement en hauteur de l'iframe
 */

window.onload = function () {
    oFrame = window.document.getElementById('datas');
    
    setInterval(function () {
        
        //etat de l'activité    0=non séléctionnée 
        //                      1=sélectionnée 
        //                      2= sélectionnée et enrestrement actif
        //                      
        //Si l'enregistrement est actif, alord on surveille les modifs 
        //dans la base de donnée toutes les secondes pour affichage rapide
        
        if (document.getElementsByName('etat')[0].value == 2) {

            var xhttp = new XMLHttpRequest();
            xhttp.open("GET", encodeURI("last_update.php", true));
            xhttp.onreadystatechange = function () {
                if (this.readyState === 4 && this.status === 200) {
                    //si la réponse AJAX n'est pas vide
                    
                    if (this.responseText.length > 0) {       
                        //si la date de la dernière maj de la bdd est différente de celle mémorisée
                        if(last_maj !== this.responseText){
                            console.log(last_maj);
                            //on enregistre la date de la maj
                            last_maj = this.responseText;
                            //on envoi un message à l'iframe pour la qu'elle mette les donées à jour
                            document.getElementById('datas').contentWindow.postMessage("message", "*");
                        }
                    }
                   
                }
            };
            xhttp.send();
        }else if (document.getElementsByName('etat')[0].value == 1) {
            const el_dialog = document.getElementById("logs");
            showlogs = true;
            if (el_dialog.style.display) {
                if (el_dialog.style.display==="none") {
                    showlogs = false;
                }
            }
            if (showlogs===true) {
                var xhttp = new XMLHttpRequest();
                xhttp.open("GET", encodeURI("last_logs.php", true));
                xhttp.onreadystatechange = function () {
                    if (this.readyState === 4 && this.status === 200) {
                        //si la réponse AJAX n'est pas vide

                        if (this.responseText.length > 0) { 
                            content = this.responseText;
                            //si la date de la dernière maj de la bdd est différente de celle mémorisée
                            if(last_log !== content){
                                //on enregistre la date de la maj
                                last_log = content;
                                //on ajoute l'élément à la console de log
                                document.getElementById('logs').innerHTML = content;
                            }
                        }

                    }
                };
                xhttp.send();
            }
        }

    }, 500);
    resizeIframe.call(oFrame);
};


// fonction appelée par la page enfant, le document contenu dans l'iframe
function ajusteIframe() {
    resizeIframe.call(oFrame);
}

// fonction de redimensionnement de l'iframe
function resizeIframe() {
    const iframe = this;
    const doc = iframe.contentDocument;
    if (doc && doc.documentElement) {
        iframe.style.height = doc.documentElement.offsetHeight + "px";
    }
}

function getIdParticipantFromTD(el) {
    var tr = el.parentElement;
    var id_participant = tr.children[1].children[0].value;
    return id_participant;
}

function nextTDbyRow(el) {
    var tr = el.parentElement;
    next_tr = tr.parentNode.rows[ tr.rowIndex + 1 ];
    if (next_tr) {
        next_td = next_tr.children[2];
        return next_td;
    }
    return false;
}

function resetTdColor() {
    var el = document.getElementsByClassName("tag");
    for (var h = 0; h < el.length; h++) {
        el[h].style.backgroundColor = 'white';
    }
}

function setNewId(el){
    if (el.innerHTML){
        var contenu = el.innerHTML;
    }
    contenu = contenu.split("<br>")[0];
    var id_participant = getIdParticipantFromTD(el);

    var xhttp = new XMLHttpRequest();
    xhttp.open("GET", encodeURI("ajax_back.php?id_participant=" + id_participant + "&ref_id=" + contenu), true);
    xhttp.send();
    el.style.backgroundColor = "white";
    el.setAttribute("contenteditable", false);
    el.blur();
}

function tdedit(el) {
    el.setAttribute("contenteditable", true);
    el.style.backgroundColor = "#f2f3f4";
    el.focus();
    el.addEventListener("keydown", function(event){
        if (event.key === "Enter") {
            setNewId(el);
            event.preventDefault();
            return false;
        }
    },false);
}

function tdclick(el) {
    //si pas d'édition de TGA en cours, on lance l'édition
     if(tdInEdition === 0){
        tdInEdition = 1;
        resetTdColor();

        //on mémorise l'ancien tag et on passe la cellule en jaune
        var old_RFID = "";
        if(!!el.innerHTML){
            old_RFID = el.innerHTML;
        }
        console.log(old_RFID);
        //on marque le td en cours et on passe la cellule en jaune
        el.id = "tagToChange";
        //el.innerHTML = "Badgez...";
        el.style.backgroundColor = "yellow";

        //on récupère l'id du participant sur la même ligne
        var id_participant = getIdParticipantFromTD(el);

        //on appelle le script de récupération de TAG
        var xhttp = new XMLHttpRequest();
        xhttp.open("GET", encodeURI("ajax_back.php?id_participant=" + id_participant), true);

        xhttp.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {

                //si on a une réponse positive de l'appelle AJAX
                //on reprend le tag td en cours d'édition
                var tagToChange = document.getElementById("tagToChange");

                //si la réponse AJAX n'est pas vide
                if (this.responseText.length > 0) {
                    //si l'édition est toujours en mode scan
                    //on clos l'édition et on passe à l'édition du tag suivant
                    if (tdInEdition === 1) {
                        tagToChange.innerHTML = this.responseText;
                        tagToChange.style.backgroundColor = "white";
                        nextTD = nextTDbyRow(tagToChange);
                        tagToChange.id = "";
                        tdInEdition = 0;
                        if (nextTD) {
                            tdclick(nextTD);
                        }
                    }
                } else {
                    //si l'édition est toujours en mode scan
                    //on clos l'édition
                    if (tdInEdition === 1) {
                        tagToChange.innerHTML = old_RFID;
                        tagToChange.style.backgroundColor = "white";
                        tagToChange.id = "";
                        tdInEdition = 0;
                        el.blur();
                    }
                }

            }
        };
        xhttp.send();
        
    // si on a déja clicker une fois
    } else if(tdInEdition === 1){
        tdInEdition = 2;
        resetTdColor();
        el.setAttribute("contenteditable", true);
        el.style.backgroundColor = "#f2f3f4";
        el.focus();
        el.addEventListener("keydown", function(event){
            if (event.key === "Enter") {
                tdInEdition = 0;
                console.log("validation" + tdInEdition);
                setNewId(el);
                event.preventDefault();
                return false;
            }
        },false);
    }
}

function starting(state) {
    e = document.getElementsByName('etat')[0];
    e.value = state;
    console.log(state);
    document.forms[0].submit();
}

function touche(el) {
    if (el==='Enter'){
        document.forms[0].submit();
    }
}

function toutcocher(el) {
    const chks = document.getElementsByClassName("chkUsers");
    for (i = 0; i < chks.length; i++) {
        if (el.checked === true) {
            chks[i].checked = true;
        } else {
            chks[i].checked = false;
        }
    }
}

var title_activite = '';
var organisateur = '';
var date_activite = '';
var heure_activite = '';

function saveValue($name) {
    $val = document.getElementById($name).value;
    document.getElementById($name).value = '';
    return $val;
}

function setValue($name, $value) {
    document.getElementById($name).value = $value;
}

function save_type_activite(el){
    const save_activite = document.getElementById('change_activite');
    if(save_activite) {
        save_activite.value = "ok";
    }
    el.form.submit();
}
//function show_dialog_activite($create) {
//    const $propriete_activite = document.getElementById("propriete_acivite");
//    const $show_activite = document.getElementById("show_activite");
//    const $btnaction = document.getElementById("btnaction");
//    const $titre = document.getElementById("proptitle");
//    if (!$propriete_activite.open) {
//        $show_activite.value = "open";
//        if ($create === true) {
//            if (document.getElementById('title_activite').value.length > 0) {
//                title_activite = saveValue('title_activite');
//                organisateur = saveValue('organisateur');
//            }
//            $btnaction.name = 'creer_activite';
//            $btnaction.innerHTML = 'Créer';
//            $titre.innerHTML = "création d'une nouvelle activité";
//
//        } else {
//            if (document.getElementById('title_activite').value.length === 0) {
//                setValue('title_activite', title_activite);
//                setValue('organisateur', organisateur);
//            }
//            $btnaction.name = 'enregistrer_activite';
//            $btnaction.innerthml = 'Enregistrer';
//            $titre.innerHTML = 'Paramètres de l\'activité';
//        }
//        $propriete_activite.show();
//    }
//}
function show_dialog_logs() {
    const el_dialog = document.getElementById("logs");
    const el_value = document.getElementById("show_logs");
    if (el_dialog.style) {
        if (el_dialog.style.display) {
            if (el_dialog.style.display==="none") {
                el_value.value = "normal";
                el_dialog.style.display = null;
                return;
            }
        }
    }
    el_value.value = "none";
    el_dialog.style.display = "none";
}

function dialog(name, value, state) {
    const el_dialog = document.getElementById(name);
    const el_value = document.getElementById(value);
    if (state === "open") {
        if (!el_dialog.open) {
            el_value.value = state;
            el_dialog.left="800px";
            el_dialog.top="10px";
            el_dialog.show();
        }
    } else {
        if (el_dialog.open) {
            el_value.value = state;
            el_dialog.close();
        }
    }
}

function show_dialog_activite() {
    dialog("propriete_acivite", "show_activite", "open");
}

function show_dialog_users() {
    dialog("add_users", "show_users", "open");
}

function show_dialog_participants() {
    dialog("add_participants", "show_participants", "open");
}


function show_dialog_password() {
    dialog("propriete_password", "show_password", "open");
}

function show_dialog_nettoyage() {
    dialog("propriete_nettoyage", "show_nettoyage", "open");
}

function show_dialog_exportation() {
    dialog("propriete_exportation", "show_exportation", "open");
}

function cancel_dialog_users() {
    dialog("add_users", "show_users", "close");
    return false;
}

function cancel_dialog_participants() {
    dialog("add_participants", "show_participants", "close");
    return false;
}

function cancel_dialog_activite() {
    dialog("propriete_acivite", "show_activite", "close");
    return false;
}

function cancel_dialog_password() {
    dialog("propriete_password", "show_password", "close");
    return false;
}

function cancel_dialog_nettoyage() {
    dialog("propriete_nettoyage", "show_nettoyage", "close");
    return false;
}

function cancel_dialog_exportation() {
    dialog("propriete_exportation", "show_exportation", "close");
    return false;
}

function password_disconnect() {
    document.getElementById('auth').value = -1;
    document.forms[0].submit();
}

