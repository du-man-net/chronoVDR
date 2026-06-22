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
 * GNU General Public License for loadClassemore details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
import { participants } from "./participantsClass.js";
import * as flags from './constantes.js';
import { loadJson, sendJson } from './ajax.js';
import { tableP } from "./tableClass.js";
import { chartsP } from "./graphClass.js";
import { coTableP } from "./coTableClass.js";
export let myParticipants = new participants();
class activite {
    constructor(a) {
        this.id = 0;
        this.vue = 0;
        this.flag = 0;
        this.running = false;
        this.admin = {};
        this._parcours = {};
    }

    get nom() {
        return this.sel.options[this.sel.selectedIndex].textContent;
    }

    get url() {
        return '../api/activite.php';
    }

    get url_save() {
        return "../api/save_activite.php";
    }

    get url_logs() {
        return "../api/last_logs.php";
    }
    
    get sel() {
        return document.getElementById("sel_activite");
    }

    check(flag) {
        return(this.flag & flag) === flag;
    }

    set parcours(parcours) {
        this._parcours = parcours;
    }
 
    async add(a) {
        let opt = await document.createElement('option');
        await opt.setAttribute('value', a.id);
        opt.textContent = await a.nom;
        if (this.admin.id > 1)
            if (a.id_admin !== this.admin.id)
                opt.disabled = true;
        await this.sel.appendChild(opt);
    }

    showState(etat) {
        //console.log(etat);
        const btn = document.getElementById('startStop').children[0];
        if (etat) {
            btn.classList.remove("text-light");
            btn.classList.add("text-danger");
            this.sel.disabled = true;
            document.getElementById("btn_off_menu").disabled = true;
            this.running = true;
        } else {
            btn.classList.remove("text-danger");
            btn.classList.add("text-light");
            this.sel.disabled = false;
            document.getElementById("btn_off_menu").disabled = false;
            this.running = false;
        }
    }

    showRefId() {
        const ref_id = document.getElementById('ref_id');
        if (this.check(flags.SHOW_PARCOURS)) {
            ref_id.textContent = "Id mat.";
            ref_id.classList.remove("rid_titre");
            ref_id.classList.add("idmat_titre");
        } else if (this.check(flags.BY_IDMAT)) {
            ref_id.textContent = "Id mat.";
            ref_id.classList.remove("rid_titre");
            ref_id.classList.add("idmat_titre");
        } else if (this.check(flags.BY_RFID)) {
            ref_id.textContent = "Tag RFID.";
            ref_id.classList.remove("idmat_titre");
            ref_id.classList.add("rid_titre");
        }
    }

    async select(id, change = false) {
        //console.log(id);
        let option;
        this.id = id;
        if (change) {
            let option = "?set_id=" + id;
            await loadJson(this.url + option);
        }
        option = "?get=1";
        let jsonRes = await loadJson(this.url + option);
        let a = await jsonRes.current;
        let list = this.sel.options;
        for (var i = 0; i < list.length; i++) {
            if (list[i].value === this.id.toString()) {
                list[i].selected = "selected";
            }
        }

        this.flag = a.flag;
        this.showRefId();
        this.showState(a.etat == 2);
        await this.show_infos(a);
        await this.show_options();
        //console.log(this.flag);
        myParticipants.setFlag(this.flag);
        await myParticipants.load();
        await this.setVue(a.vue);
        //console.log("before load_datas");
        await this.load_datas();
    }

    async load_participant() {
        await myParticipants.load();
    }

    async setVue(newVue = 'tableau') {
        let first = false;
        let change = false;
        let myVue;
        //console.log("old vue = " + this.vue);
        //console.log("new vue = " + newVue);

        if (!this.cVue) {
            first = true;
            this.vue = newVue;
        } else if (newVue !== this.vue) {
            change = true;
            this.vue = newVue;
        }

        if (first || change) {
            //console.log("vue change");
            const btn = document.getElementById('change_vue').children[0];
            if (this.vue === 'tableau') {
                myVue = new tableP();
                //console.log("set vue tableau");
                btn.classList.remove("bi-table");
                btn.classList.add("bi-graph-up");
            } else if (this.vue === 'graphique') {
                myVue = new chartsP();
                //console.log("set vue graphique");
                btn.classList.add("bi-table");
                btn.classList.remove("bi-graph-up");
            } else if (this.vue === 'co') {
                myVue = new coTableP();
                //console.log("set vue graphique");
                //btn.classList.add("bi-table");
                //btn.classList.remove("bi-graph-up");
            }
            this.cVue = myVue;
            let option = "?set_vue=" + this.vue;
            await loadJson(this.url + option);
        }
        this.cVue.setFlag(this.flag);
        if (this.cVue.parcours) {
            //console.log("attribution de la liste des parcours");
            this.cVue.parcours = this._parcours;
        }
        await myParticipants.set_vue(this.cVue);
        await this.cVue.clear();
    }

