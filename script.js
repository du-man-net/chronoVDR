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

import { Chart, registerables } from "chart.js"
import 'chartjs-adapter-luxon';
Chart.register(...registerables);

const BY_RFID = 0x1;
const BY_IDMAT = 0x2;

const SHOW_TIME = 0x4;
const SHOW_DATA = 0x8;
const SHOW_START = 0x10;

const TIME_SINCE_START = 0x20;
const HOUR_PER_LAP = 0x40;
const TIME_PER_LAP = 0x80;
const DATA_PER_TEST = 0x100;

const IS_LIMIT_TIME = 0x200;
const IS_LIMIT_LAP = 0x400;
const IS_LIMIT = 0x800;

const SHOW_FINAL_TIME = 0x1000;
const SHOW_NUMBER_LAPS = 0x2000;
const SHOW_TOTAL_DATA = 0x4000;

const SHOW_PARCOURS = 0x8000;

var tabIdx = new Array();
var flag = 0;
var myChart = {};

var tdInEdition = 0;

var xhttpEdit;

var last_log = 0;
var last_logs_index = 0;

var id_activite = 0;
var vue = "";
var etat = 0;

var lst_parcours = [];
var last_index_parcours = 0;

var lst_cos = [];
var curent_co = [];
var last_index = 0;

var ajaxEdit;
var myInterval = 0;

/******************************************************
 etat de l'activité    0= non séléctionnée 
 1= sélectionnée 
 2= sélectionnée et enregistrement actif
 
 Si l'enregistrement est actif, alord on surveille les modifs 
 dans la base de donnée toutes les secondes pour affichage rapide
 
 * Gestion dimensionnement en hauteur de l'iframe
 * Mise à jour des données de l'iFrame
 * Mise à jour des données de la console de log
 *******************************************************/

document.addEventListener("DOMContentLoaded", () => {

    //on récupère la vue en cours à partir d'un input hidden
    vue = document.getElementById("Vue").value;
    //on récupère l'état de l'activité (0 inactif, 1 log, 2 enregistrement)
    etat = document.getElementsByName('etat')[0].value;

    setInterval(function () {
        const date = new Date();
        document.getElementById('dt-sys').innerHTML = formatDateTime(date);
    }, 1000);

    getData();

    if (vue !== "co") {
        onClickEvent("vue", setVue);
    }
    onClickEvent("stopstart", starting);
    onClickEvent("tag_scan_rfid", do_scan_balise);

    onClickEvent("pwd_off", password_disconnect);
    onClickEvent("pwd_on", show_dialog_password);
    onClickEvent("log", show_dialog_logs);
    onClickEvent("refresh", refresh);
    onClickEvent("imgmenu", show_dialog_menu);
    onClickEvent("dial_users", show_dialog_users);
    onClickEvent("dial_activite", creer_activite);
    onClickEvent("dial_prop", show_dialog_activite);
    onClickEvent("dial_parcours", show_dialog_parcours);
    onClickEvent("dial_export", show_dialog_exportation);
    onClickEvent("dial_nett", show_dialog_nettoyage);
    onClickEvent("dial_participants", show_dialog_participants);

    onClickEvent("cancel_activite", cancel_dialog_activite);
    onClickEvent("cancel_parcours", cancel_dialog_parcours);
    onClickEvent("cancel_participants", cancel_dialog_participants);
    onClickEvent("cancel_exportation", cancel_dialog_exportation);
    onClickEvent("cancel_nettoyage", cancel_dialog_nettoyage);
    onClickEvent("cancel_password", cancel_dialog_password);
    onClickEvent("cancel_users", cancel_dialog_users);

    onChangeEvent("flag", save_type_activite);
    onChangeEvent("selparcours", save_type_activite);
    onChangeEvent("selListeBalise", addBaliseFromList);
    onChangeEvent("chk_ordre", setOrdre);
    onChangeEvent("toutcocher", toutcocher);

    document.addEventListener('change', function (event) {
        if (event.target.matches(".sel_ordre")) {
            setOrdreBalise(event.target);
        }
    });

    document.addEventListener('click', function (event) {
        if (event.target.matches(".idmat")) {
            selec_id_mat(event.target);
        } else if (event.target.matches(".tag")) {
            tdclick(event.target);
        } else if (event.target.matches(".idparcours")) {
            selec_id_parcours(event.target);
        } else if (event.target.matches(".co_left")) {
            changeParcours(event.target);
        } else if (event.target.matches(".co_right")) {
            changeParcours(event.target);
        } else if (event.target.matches(".tagToChange")) {

        }
    });

    //validation du mot de passe par la touche entrée
    document.getElementById('auth').addEventListener("keydown", (event) => {
        touche(event.key);
    });
});

