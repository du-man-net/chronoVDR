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

import * as flags from './constantes.js';
import { activite } from "./activiteClass.js";
import { loadJson, sendJson, formSend } from './ajax.js';
import { tableToAdd } from "./toAddClass.js";
import { co } from "./coClass.js";

export let myToAdd = new tableToAdd();
export let myActivite = new activite;
export let myCo = new co;

class activites {
    constructor() {
        this.organisateur = {};
        //this.users_import = [];
    }
    get url() {
        return '../api/activite.php';
    }
    get url_res() {
        return '../api/reseau.php';
    }

    get url_import() {
        return "../api/import.php";
    }
    get url_time() {
        return "../api/time.php";
    }
    get sel() {
        return document.getElementById("sel_activite");
    }

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async startTime() {
        var sec = 0; var time;
        while (true) {
            if(sec===0){
                let jsonRes = await loadJson(this.url_time);
                time = jsonRes.time.substring(0, 5);
                sec = Number(jsonRes.time.substring(6));
                document.getElementById("htime").textContent = time;
            }else{
                sec++;
                if (sec===60) sec = 0;
            }
            document.getElementById("stime").textContent = (sec < 10 ? '0' : '') + sec;
            await this.sleep(1000);
        }
    }
 
    async load(option = "") {
        let jsonRes = await loadJson(this.url + option);
        let lst = await jsonRes.liste;
        let selected = await jsonRes.selected;
        if (lst) {
            this.clear();
            for await (const a of lst) {
                await myActivite.add(a);
            }
            await myCo.load();
            myActivite.parcours = myCo.parcours;
            await myActivite.select(selected);
            let type = await jsonRes.type;
            await this.show_type(type);
            let classe = await myToAdd.loadClasse();
            await myToAdd.load(classe);
        }
    }

    async create() {
        let option = "?add=1";
        let jsonRes = await loadJson(this.url + option);
        let a = await jsonRes.current;
        myActivite.add(a);
        await myActivite.select(a.id);
        myToAdd.load();
    }

    async remove(callBack) {
        await myActivite.delete_datas();
        let option = "?del=" + myActivite.id;
        let jsonRes = await this.load(option);
        //on met à jour la liste à ajouter
        if (callBack)
            callBack.call();
    }

    get list() {
        return document.getElementById("sel_activite").options;
    }

    clear() {
        document.getElementById("sel_activite").options.length = 0;
    }

    async show_type(type) {
        let sel_activite = document.getElementById("sel_type_activite");
        sel_activite.options.length = 0;
        for (var flag in type) {
            let new_opt = document.createElement('option');
            new_opt.setAttribute('value', flag);
            new_opt.innerHTML = type[flag];
            sel_activite.appendChild(new_opt);
        }
    }

    clearType() {
        let sel_activite = document.getElementById("sel_type_activite");
        sel_activite.options.length = 0;
    }

    async show_reseau() {
        let jsonRes = await loadJson(this.url_res);
        let lst = await jsonRes.interfaces;
        let reseau = document.getElementById("reseau");
        reseau.innerHTML = "";
        lst.forEach((i) => {
            let d = document.createElement('div');
            d.classList.add('card-text');
            d.textContent = i.type + " : " + i.nom;
            reseau.appendChild(d);

            d = document.createElement('div');
            d.textContent = "adresse : " + i.adresse;
            d.classList.add('card-text');
            reseau.appendChild(d);

            d = document.createElement('div');
            d.textContent = "masque : " + i.mask;
            d.classList.add('card-text');
            reseau.appendChild(d);

            d = document.createElement('div');
            reseau.appendChild(d);
        });
    }

    export() {
        let option = document.getElementById('liste_exportation').value;
        let a = document.createElement("a");
        a.href = encodeURI(this.url + "?export=" + option);
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(a.href);
        a.remove();
    }

    async lire_import() {
        let myFile = document.getElementById('formFile');
        let myClasse = document.getElementById('importClasse');

        document.getElementById('formFileStatus').innerHTML = 'Uploading...';


        let formData = new FormData();
        formData.append("importClasse", myClasse.value);
        formData.append("fileImport", myFile.files[0], "import.csv");

        let jsonRes = await formSend(this.url_import, formData);

        this.users_import = [];
        let formFileStatus = document.getElementById('formFileStatus');
        let formFileResult = document.getElementById('formFileResult');
        this.users_import = await jsonRes.users;
        let erreur_message = await jsonRes.erreur_message;
        if (erreur_message) {
            formFileStatus.innerHTML = erreur_message;
        } else {
            let nb_users = 0;
            if (this.users_import)
                nb_users = this.users_import.length;
            formFileStatus.innerHTML = "Lecture du fichier termninée : ";
            formFileStatus.innerHTML += " " + nb_users + " noms trouvés.";
        }
        this.users_import.forEach(e => {
            formFileResult.innerHTML += e.nom + "," + e.prenom + "," + e.classe + "," + e.nais + "," + e.sexe + "<br/>";
        });
        this.togle_btn_import(true);
    }

    async import() {
        await sendJson(this.url_import, this.users_import);
        this.togle_btn_import(false);
        let classe = await myToAdd.loadClasse();
        await myToAdd.load(classe);
        let formFileStatus = document.getElementById('formFileStatus');
        formFileStatus.innerHTML = "Résultats";
    }

    togle_btn_import(etat) {
        let import_visible = etat === true ? "" : "none";
        let lecture_visible = etat === true ? "none" : "";
        document.getElementById('btn_modal_lecture').style.display = lecture_visible;
        document.getElementById('btn_modal_import').style.display = import_visible;
    }

    formatDateTime(dt, showH = true) { // This is to display 12 hour format like you asked
//        const y = dt.getFullYear();
//        const M = dt.getMonth();
//        const d = dt.getDate();
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
}

export { activites };
