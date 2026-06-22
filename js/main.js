/* 
 * Copyright (C) 2026 gleon
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

import { isAuth, login } from './ajax.js';
import { admins } from "./adminsClass.js";
import { tableToAdd } from "./toAddClass.js";
import { participants } from "./participantsClass.js";
import { activites, myActivite, myToAdd, myCo } from "./activitesClass.js";
import { myParticipants } from "./activiteClass.js";
import { get_modal_alert, get_modal_del_ativite, get_modal_del_datas, showOffcanvasProps, showOffcanvasMenu, showOffcanvasToAdd } from './ui.js';
import { Sortable } from "sortablejs";

let myAdmins = new admins();
let myActivites = new activites();
let selClasse = document.getElementById("selclasseParticipants");

document.addEventListener('DOMContentLoaded', () => {


//****************************************
//Vérification de l'anuthentification auprès du serveur
//****************************************
    isAuth().then((a) => {
        if (!a.logged) {
            document.location.href = "index.html";
        } else {
            myActivite.admin = a;
            myAdmins.admin = a;
            document.getElementById('menu_a_name').textContent = a.nom;
            document.getElementById('admin_a_name').textContent = a.nom;
        }
    });

    //****************************************
    //Activité
    //selection
    //****************************************
    document.getElementById('sel_activite').addEventListener('change', (el) => {
        //un peu de magie
        //passage d'une fonction callback pour faire la mis a jour
        //des élèves à ajouter
        myActivite.select(el.target.value, true, loadToAdd);

    });

    //****************************************
    //Activité
    //Page de propriété de l'activité
    //****************************************
    document.getElementById('save_props_activite').addEventListener('click', () => {
        myActivite.save();
        showOffcanvasProps(false);
    });

    document.getElementById('sel_type_activite').addEventListener('change', (el) => {
        myActivite.flag = el.target.value;
        myActivite.show_options();
    });


    //****************************************
    //Activité
    //Ajout / suppression
    //****************************************
    document.getElementById('add_activite').addEventListener('click', () => {
        showOffcanvasMenu(false);
        myActivites.create();
        showOffcanvasProps(true);
    });

    document.getElementById('del_activite').addEventListener('click', ()=> {
        let title = "Supprimer l'activité";
        let message = "Vous allez supprimer l'activité ***" + myActivite.nom + "*** et toutes les données !";
        document.getElementById("btn_modal_del_ativite").addEventListener("click", ()=> {
            myActivites.remove(loadToAdd);
            mymodal.hide();
        });
        showOffcanvasMenu(false);
        let mymodal = get_modal_del_ativite(title, message);
        mymodal.show();
    });

    //****************************************
    //Activité
    //Export
    //****************************************
    document.getElementById('export_activite').addEventListener('click', () => {
        showOffcanvasMenu(false);
    });

    document.getElementById('btn_modal_export').addEventListener('click', () => {
        myActivites.export();
        let modal_export = bootstrap.Modal.getInstance(document.getElementById('modal_export'));
        modal_export.hide();
    });

    document.getElementById('import_activite').addEventListener('click', () => {
        showOffcanvasMenu(false);
        myActivites.togle_btn_import(false);
    });

    //myParticipants.togle_btn_import(false);
    document.getElementById("form_import").addEventListener('submit', async () => {
        event.preventDefault();
        if (document.getElementById('btn_modal_import').style.display === "none") {
            myActivites.lire_import();
        } else {
            myActivites.import();
            const modal_import = bootstrap.Modal.getInstance(document.getElementById('modal_import'));
            modal_import.hide();
        }
    });

    //****************************************
    //Activité
    //Suppression des données
    //****************************************
    document.getElementById('del_datas').addEventListener('click', () =>{
        let title = "Supprimer ldes données";
        let message = "Vous allez supprimer toute les données de l'activité ***" + myActivite.nom + "*** !";
        document.getElementById("btn_modal_del_datas").addEventListener("click", ()=> {
            myActivite.delete_datas();
            mymodal.hide();
        });
        showOffcanvasMenu(false);
        let mymodal = get_modal_del_datas(title, message);
        mymodal.show();
    });

    //****************************************
    //Equipe
    //Ajout supression
    //****************************************
    document.getElementById('create_equipe').addEventListener('click', () => {
        myParticipants.addDeleteEquipe();
    });

    //****************************************
    //Participants
    //ajout supression
    //****************************************
    document.getElementById('ptoadd').addEventListener('click', () => {
        showOffcanvasToAdd(false);
        myParticipants.addSelected()
                .then(() => myToAdd.load(selClasse.value)
                );
    });

    document.getElementById('ptodel').addEventListener('click', () => {
        myParticipants.deleteSelected()
                .then(() => myToAdd.load(selClasse.value)
                );
    });

    //décoche la case de selection quand le canvas est montré
    document.getElementById('off_ptoadd').addEventListener('show.bs.offcanvas', (el) => {
        //console.log("off_ptoadd show");
        myToAdd.load(selClasse.value).then(() => myToAdd.toutCocher(false));
    });
    document.getElementById('off_ptoadd').addEventListener('hide.bs.offcanvas', (el) => {
        //console.log("off_ptoadd hide");
    });

    //****************************************
    //Participants
    //choix de classe / selection de participants
    //****************************************
    selClasse.addEventListener('change', (el) => {
        myToAdd.load(el.target.value);
    });

    document.getElementById('tout_ptoadd').addEventListener('change', () => {
        myToAdd.toutCocher();
    });

    document.getElementById("parts").addEventListener('click', async (event) => {
        if (event.target.closest(".idco")) {
            event.stopPropagation();
            await myParticipants.editCoId(event.target.closest(".idco"));

        } else if (event.target.closest(".idmat")) {
            event.stopPropagation();
            await myParticipants.editIdMat(event.target.closest(".idmat"));

        } else if (event.target.closest(".rid")) {
            myParticipants.editRid(event.target.closest(".rid"));

        } else if (event.target.matches(".e")) {
            let chk = event.target.children[0];
            chk.checked = !(chk.checked);
        }
    });

    //callback pour la mise à jour des élèves à ajouter
    function loadToAdd() {//callback
        myToAdd.load(selClasse.options[selClasse.selectedIndex].value);
    }

    //****************************************
    //Fonctions
    //Démarrage Arret de l'activité
    //****************************************

    document.getElementById('startStop').addEventListener('click', async () => {
        let etat = await myActivite.startStop();
        myActivite.showState(etat);
    });

    document.getElementById('change_vue').addEventListener('click', () => {
        if (myActivite.vue === 'tableau') {
            myActivite.setVue("graphique");
            myActivite.load_datas();
        } else if (myActivite.vue === 'graphique') {
            myActivite.setVue("tableau");
            myActivite.load_datas();
        }
    });

    document.getElementById('show_logs').addEventListener('click', async () => {
        myActivites.show_logs();
    });
    

    //****************************************
    //Choix de parcours
    //Pour la course d'orientation
    //****************************************
    document.getElementById('sel_parcours').addEventListener('change', (el) => {
        myCo.select(el.target.value);
    });

    document.getElementById('add_parcours').addEventListener('click', (el) => {
        myCo.addParcours();
    });

    document.getElementById('del_parcours').addEventListener('click', (el) => {
        myCo.delParcours();
    });

    document.getElementById('ordre_parcours').addEventListener('change', (el) => {
        myCo.saveParcours();
    });

    document.getElementById('nom_parcours').addEventListener('change', (el) => {
        myCo.saveParcours();
    });

    document.addEventListener('change', function (event) {
        if (event.target.matches(".pb_nom")) {
            let id = event.target.closest("tr").id;
            myCo.saveBaliseParcours(id);
        } else if (event.target.matches(".pb_value")) {
            let id = event.target.closest("tr").id;
            myCo.saveBaliseParcours(id);
        }
    });
 
    document.addEventListener('click', function (event) {
        if (event.target.matches(".pb_del")) {
            let id = event.target.closest("tr").id;
            myCo.delBaliseParcours(id);
        } else if (event.target.matches(".pb_add")) {
            let id = event.target.closest("tr").id;
            myCo.addBaliseParcours(id);
        } else if (event.target.closest(".co_left")) {
            let id = event.target.closest("tr").id;
            myActivite.cVue.upParcours(id);
        } else if (event.target.closest(".co_right")) {
            let id = event.target.closest("tr").id;
            myActivite.cVue.downParcours(id);
        }
    });

    //****************************************
    //Pilotage de parcours les usager
    //****************************************



    //****************************************
    //gestion des évennement du panneau admin
    //****************************************

    document.getElementById('admin_add').addEventListener('click', (el) => {
        myAdmins.add();
    });

    document.getElementById('admin_save').addEventListener('click', (el) => {
        myAdmins.save();
    });

    document.getElementById('admin_del').addEventListener('click', (el) => {
        myAdmins.del();
    });

    document.getElementById('sel_admin').addEventListener('change', (el) => {
        myAdmins.select(el.target.value);
    });
    document.getElementById('power_off').addEventListener('click', (el) => {
        myAdmins.logout();
    });

    //****************************************
    //liste classable pour 
    //les balises de la course d'orientation 
    //****************************************

    var tableBody = document.getElementById('recipeTableBody');

//    document.querySelector('.recipe-table__add-row-btn').addEventListener('click', (e) => {
//        console.log(e);
//        var el = e.currentTarget;
//
//        var htmlString = document.getElementById('rowTemplate').content.cloneNode(true);
//        tableBody.append(htmlString);
//        return false;
//    });

//    tableBody.querySelector('.btn-close').addEventListener('click', (e) => {
//        console.log(e);
//        var el = e.currentTarget;
//        var row = el.closest('tr');
//        row.remove();
//        return false;
//    });

    new Sortable(
            tableBody,
            {
                animation: 150,
                scroll: true,
                handle: '.drag-handler',
                onEnd: function () {
                    for (var i = 0; i < tableBody.rows.length; i++) {
                        let span = tableBody.rows[i].querySelector(".drag-handler");
                        span.textContent = i + 1;
                        let id = tableBody.rows[i].id;
                        myCo.saveBaliseParcours(id);
                    }
                }
            }
    );

    myActivites.load();
    myActivites.show_reseau();
    myActivites.startTime();
    myAdmins.load();


});