    //****************************************************************
    // prise en charge du démarrage de 
    // la mise a jour des données toutes les xxx secondee
    //****************************************************************

    async startStop() {
        if (this.running) {
            //console.log("stop running");
            this.running = false;
        } else {
            //console.log("start running");
            this.run();
        }
        let option = "?startStop=" + (this.running ? 1 : 0);
        await loadJson(this.url + option);
        return this.running;
    }

    async run() {
        if (this.running)
            return;
        this.running = true;
        while (this.running) {
            await this.load_datas();
            var delay;
            if (this.vue === 'tableau') {
                delay = 500;
            } else if (this.vue === 'graphique') {
                delay = 500;
            } else if (this.vue === 'co') {
                delay = 2000;
            }
            await this.sleep(delay);
        }
    }
     
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

//****************************************************************
    // petit bout de magie :
    // cVue correspond à la vue choisie
    // la maj des données est faite par la même proc
    // qualque soit la vue choisie
//****************************************************************
    async load_datas()
    {
        if (this.cVue) {
            this.cVue.load();
        } else {
            console.log("cVue is nothisng");
        }
    }

    async delete_datas() {
        if (this.cVue)
            await this.cVue.clear();
        let option = "?del_datas=1";
        await loadJson(this.url + option);
    }

    async save() {
        this.flag = document.getElementById('sel_type_activite').value;
        let a = {
            flag: this.flag,
            nom: document.getElementById('nom_activite').value,
//            organisateur: document.getElementById('nom_orga').value,
//            nb_max:document.getElementById('nb_max').value,
//            temps_max:document.getElementById('temps_max').value,
            delais_min: document.getElementById('delais_min').value
        };
        //on change le nom dans le selecteur
        this.sel.options[this.sel.selectedIndex].textContent = a.nom;
        await sendJson(this.url_save, a);
        await this.select(this.sel.value, false);
    }
    
    set_type() {
        document.getElementById("sel_type_activite").options.foreach(a => {
            a.selected = (this.flag === a.value) ? 'selected' : "";
        }, this);
    }

    show_infos(a) {
        //console.log(a);
        document.getElementById('sel_type_activite').value = a.flag;
        document.getElementById('nom_activite').value = a.nom;
        document.getElementById('nom_orga').value = a.nom_admin;
//        document.getElementById('nb_max').value = this.a.nb_max;
//        document.getElementById('temps_max').value = this.a.temps_max;
        document.getElementById('delais_min').value = a.delais_min;
    }

    show_options() {

//        document.getElementById('temps_max').disabled = this.check(flags.IS_LIMIT_TIME);
//        document.getElementById('temps_max_lbl').disabled = this.check(flags.IS_LIMIT_TIME);
//
//        document.getElementById('nb_max').disabled = this.check(flags.IS_LIMIT_LAP);
//        document.getElementById('nb_max_lbl').disabled = this.check(flags.IS_LIMIT_LAP);

        let el = {};
        document.getElementById('sel_id_type').childNodes.forEach(function (element) {
            element.selected = element.value & this.flag;
        }, this);
        let list_flag = ["flag_show_data", "flag_show_time", "flag_show_startTime", "flag_final_time", "flag_number_laps", "flag_total_datas"];
        list_flag.forEach(function (check_flag) {
            el = document.getElementById(check_flag);
            el.checked = this.flag & el.value ? true : false;
            el.disabled = true;
        }, this);
        el = document.getElementById('how_show_time');
        el.disabled = true;
        el.childNodes.forEach(function (element) {
            element.selected = element.value & this.flag;
        }, this);
    }

}

export { activite };