function getData() {
    //console.log(vue);
    if (vue === "co") {
        ajaxGet("vues/_co.php?data=parcours&idx=" + last_index_parcours).then(reponse => {
            getParcours(reponse);
            Ajax("vues/_co.php?data=curent", putParcoursToTab);
            Ajax("vues/_co.php?data=datas&idx=" + last_index, showClosedParcours);
            myInterval = setInterval(function () {
                ajaxGet("vues/_co.php?data=parcours&idx=" + last_index_parcours).then(reponse => {
                    getParcours(reponse);
                    Ajax("vues/_co.php?data=curent", putParcoursToTab);
                    Ajax("vues/_co.php?data=datas&idx=" + last_index, showClosedParcours);
                });
            }, 2000);
        });

    } else if (vue === "tableau") {
        ajaxGet("vues/_tableau.php?idx=" + last_index).then(reponse => {
            putAjaxDatasToTab(reponse);
            myInterval = setInterval(function () {
                if (etat == 2) {
                    Ajax("vues/_tableau.php?idx=" + last_index, putAjaxDatasToTab);
                } else if (etat > 0) {
                    Ajax("last_logs.php?idx=" + last_logs_index, showLog);
                }
            }, 500);
        });
    } else if (vue === "graphique") {
        prepareGraph();
        ajaxGet("vues/_graphique.php?idx=" + last_index).then(reponse => {
            putAjaxDatasToGraph(reponse);
            myInterval = setInterval(function () {
                if (etat == 2) {
                    Ajax("vues/_graphique.php?idx=" + last_index, putAjaxDatasToGraph);
                } else if (etat > 0) {
                    Ajax("last_logs.php?idx=" + last_logs_index, showLog);
                }
            }, 500);
        });
    }
}

function onClickEvent(id, callBack) {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener("click", (event) => {
            const valeur = event.currentTarget.dataset.val;
            if (valeur) {
                if (valeur === "forbidden") {
                    //do nothing
                } else if (valeur === "show_dialog_menu") {
                    show_dialog_menu();
                    callBack(valeur);
                } else if (valeur === "return") {
                    event.preventDefault();
                    callBack(valeur);
                } else {
                    callBack(valeur);
                }
            } else {
                callBack();
            }
        });
    }
}

function onChangeEvent(id, callBack) {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener("change", (event) => {
            const valeur = event.currentTarget.dataset.val;
            if (valeur) {
                callBack(valeur);
            } else {
                callBack();
            }
        });
    }
}

function clearDatas() {
    tabIdx = new Array();
    last_index = 0;
    flag = 0;
}
/****************************************
 * fonction générique AJAX
 ***************************************/
function Ajax(url, cFunction) {
    var xhttp;
    xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            if (xhttp.responseText.length > 0) {
                if (cFunction) {
                    cFunction(this);
                }
            }
        }
    };
    xhttp.open("GET", encodeURI(url), true);
    xhttp.send(null);
    return xhttpEdit;
}

/****************************************
 * fonction générique AJAX ave promesse
 ***************************************/
function ajaxGet(url) {
    return new Promise(function (resolve, reject) {
        // Nous allons gérer la promesse
        let xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            // Si le traitement est terminé
            if (xmlhttp.readyState === 4) {
                if (xmlhttp.status === 200) {
                    resolve(xmlhttp);
                } else {
                    reject(xmlhttp);
                }
            }
        };

        xmlhttp.open('GET', encodeURI(url), true);
        xmlhttp.send(null);
    });
}

/****************************************
 * Visibilité de la page de logs
 ***************************************/
function logIsShow() {
    const el_dialog = document.getElementById("logs");
    if (el_dialog.style.display) {
        if (el_dialog.style.display === "none") {
            return false;
        }
    }
    return true;
}

/****************************************
 * Function callBack actualisation des logs
 ***************************************/
