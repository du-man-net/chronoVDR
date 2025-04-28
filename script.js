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
var xhttpEdit;
var last_logs_index = 0;

/**
 * Gestion dimensionnement en hauteur de l'iframe
 */

window.onload = function () {
    console.log = console.info;
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
                            //console.log(last_maj);
                            //on enregistre la date de la maj
                            last_maj = this.responseText;
                            //on envoi un message à l'iframe pour la qu'elle mette les donées à jour
                            document.getElementById('datas').contentWindow.postMessage("message", "*");
                        }
                    }
                   
                }
            };
            xhttp.send();
        }
        
        if (document.getElementsByName('etat')[0].value > 0) {
        //if (parseInt(document.getElementsByName('etat')[0].value) > 0) {
            const el_dialog = document.getElementById("logs");
            showlogs = true;
            if (el_dialog.style.display) {
                if (el_dialog.style.display==="none") {
                    showlogs = false;
                }
            }
            if (showlogs===true) {

                var xhttplog = new XMLHttpRequest();
                xhttplog.open("GET", encodeURI("last_logs.php?idx="+last_logs_index, true));
                xhttplog.onreadystatechange = function () {
                    if (this.readyState === 4 && this.status === 200) {
                        //si la réponse AJAX n'est pas vide
                        
                        if (this.responseText.length > 0) { 
                            
                            const datas = JSON.parse(this.responseText);
                            
                            last_logs = datas['logs'];
                            if(last_logs.length>0){
                                
                                txt_logs = document.getElementById('logs');
                                
                                for (const last_log of last_logs) {
                                    var newDiv = document.createElement("div");
                                    var newContent = document.createTextNode(last_log);
                                    newDiv.appendChild(newContent);
                                    txt_logs.append(newDiv);
                                    if (txt_logs.childElementCount > 15) {
                                        txt_logs.firstElementChild.remove();
                                    }
                                }
                                //on récupère le dernier index pour minimiser les reqêtes suivantes
                                last_logs_index = datas['last_index'];
                            }
                        }

                    }
                };
                xhttplog.send();
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
    contenu = "";
    if (el.innerHTML){
        var ctn = el.innerHTML;
        console.log(ctn);
    }

    var id_participant = getIdParticipantFromTD(el);
    //console.log(id_participant);
    
    var xhttp = new XMLHttpRequest();
    //on appelle le script de récupération de TAG
    xhttp.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            console.log("ref_id changé pour "+id_participant);
            el.style.backgroundColor = "white";
            el.setAttribute("contenteditable", false);
            el.blur();
        }
    };
    xhttp.open("GET", "ajax_back.php?id_participant=" + encodeURI(id_participant) + "&ref_id=" + encodeURI(ctn), true);
    xhttp.send();
    

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
    if(xhttpEdit){
        xhttpEdit.abort();
        //console.log("xhttpEdit aborted");
    }
    xhttpEdit = new XMLHttpRequest();
    
    if(tdInEdition === 0){
        tdInEdition = 1;
        
        //on supprime les résidus de couleur jaune
        resetTdColor();

        //on marque le td en cours et on passe la cellule en jaune
        el.id = "tagToChange";
        el.style.backgroundColor = "yellow";

        //on récupère l'id du participant sur la même ligne
        var id_participant = getIdParticipantFromTD(el);
        //console.log("id_participant="+id_participant);

        //on appelle le script de récupération de TAG
        xhttpEdit.onreadystatechange = function() {
            //console.log (this.readyState + " " + this.status);
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {

                //si on est dans le mode scan
                if (tdInEdition === 1) {
                    //console.log("mode edition");
                    
                    //on retroue le case du RFID en cours d'édition
                    var tagToChange = document.getElementById("tagToChange");
                    //si on récupère un rfid
                    if (this.responseText.length > 0) {
                        //on enregistre le rfid
                        //console.log(this.responseText);
                        tagToChange.innerHTML = this.responseText;
                        tagToChange.style.backgroundColor = "white";
                        nextTD = nextTDbyRow(tagToChange);
                        tagToChange.id = "";
                        tdInEdition = 0;
                        if (nextTD) {
                            //on passe à l'éditon de la ligne suivante
                            //console.log("edition case suivante");
                            tdclick(nextTD);
                        }
                    //si on ne résupère pas de rfid, le scan s'est mal passé 
                    }else{
                        //on remet tout en mode noral et on quitte le mode scan
                        //console.log("retour ajax vide");
                        tagToChange.style.backgroundColor = "white";
                        tagToChange.id = "";
                        tdInEdition = 0;
                        el.blur(); 
                    }
                //si on est plus dans le mode scan
                }else{
                    //console.log("mode edition quitté, ajax ignoré");
                }
            }
        };
        
        xhttpEdit.open("GET", "ajax_back.php?id_participant=" + encodeURI(id_participant), true);
        xhttpEdit.timeout = 15000;
        xhttpEdit.send(null);
        
    // si on est déja en mode scan 
    } else if(tdInEdition === 1){
        var tagToChange = document.getElementById("tagToChange");
        if(tagToChange){
            if(tagToChange===el){
                //console.log("demande effacement de "+getIdParticipantFromTD(el));
                tagToChange.innerHTML = "";
                setNewId(el);
            }else{
                //console.log("fin edition de "+getIdParticipantFromTD(tagToChange));
            }
            tagToChange.style.backgroundColor = "white";   
            tagToChange.id = "";
        }
        tdInEdition = 0;   
    }
}

function starting(state) {
    e = document.getElementsByName('etat')[0];
    e.value = state;
    //console.log(state);
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

function creer_activite(){
    document.getElementById("show_menu").value="close";
    setTimeout(()=> {
        var evt = document.createEvent("MouseEvents");
        evt.initMouseEvent("click", true, true, window,0, 0, 0, 0, 0, false, false, false, false, 0, null);
        document.getElementById("btn_creer_activite").dispatchEvent(evt);
    },50); 
}

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

function show_dialog_menu() {
    const el_dialog = document.getElementById("dialog_menu");
    const el_value = document.getElementById("show_menu");
    if (el_value.value==="open") {
        //if (el_dialog.open) {
            el_value.value = "close";
            el_dialog.close();
        //}
    }else{
        //if (!el_dialog.open) {
            el_value.value = "open";
            el_dialog.show();
        //}
    }
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

function setVue(vue){
    const el_value = document.getElementById("selVue");
    el_value.value = vue;
    document.forms[0].submit();
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
    document.getElementById("auth").focus();
    
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
    document.getElementById('auth').value = "none";
    document.forms[0].submit();
}

