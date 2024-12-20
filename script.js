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

var askStopEditing = false;
var tdInEdition = false;
var oFrame;

/**
 * Gestion dimensionnement en hauteur de l'iframe
 */

window.onload = function () {
    oFrame = window.document.getElementById('datas');
    setInterval(function () {
        console.log(document.getElementsByName('etat')[0].value);
        if (document.getElementsByName('etat')[0].value == 2) {
            var frame_id = 'datas';
            document.getElementById(frame_id).contentWindow.postMessage("message", "*");
        }
    }, 10000);
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
        //iframe.style.width = (doc.documentElement.clientWidth) + "px"
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

function tdedit(el) {
    
    const e = el.firstChild;
    if (e.innerHTML){
        var contenu = e.innerHTML;
    }else{
        var contenu = el.innerHTML;
    }
    contenu = contenu.split("<br>")[0];
    var id_participant = getIdParticipantFromTD(el);

    var xhttp = new XMLHttpRequest();
    xhttp.open("GET", encodeURI("ajax_back.php?id_participant=" + id_participant + "&ref_id=" + contenu), true);
    xhttp.send();
}

function tdclick(el) {
    //si pas d'édition de TGA en cours, on lance l'édition
    if (el.id !== "tagToChange" && tdInEdition === false) {
        askStopEditing = false;
        tdInEdition = true;
        resetTdColor();

        //on mémorise l'ancien tag et on passe la cellule en jaune
        var old_RFID = el.innerHTML;
        console.log(old_RFID);
        //on marque le td en cours et on passe la cellule en jaune
        el.id = "tagToChange";
        el.innerHTML = "Badgez...";
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
                    //le tag a bien été changé on repasse en blanc
                    tagToChange.innerHTML = this.responseText;
                    tagToChange.style.backgroundColor = "white";
                    //fin de l'édition et passge au tag suivant
                    tagToChange.id = "";
                    tdInEdition = false;
                    if (askStopEditing === false) {
                        nextTD = nextTDbyRow(tagToChange);
                        if (nextTD) {
                            tdclick(nextTD);
                        }
                    }
                } else {
                    //sinon, ça c'est mal passé, on remet l'ancien contenu
                    //on signal l'erreur en rouge
                    tagToChange.innerHTML = old_RFID;
                    tagToChange.style.backgroundColor = "red";
                    //fin de l'édition et passge au tag suivant
                    tagToChange.id = "";
                    tdInEdition = false;
                }
            }
        };
        xhttp.send();
    } else {
        //si une édition de TAG est en cours, on demande l'arret
        //et on passe tout en blanc
        askStopEditing = true;
        resetTdColor();
    }
}

function starting(state) {
    e = document.getElementsByName('etat')[0];
    e.value = state;
    console.log(e);
    console.log(state);
    document.forms[0].submit();
}

function touche(el) {
    if (el=='Enter'){
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

var nom_activite = '';
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

function show_dialog_activite($create) {
    const $propriete_activite = document.getElementById("propriete_acivite");
    const $show_activite = document.getElementById("show_activite");
    const $btnaction = document.getElementById("btnaction");
    const $titre = document.getElementById("proptitle");
    if (!$propriete_activite.open) {
        $show_activite.value = "open";
        if ($create === true) {
            if (document.getElementById('nom_activite').value.length > 0) {
                nom_activite = saveValue('nom_activite');
                organisateur = saveValue('organisateur');
                date_activite = saveValue('date_activite');
                heure_activite = saveValue('heure_activite');
            }
            $btnaction.name = 'creer_activite';
            $btnaction.innerHTML = 'Créer';
            $titre.innerHTML = "création d'une nouvelle activité";

        } else {
            if (document.getElementById('nom_activite').value.length === 0) {
                setValue('nom_activite', nom_activite);
                setValue('organisateur', organisateur);
                setValue('date_activite', date_activite);
                setValue('heure_activite', heure_activite);
            }
            $btnaction.name = 'enregistrer_activite';
            $btnaction.innerthml = 'Enregistrer';
            $titre.innerHTML = 'Paramètres de l\'activité';
        }
        $propriete_activite.show();
    }
}

function dialog($name, $value, $state) {
    const $el_dialog = document.getElementById($name);
    const $el_value = document.getElementById($value);
    if ($state === "open") {
        if (!$el_dialog.open) {
            $el_value.value = $state;
            $el_dialog.show();
        }
    } else {
        if ($el_dialog.open) {
            $el_value.value = $state;
            $el_dialog.close();
        }
    }
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

function password_disconnect() {
    document.getElementById('auth').value = -1;
    document.forms[0].submit();
}

function select_activite(el) {
    const $id = el.value;
    console.log($id);
    if ($id > 0) {
        document.location.href = "index.php?id=" + $id;
    }
}