function showLog(xhttp) {
    if (logIsShow()) {
        const datas = JSON.parse(xhttp.responseText);

        const last_logs = datas['logs'];
        if (last_logs.length > 0) {

            const txt_logs = document.getElementById('logs');

            for (const last_log of last_logs) {
                const newDiv = document.createElement("div");
                const newContent = document.createTextNode(last_log);
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

/****************************************
 // retrouve le ID du participant dans la ligne
 ****************************************/
function getIdParticipantFromTD(el) {
    return el.parentElement.id;
    ;
}

/****************************************
 // retrouve le td de la ligne suivante
 ****************************************/
function nextTDbyRow(el) {
    var tr = el.parentElement;
    let next_tr = tr.parentNode.rows[ tr.rowIndex + 1 ];
    if (next_tr) {
        let next_td = next_tr.children[2];
        return next_td;
    }
    return false;
}

/****************************************
 // efface la couleur de toutes les case tag editable
 ****************************************/
function resetTdColor() {
    var el = document.getElementsByClassName("tag");
    for (var h = 0; h < el.length; h++) {
        el[h].style.backgroundColor = 'white';
    }
}

/****************************************
 A modifier en profondeur
 Pour inclure la suppression d'un id de matériel portant le même nom dans la liste
 Et prise en chage de la fonction Ajax
 ****************************************/

/****************************************
 //choisir l'id de matériel 
 ****************************************/
function selec_id_mat(even) {
    event.stopPropagation();
    if (!even.id) {
        even.id = "idmat";
        const val = even.innerHTML;
        even.innerHTML = "";
        const select = even.append(NewSelectIdMat(val));
    }
}

/****************************************
 // création d'une box pour choisir l'id de matériel 
 ****************************************/
function NewSelectIdMat(val) {
    var select = document.createElement('select');
    var new_opt = document.createElement('option');
    new_opt.setAttribute('value', -1);
    new_opt.innerHTML = "Effacer";
    select.appendChild(new_opt);
    for (var i = 1; i < 31; i++) {
        var new_opt = document.createElement('option');
        if (i == val) {
            new_opt.setAttribute('selected', 'selected');
        }
        new_opt.setAttribute('value', i);
        new_opt.innerHTML = i;
        select.appendChild(new_opt);
    }
    //add the event we need
    select.onchange = function () {
        var td_idmat = select.parentElement;
        var id_participant = getIdParticipantFromTD(td_idmat);
        Ajax("ajax/tagMaj.php?id_participant=" + encodeURI(id_participant) + "&ref_id=" + encodeURI(select.value), setNewIdmat);
    };
    return select;
}

/****************************************
 //function de callBack de l'édition d'un idmat de participant
 ****************************************/
function setNewIdmat(xhttp) {
    var td_idmat = document.getElementById("idmat");
    if (td_idmat) {
        //on split la réponse pur récupérer les modif à effectuer
        const xhttp_tab = xhttp.responseText.split(",");
        //pour tous les id de fin de liste
        for (var i = 1; i < xhttp_tab.length; i++) {
            //on retrouve la ligne correspondant à l'id de chaque participant
            const tr_participant = document.getElementById(xhttp_tab[i]);
            //on retrouve la cellule correspoandante
            if (tr_participant) {
                const td_participant_idmat = tr_participant.cells[2];
                //on vire la référence matériel
                if (td_participant_idmat) {
                    td_participant_idmat.innerHTML = "";
                }
            }
        }
        //pour le premier élément de la liste, on met la référence en place
        if (xhttp_tab[0] == -1) {
            td_idmat.innerHTML = "";
        } else {
            td_idmat.innerHTML = xhttp_tab[0];
        }
        td_idmat.id = "";

    }
}

/****************************************
 /Selection du parcours (à vireer dans le code de l'Iframe
 ****************************************/
function selec_parcours(even) {
    event.stopPropagation();
    if (even.id != "seledit") {
        even.id = "seledit";
        val = even.innerHTML;
        even.innerHTML = "";
        select = document.getElementById("selParcoursParticipant");
        even.append(select);
        //add the event we need
        select.onchange = function () {
            text = select.options[select.selectedIndex].text;
            val = select.value;
            var td_parcours = select.parentElement;
            div = document.getElementById("divParcoursParticipant");
            div.append(select);
            td_parcours.innerHTML = text;
            td_parcours.id = val;
        };
    }
}

/****************************************
 //fonction AJAX pour l'edition des tag participants
 ****************************************/
function AjaxTag(url, cFunction) {
    xhttpEdit = new XMLHttpRequest();
    xhttpEdit.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            cFunction(this);
        }
    };
    xhttpEdit.open("GET", encodeURI(url), true);
    //xhttpEdit.timeout = 15000;
    xhttpEdit.send(null);
    return xhttpEdit;
}

/****************************************
 //edition d'un tag dans la liste des particiannts
 ****************************************/
function tdclick(el) {
    //si pas d'édition de TGA en cours, on lance l'édition
    if (xhttpEdit) {
        xhttpEdit.abort();
        //console.log("xhttpEdit aborted");
    }
    var id_participant = getIdParticipantFromTD(el);

    if (tdInEdition === 0) {
        tdInEdition = 1;
        //on supprime les résidus de couleur jaune
        resetTdColor();
        //on marque le td en cours et on passe la cellule en jaune
        el.id = "tagToChange";
        el.style.backgroundColor = "yellow";
        //on récupère l'id du participant sur la même ligne
        ajaxEdit = AjaxTag("ajax/tagToChange.php?id_participant=" + encodeURI(id_participant), majTagParticipant);

        // si on est déja en mode scan 
    } else if (tdInEdition === 1) {
        var tagToChange = document.getElementById("tagToChange");
        if (tagToChange) {
            if (tagToChange === el) {
                //ajaxEdit.abort();
                Ajax("ajax/tagMaj.php?id_participant=" + encodeURI(id_participant) + "&ref_id=-1", deleteId);
                tagToChange.innerHTML = "suppr...";
                tagToChange.style.backgroundColor = "red";
            } else {
                ajaxEdit.abort();
                abortId();
            }
        }
        tdInEdition = 0;
    }
}

/****************************************
 //function de callBack de l'édition d'un tag de participant
 ****************************************/
function deleteId() {
    var tagToChange = document.getElementById("tagToChange");
    if (tagToChange) {
        tagToChange.style.backgroundColor = "white";
        tagToChange.innerHTML = "";
        tagToChange.blur();
        tagToChange.id = "";
    }
}

/****************************************
 //function de callBack de l'édition d'un tag de participant
 ****************************************/
function abortId() {
    var tagToChange = document.getElementById("tagToChange");
    if (tagToChange) {
        tagToChange.style.backgroundColor = "white";
        tagToChange.blur();
        tagToChange.id = "";
    }
}

/****************************************
 //function de callBack de l'édition d'un tag de participant
 ****************************************/
function majTagParticipant(xhttp) {

    //si on est dans le mode scan
    if (tdInEdition === 1) {
        //console.log("mode edition");

        //on retroue le case du RFID en cours d'édition
        var tagToChange = document.getElementById("tagToChange");
        //si on récupère un rfid
        if (xhttp.responseText.length > 0) {
            //on enregistre le rfid
            //console.log(xhttp.responseText);
            tagToChange.innerHTML = xhttp.responseText;
            tagToChange.style.backgroundColor = "white";
            let nextTD = nextTDbyRow(tagToChange);
            tagToChange.id = "";
            tdInEdition = 0;
            if (nextTD) {
                //on passe à l'éditon de la ligne suivante
                //console.log("edition case suivante");
                tdclick(nextTD);
            }
            //si on ne résupère pas de rfid, le scan s'est mal passé 
        } else {
            //on remet tout en mode noral et on quitte le mode scan
            //console.log("retour ajax vide");
            tagToChange.style.backgroundColor = "white";
            tagToChange.id = "";
            tdInEdition = 0;
            tagToChange.blur();
        }
    }
}

/****************************************
 //Edition d'un tag lors de l'ajout d'une balise de CO
 ****************************************/
function do_scan_balise() {

    if (tdInEdition === 0) {
        tdInEdition = 1;
        //on marque le td en cours et on passe la cellule en jaune
        const textToChange = document.getElementById("tag_scan_rfid");
        textToChange.style.backgroundColor = "yellow";
        AjaxTag("ajax/scan_balise.php?scan_balise=1", majTagBalise);
    }
}

/****************************************
 //function de callBack de l'édition d'un tag lors de l'ajout d'une balise de CO
 ****************************************/
function majTagBalise(xhttp) {
    //si on est dans le mode scan
    if (tdInEdition === 1) {
        //console.log("mode edition");

        //on retroue le case du RFID en cours d'édition
        const textToChange = document.getElementById("tag_scan_rfid");
        //si on récupère un rfid
        if (xhttp.responseText.length > 0) {
            //on enregistre le rfid
            textToChange.value = xhttp.responseText;
            textToChange.style.backgroundColor = "white";
            textToChange.blur();
            //si on ne résupère pas de rfid, le scan s'est mal passé 
        } else {
            //on remet tout en mode noral et on quitte le mode scan
            //console.log("retour ajax vide");
            textToChange.style.backgroundColor = "white";
            textToChange.blur();
        }
        tdInEdition = 0;
    }
}


function starting(state) {
    const e = document.getElementsByName('etat')[0];
    e.value = state;
    //console.log(state);
    document.forms[0].submit();
}

function touche(el) {
    if (el === 'Enter') {
        document.forms[0].submit();
    }
}

function refresh() {
    window.location.reload();
}

function toutcocher() {
    const el = document.getElementById('toutcocher')
    const chks = document.getElementsByClassName("check_users");
    for (var i = 0; i < chks.length; i++) {
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

function save_type_activite() {
    const save_activite = document.getElementById('change_activite');
    if (save_activite) {
        save_activite.value = "ok";
    }
    document.forms[0].submit();
}

function creer_activite() {
    document.getElementById("show_menu").value = "close";
    setTimeout(() => {
        var evt = document.createEvent("MouseEvents");
        evt.initMouseEvent("click", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
        document.getElementById("btn_creer_activite").dispatchEvent(evt);
    }, 50);
}

function show_dialog_logs() {
    const el_dialog = document.getElementById("logs");
    const el_value = document.getElementById("show_logs");
    if (el_dialog.style) {
        if (el_dialog.style.display) {
            if (el_dialog.style.display === "none") {
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
    if (el_value.value === "open") {
        //if (el_dialog.open) {
        el_value.value = "close";
        el_dialog.close();
        //}
    } else {
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
            el_dialog.left = "800px";
            el_dialog.top = "10px";
            el_dialog.show();
        }
    } else {
        if (el_dialog.open) {
            el_value.value = state;
            el_dialog.close();
        }
    }
}

function setVue(vue) {
    //console.log(vue);
    if (vue !== "co") {
        const el_value = document.getElementById("selVue");
        el_value.value = vue;
        document.forms[0].submit();
    }
}

function show_dialog_activite() {
    dialog("propriete_activite", "show_activite", "open");
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

function show_dialog_parcours() {
    dialog("add_parcours", "show_parcours", "open");
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
    dialog("propriete_activite", "show_activite", "close");
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

function cancel_dialog_parcours() {
    dialog("add_parcours", "show_parcours", "close");
    return false;
}

function password_disconnect() {
    document.getElementById('auth').value = "none";
    document.forms[0].submit();
}



function addBaliseFromList() {
    const elid = document.getElementById('id_parcours_balise');
    const selval = document.getElementById('selListeBalise');
    if (selval.value) {
        elid.value = selval.value;
    }
}

function setOrdreBalise(el) {
    document.querySelectorAll('.sel_ordre').forEach(select => {
        if (select !== el) {
            if (select.value === el.value) {
                select.selectedIndex = "0";
            }
        }
    });
}

function setOrdre() {
    const elid = document.getElementById('parcours_ordre_value');
    const chkOrdre = document.getElementById('chk_ordre');
    if (chkOrdre.checked) {
        elid.value = 2;
    } else {
        elid.value = 1;
    }
    document.forms[0].submit();
}

//****************************************************************
// Injection des données
//****************************************************************

function RemoveResults() {
    const table = document.getElementById("parts");
    for (const row of table.rows) {
        if (!row.id & row.rowIndex > 0) {
            row.remove();
        }
    }
    for (const row of table.rows) {
        if (row.rowIndex > 0) {
            row.cells[0].rowSpan = 1;
            row.cells[1].rowSpan = 1;
            row.cells[2].rowSpan = 1;
            for (var i = row.cells.length - 1; i > 2; i--) {
                row.cells[i].remove();
            }
        } else {
            for (var i = row.cells.length - 2; i > 2; i--) {
                row.cells[i].remove();
            }
        }

    }
}

//****************************************************************
//****************************************************************
//****************************************************************
// injection des données graphiques
//****************************************************************
//****************************************************************
//****************************************************************
export function putAjaxDatasToGraph(xhttp) {

    const datas = JSON.parse(xhttp.responseText);

    //on récupère les drapeaux de l'activité si ils sont transmis (une seule fois)
    if (datas['flag']) {
        flag = datas['flag'];
    }

    //on récupère le dernier index pour minimiser les reqêtes suivantes
    last_index = datas['last_index'];
    const users_datas = datas['datas'];

    for (var id in users_datas) {
        const udatas = users_datas[id];

        // Create an array of ISO strings

        udatas.forEach(function (item) {
            let dt = new Date(item['x']);
            let labels = myChart[id].data.labels;
            labels.push(dt.toISOString());

            if (check(TIME_PER_LAP)) {

                if (labels.length > 1) {
                    const datebefore = labels[labels.length - 2];
                    const diff = (Date.parse(dt) - Date.parse(datebefore)) / 1000;
                    myChart[id].data.datasets[0].data.push(diff);
                } else {
                    myChart[id].data.datasets[0].data.push(0);
                }

            } else {
                myChart[id].data.datasets[0].data.push(item['y']);
            }

        });
        myChart[id].update();
    }
}

//****************************************************************
// préoparaion à l'injection des données graphiques
//****************************************************************
export function prepareGraph() {
    var id;
    const lines = document.querySelectorAll(".line");
    for (const line of lines) {
        var cell = line.insertCell(3);
        var canv = document.createElement('canvas');
        canv.classList.add('canvas');
        canv.style.height = "150px";
        canv.style.width = "100%";
        canv.style.maxWidth = "1200px";
        cell.appendChild(canv); // adds the canvas to #someBox
        var title = line.children[0].innerHTML;
        var id = line.id;

        myChart[id] = new Chart(canv, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                        pointRadius: 1,
                        pointBackgroundColor: "rgba(0,0,255,1)",
                        borderWidth: 1,
                        data: [],
                        fill: false
                    }]
            },
            options: {
                scales: {
                    x: {
                        //distribution: 'linear',
                        type: 'time',
                        time: {
                            unit: 'minute'
                        },
                        ticks: {
                            source: 'data'
                        },
                        adapters: {
                            date: {
                                zone: 'Europe/Paris'
                            }
                        }
                    }
                },
                layout: {
                    padding: 0
                },
                plugins: {
                    title: {
                        display: false//,
//                        text: title,
//                        position: 'top',
//                        align: 'center'
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
}

//****************************************************************
//****************************************************************
//****************************************************************
// Injection des données tableau
//****************************************************************
//****************************************************************
//****************************************************************
function putAjaxDatasToTab(xhttp) {

    const users_datas = JSON.parse(xhttp.responseText);

    if (id_activite != users_datas['id_activite']) {
        id_activite = users_datas['id_activite'];
        clearDatas();
    }
    //on récupère les drapeaux de l'activité si ils sont transmis (une seule fois)
    if (users_datas['flag']) {
        flag = users_datas['flag'];
    }
    //on récupère le dernier index pour minimiser les requêtes suivantes
    if (users_datas['last_index'] > last_index) {
        last_index = users_datas['last_index'];
        //console.log(users_datas);
    }

    let coll_resultats = check(SHOW_FINAL_TIME) || check(SHOW_NUMBER_LAPS) || check(SHOW_TOTAL_DATA);
    if (coll_resultats) {
        addColResultats();
    }
    let coll_span = check(SHOW_TIME) & check(SHOW_DATA);
    if (coll_span) {
        addRowSpan();
    }

    for (const users_data of users_datas['datas']) {
        addDataToLine(users_data['id'], users_data['temps'], users_data['data'], coll_resultats);
    }
}

//****************************************************************
// Ajout des données dans une ligne participants
//****************************************************************
function addDataToLine(id, dt, value, coll_resultats) {
    if (!tabIdx[id]) {
        tabIdx[id] = 2;
    }
    tabIdx[id]++;
    //on essaye de récupérer la ligne de temps 
    if (check(SHOW_TIME)) {

        var row = document.getElementById(id);

        if (row) {
            //on essaye de récupérer la cellule 
            let cell = row.cells[tabIdx[id]];

            if (!cell || cell.id) {
                //on créer une ccolonne suplémentaire
                addCellToAllLine(coll_resultats);
                cell = row.cells[tabIdx[id]];
            }
            //on formate les données
            //dans la première colomne
            if (tabIdx[id] === 3) {
                //on affiche la date telle quelle
                cell.title = dt;
                dt = new Date(dt);
                cell.innerHTML = formatDateTime(dt);

                //dans les autres, ça dépend de l'activité
            } else {
                if (check(HOUR_PER_LAP)) {
                    cell.title = dt;
                    dt = new Date(dt);
                    cell.innerHTML = formatDateTime(dt);

                } else if (check(TIME_PER_LAP)) {
                    cell.title = dt;
                    dt = Date.parse(dt);
                    const cellbefore = row.cells[tabIdx[id] - 1];
                    const datebefore = Date.parse(cellbefore.title);
                    const diff = (dt - datebefore) / 1000;
                    cell.innerHTML = formatTime(diff);

                } else if (check(TIME_SINCE_START)) {
                    cell.title = dt;
                    dt1 = Date.parse(dt);
                    const firstcell = row.cells[1];
                    const firstdate = Date.parse(firstcell.title);
                    const diff = (dt - firstdate) / 1000;
                    cell.innerHTML = formatTime(diff);
                }
            }

            if (check(SHOW_FINAL_TIME)) {
                let lastcell = row.cells.length - 1;
                cell = row.cells[lastcell];
                const firstcell = row.cells[3];
                const firstdate = Date.parse(firstcell.title);
                const diff = (dt - firstdate) / 1000;
                cell.innerHTML = formatTime(diff);

            } else if (check(SHOW_NUMBER_LAPS)) {
                let lastcell = row.cells.length - 1;
                cell = row.cells[lastcell];
                cell.innerHTML = tabIdx[id] - 3;
            }
        }
    }
    if (check(SHOW_DATA)) {
        row = row.parentNode.rows[ row.rowIndex + 1 ];
        if (row) {
            //on essaye de récupérer la cellule
            var cell = row.cells[tabIdx[id] - 3];

            //on vérifie si la  cellule est dans la colonne total
            if (cell) {
                if (!cell.classList.contains('total')) {
                    cell.innerHTML = value;
                }
            }
            if (check(SHOW_TOTAL_DATA)) {
                let lastcell = row.cells.length - 1;
                cell = row.cells[lastcell];
                var total = 0;
                for (var i = 0; i < row.cells.length - 1; i++) {
                    let numb = row.cells[i].innerText;
                    //on filtre les espaces insécable (cellules vides)
                    if (numb.charCodeAt(0) !== 160) {
                        total += parseFloat(numb);
                    }
                }
                cell.innerHTML = total;
            }
        }
    }

}

//****************************************************************
// validation des drapeaux
//****************************************************************
function check(thisflag) {
    if ((flag & thisflag) === thisflag) {
        return true;
    }
    return false;
}

//****************************************************************
// Calcul de l'entête des colonnes
//****************************************************************
function get_start_prefix() {
    if (flag & DATA_PER_TEST) {
        return "data";
    }
    if (flag & TIME_PER_LAP) {
        return "temps";
    }
    if (flag & TIME_SINCE_START) {
        return "temps";
    }
    if (flag & HOUR_PER_LAP) {
        return "heure";
    }
}

//****************************************************************
// Calcul de l'entête de la dernirèe colonne
//****************************************************************
function get_end_prefix() {
    if (flag & SHOW_FINAL_TIME) {
        return "Temps";
    }
    if (flag & SHOW_NUMBER_LAPS) {
        return "Nb tours";
    }
    if (flag & SHOW_TOTAL_DATA) {
        return "Total";
    }
    return "unknow";
}

//****************************************************************
// Ajout des ligne supplementaires de data si besoin
//****************************************************************
function addRowSpan() {
    const lines = document.querySelectorAll(".line");
    for (const line of lines) {
        line.cells[0].rowSpan = 2;
        line.cells[1].rowSpan = 2;
        line.cells[2].rowSpan = 2;
        let line2 = line.nextSibling;
        if (!line2.id) {
            var newrow = document.createElement('tr');
            line.parentNode.insertBefore(newrow, line.nextSibling);
        }
    }
}

//****************************************************************
// Ajout d'une supplementaires de résultats si besoin
//****************************************************************
function addColResultats() {
    const colRes = document.getElementById("colRes");
    if (!colRes) {
        const table = document.getElementById("parts");
        var numb, row, cell, c_th;
        row = table.rows[0];
        numb = row.cells.length - 1;
        cell = row.insertCell(numb);
        c_th = document.createElement('th');
        cell.replaceWith(c_th);
        c_th.classList.add('title');
        c_th.id = "colRes";
        c_th.innerHTML = get_end_prefix();

        const tableLenght = table.rows.length;

        for (let i = 1; i < tableLenght; i++) {
            let row = table.rows[i];
            if (row.id) {
                cell = row.insertCell(numb);
            } else {
                cell = row.insertCell(numb - 3);
            }
            cell.id = "colRes" + i;
            cell.classList.add('total');

        }
    }
}

//****************************************************************
// Ajout de colonnes supplementaires au fur et a mesure de l'affichage des données 
//****************************************************************
function addCellToAllLine(coll_resultats) {
    //on regarde si il faut la colonne total
    const table = document.getElementById("parts");
    var numb, row, cell, c_th;

    row = table.rows[0];
    //on ajoute un élément de la ligne d'entête
    numb = row.cells.length - 1;
    if (coll_resultats) {
        numb--;
    }

    cell = row.insertCell(numb);
    c_th = document.createElement('th');
    cell.replaceWith(c_th);
    c_th.classList.add('title');
    if (numb == 3) {
        if (flag & SHOW_START) {
            c_th.innerHTML = "Départ";
        } else {
            c_th.innerHTML = get_start_prefix() + (numb - 2);
        }
    } else {
        c_th.innerHTML = get_start_prefix() + (numb - 2);
    }

    for (let i = 1; i < table.rows.length; i++) {
        let row = table.rows[i];
        if (row.cells.length >= numb) {
            cell = row.insertCell(numb);
        } else {
            cell = row.insertCell(numb - 3);
        }
        cell.classList.add('data');
        cell.innerHTML = "&nbsp;";
    }
}

//****************************************************************
//****************************************************************
//****************************************************************
// Injection des donnée de parcours de course d'orientation
//****************************************************************
//****************************************************************
//****************************************************************
function putParcoursToTab(xhttp) {
    const datas = JSON.parse(xhttp.responseText);
    const parcours = datas['parcours'];
    const participants = document.getElementById("parts").rows;
    for (var i = 1; i < participants.length; i++) {
        var id_participant = participants[i].id;
        if (id_participant) {
            //on ne rafraichi pas une case en cours d'édition
            if (participants[i].cells[3].style.backgroundColor !== "lightgray") {
                var myparcours = parcours[id_participant];
                if (myparcours) {
                    participants[i].cells[3].innerHTML = myparcours['nom'];
                    participants[i].cells[3].dataset.id_co = myparcours['id_co'];
                    if (myparcours['etat'] == 1) {
                        participants[i].cells[3].style.backgroundColor = "yellow";
                    } else if (myparcours['etat'] == 0) {
                        participants[i].cells[3].style.backgroundColor = "white";
                    }
                } else {
                    participants[i].cells[3].innerHTML = "";
                    participants[i].cells[3].dataset.id_co = "";
                    participants[i].cells[3].style.backgroundColor = "white";
                }
            }
        }
    }
}

function getParcours(xhttp) {
    const datas = JSON.parse(xhttp.responseText);
    if (datas['last_index'] > last_index_parcours) {
        last_index_parcours = datas['last_index'];
        lst_parcours = datas['parcours'];
    }
}

/****************************************
 //choisir l'id de matériel 
 ****************************************/
function selec_id_parcours(even) {
    event.stopPropagation();
    if (even.style.backgroundColor !== "yellow") {
        const nomParcours = even.innerHTML;
        even.innerHTML = "";
        var id_co;
        if (even.dataset.id_co) {
            id_co = even.dataset.id_co;
        }
        even.style.backgroundColor = "lightgray";
        const select = even.append(NewSelectIdp(nomParcours, id_co));
    }
}

function NewSelectIdp(nomParcours, id_co) {
    var select = document.createElement('select');
    var new_opt = document.createElement('option');
    new_opt.setAttribute('value', -1);
    new_opt.innerHTML = "Effacer";
    select.appendChild(new_opt);
    for (var i = 0; i < lst_parcours.length; i++) {
        const parcours = lst_parcours[i];
        var new_opt = document.createElement('option');
        if (parcours['nom'] === nomParcours) {
            new_opt.setAttribute('selected', 'selected');
        }
        new_opt.setAttribute('value', parcours['id']);
        new_opt.innerHTML = parcours['nom'];
        select.appendChild(new_opt);
    }
    //add the event we need
    select.onchange = function () {
        var td_idp = select.parentElement;
        var id_participant = getIdParticipantFromTD(td_idp);
        var url = "vues/_co.php?id_participant=" + encodeURI(id_participant) + "&idp=" + encodeURI(select.value);
        if (id_co) {
            url += "&id_co=" + encodeURI(id_co);
        }
        Ajax(url, setNewParcours);
    };
    return select;
}

/****************************************
 //function de callBack de l'édition d'un idmat de participant
 ****************************************/
function setNewParcours(xhttp) {
    const xhttp_tab = xhttp.responseText.split(",");
    const id_participant = xhttp_tab[0].trim();
    const id_co = xhttp_tab[1];
    const idp = xhttp_tab[2];
    var tr_participant = document.getElementById(id_participant);
    if (tr_participant) {
        const td_participant_id_co = tr_participant.cells[3];
        td_participant_id_co.dataset.id_co = id_co;
        if (idp > 0) {
            for (var i = 0; i < lst_parcours.length; i++) {
                const parcours = lst_parcours[i];
                if (parcours['id'] == idp) {
                    td_participant_id_co.innerHTML = parcours['nom'];
                    td_participant_id_co.style.backgroundColor = "white";
                    return;
                }
            }
        } else {
            td_participant_id_co.innerHTML = "";
            td_participant_id_co.style.backgroundColor = "white";
        }
    }
}

/****************************************
 //function d'affichage des parcours réalisés
 ****************************************/
function showClosedParcours(xhttp) {
    var refresh = false;
    var datas = JSON.parse(xhttp.responseText);
    if (datas.last_index > last_index) {
        last_index = datas.last_index;
        refresh = true;
    }
    if (refresh) {
        if (lst_cos.length === 0) {
            lst_cos = datas.datas;
        } else {
            const last_cos = datas.datas;
            for (var i = 0; i < last_cos.length; i++) {
                const id_co = lst_cos.findIndex(p => p.id_participant === last_cos[i].id_participant);
                if (id_co > -1) {
                    lst_cos[id_co].lstco = lst_cos[id_co].lstco.concat(last_cos[i].lstco);
                    curent_co[lst_cos[id_co].id_participant] = lst_cos[id_co].lstco.length - 1;
                } else {
                    lst_cos.push(last_cos[i]);
                }
            }
        }
        for (var i = 0; i < lst_cos.length; i++) {
            let cos = lst_cos[i];
            let co_idx = curent_co[cos.id_participant];
            if (!co_idx) {
                co_idx = cos.lstco.length - 1;
                curent_co[cos.id_participant] = co_idx;
            }
            showOneParcours(cos.id_participant, cos.lstco[co_idx], (co_idx + 1) + "/" + cos.lstco.length);
        }
    }
}

/****************************************
 //function de changement de parcours avec flèches
 ****************************************/
function changeParcours(el) {
    let id_participant = el.parentNode.parentNode.id;
    let id_co = lst_cos.findIndex(p => p.id_participant == id_participant);
    if (id_co > -1) {
        let cos = lst_cos[id_co];
        let co_idx = curent_co[id_participant];
        if (el.innerText === '<') {
            if (co_idx < cos.lstco.length - 1) {
                co_idx = co_idx + 1;
            }
        } else if (el.innerText === '>') {
            if (co_idx > 0) {
                co_idx = co_idx - 1;
            }
        }
        showOneParcours(id_participant, cos.lstco[co_idx], (co_idx + 1) + "/" + cos.lstco.length);
        curent_co[id_participant] = co_idx;
    }
}

/****************************************
 //function d'affichage de un parcours
 ****************************************/
function showOneParcours(idp, co, co_n) {
    const co_n_participant = document.getElementById('co_n' + idp);
    co_n_participant.innerHTML = co_n;
    const co_participant = document.getElementById('co' + idp);
    let old_table = co_participant.children[0];
    if (old_table) {
        co_participant.removeChild(old_table);
    }
    const tbl = document.createElement('table');
    tbl.classList.add('co');
    const tr1 = tbl.insertRow();
    const tr2 = tbl.insertRow();
    co_participant.appendChild(tbl);

    const lst_trouvees = co.balises;

    var parcours = lst_parcours.find(b => b.id === co.id_parcours);
    if (parcours) {
        let t_start = new Date(co.t_start);
        var heure;
        var pts = 0;

        let td1 = tr1.insertCell();
        td1.classList.add('name');
        td1.innerHTML = parcours.nom;
        let td2 = tr2.insertCell();
        td2.classList.add('start');
        td2.innerHTML = formatDateTime(t_start);

        //on parcours les balises dans le sens des balises trouvées par le coureur
        for (var k = 0; k < lst_trouvees.length; k++) {
            const fb = foundBalise(lst_trouvees[k], parcours, k);

            td1 = tr1.insertCell();
            td1.innerHTML = fb.nom;
            if (fb.succeed === 0) {
                td1.classList.add('false');
            } else if (fb.succeed === 1) {
                td1.classList.add('mid');
            } else if (fb.succeed === 2) {
                td1.classList.add('good');
            }
            pts = pts + parseInt(fb.value);
            let h = new Date(lst_trouvees[k].temps);
            heure = formatTime((h - t_start) / 1000);
            td2 = tr2.insertCell();
            td2.innerHTML = heure;
        }
        //on cherche les balises oubliées
        for (var k = 0; k < parcours.balises.length; k++) {
            const fb = lst_trouvees.find(b => b.tag === parcours.balises[k].tag);
            if (!fb) {
                td1 = tr1.insertCell();
                td1.innerHTML = parcours.balises[k].nom;
                td1.classList.add('forget');
                td2 = tr2.insertCell();
                pts = pts - 2;
            }
        }
        td1 = tr1.insertCell();
        td1.classList.add('pts');
        td1.innerHTML = pts + " pts";
        let h = new Date(co.t_end);
        heure = formatTime((h - t_start) / 1000);
        td2 = tr2.insertCell();
        td2.classList.add('tps');
        td2.innerHTML = heure;

    }
}

function foundBalise(trouvee, parcours, idx) {
    res = [];
    if (parcours.balises[0].ordre > 0) {
        const balise = parcours.balises[idx];
        if (balise) {
            if (trouvee.tag === balise.tag) {
                var res = [];
                res.nom = balise.nom;
                res.value = balise.value;
                res.temps = trouvee.temps;
                res.succeed = 2;
            }
        }
        if (!res.succeed) {
            var found = parcours.balises.find(b => b.tag === trouvee.tag);
            if (found) {
                res.nom = found.nom;
                res.value = -1;
                res.temps = trouvee.temps;
                res.succeed = 1;
            } else {
                res.nom = trouvee.tag;
                res.value = -2;
                res.temps = trouvee.temps;
                res.succeed = 0;
            }
        }
    } else {
        var found = parcours.balises.find(b => b.tag === trouvee.tag);
        if (found) {
            res.nom = found.nom;
            res.value = found.value;
            res.temps = trouvee.temps;
            res.succeed = 2;
        } else {
            res.nom = trouvee.tag;
            res.value = -2;
            res.temps = trouvee.temps;
            res.succeed = 0;
        }
    }
    return res;
}

function formatDateTime(dt, showH = true) { // This is to display 12 hour format like you asked
    const y = dt.getFullYear();
    const M = dt.getMonth();
    const d = dt.getDate();
    var h = dt.getHours();
    var m = dt.getMinutes();
    var s = dt.getSeconds();
    h = h < 10 ? '0' + h : h;
    m = m < 10 ? '0' + m : m;
    s = s < 10 ? '0' + s : s;
    if (showH) {
        var strTime = h + ':' + m + ':' + s;
    } else {
        var strTime = m + ':' + s;
    }
    //var strTime = d + "/" + M + "/" + y + " " + h + ':' + m + ':' + s;
    return strTime;
}

function formatTime(time) { // This is to display 12 hour format like you asked
    var d = Math.floor(time / 86400);
    var h = Math.floor((time % 86400) / 3600);
    var m = Math.floor((time % 3600) / 60);
    var s = Math.round(time % 60);
    h = h < 10 ? '0' + h : h;
    m = m < 10 ? '0' + m : m;
    s = s < 10 ? '0' + s : s;
    if (d > 0) {
        return d + "j " + h + ":" + m + ":" + s;
    } else if (h > 0) {
        return h + ":" + m + ":" + s;
    } else {
        return m + ":" + s;
    }
}