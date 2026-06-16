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

import { loadJson, sendJson } from './ajax.js';
class co {
    constructor() {
        this.last_data_index = 0;
        this.parcours = [];
    }
    get url() {
        return '../api/co.php';
    }
    get url_save() {
        return '../api/save_co.php';
    }
    get sel() {
        return document.getElementById("sel_parcours");
    }
    get table() {
        return document.getElementById('recipeTableBody');
    }
    get tableToAdd() {
        return document.getElementById('liste_balises');
    }
    get propsParcours() {
        return document.getElementById("collapseParcours");
    }
    set cur_nom(val) {
        document.getElementById("nom_parcours").value = val;
    }
    set cur_ordre(val) {
        if (val>0) {
            document.getElementById("ordre_parcours").checked = true;
        } else {
            document.getElementById("ordre_parcours").checked = false;
        }
    }

    getParcours(){
        const parcours_id = this.sel.value;
        return this.parcours.find((p) => p.id === parcours_id);
    }
    
    getParcoursIdx(){
        const parcours_id = this.sel.value;
        return this.parcours.findIndex((p) => p.id === parcours_id);
    }
    
    getBaliseIdx(id){
        const balise_id = id.substr(2);
        const parcours = this.getParcours();
        return parcours.balises.findIndex((b) => b.id === balise_id);
    }
    
    async load(id = 0) {
        let option = "?data=parcours&idx=" + this.last_data_index;
        let jsonRes = await loadJson(this.url + option);
        this.last_data_index = await jsonRes.last_index;
        this.parcours.push(...jsonRes.parcours);
        await this.setListParcours(id);
        this.select(id);
    }

    async setListParcours(id) {
        if (this.parcours.length > 0)
            this.sel.options.length = 0;
        for await (const p of this.parcours) {
            let opt = await document.createElement('option');
            await opt.setAttribute('value', p.id);
            opt.textContent = await p.nom;
            if (id)
                if (id == p.id)
                    opt.selected = true;
            await this.sel.appendChild(opt);
        }
    }

    async loadBalises(id) {
        let option = "?data=balises&id=" + id;
        let jsonRes = await loadJson(this.url + option);
        let balises = await jsonRes.balises;
        return balises;
    }

    async setListBalises(list) {
        await this.clearBalises();
        for await (const b of list) {
            let tr = document.createElement('tr');
            tr.id = "b" + b.id;
            tr.append(document.getElementById("tmpl_balise").content.cloneNode(true));
            tr.querySelector(".balise_nom").textContent = b.nom;
            tr.querySelector(".balise_tag").textContent = b.tag;
            this.tableToAdd.appendChild(tr);
        }
    }

    async select(id = false) {
        if (!id) {
            id = this.sel.value;
        }
        const parcours = this.getParcours();
        if (parcours) {
            this.cur_nom = parcours.nom;
            this.cur_ordre = parcours.ordre;
            parcours.balises.sort((a, b) => a.ordre - b.ordre);
            this.clearParcours();
            for (const b of parcours.balises) {
                let tr = document.createElement('tr');
                tr.id = "pb" + b.id;
                tr.append(document.getElementById("tmpl_parcours").content.cloneNode(true));
                if (b.nom) {
                    tr.querySelector(".pb_nom").value = b.nom;
                } else {
                    tr.querySelector(".pb_nom").placeholder = b.nombalise;
                }
                tr.querySelector(".drag-handler").textContent = b.ordre;
                tr.querySelector(".pb_tag").textContent = b.tag;
                tr.querySelector(".pb_value").value = b.value;
                this.table.appendChild(tr);
            }
            let list = await this.loadBalises(id);
            this.setListBalises(list);
            return id;
        }
        return false;
    }

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async clearBalises() {
        let r = this.tableToAdd.rows;
        while (this.tableToAdd.rows.length > 0) {
            this.tableToAdd.deleteRow(-1);
        }
    }

    async clearParcours() {
        while (this.table.lastChild) {
            this.table.removeChild(this.table.lastChild);
        }
    }

    async saveBaliseParcours(id) {
        let balise = document.getElementById(id);
        const nom = balise.querySelector("input").value;
        const b = this.getParcours().balises[this.getBaliseIdx(id)];
        b.nom = nom;
        if (nom === ""){
            balise.querySelector(".pb_nom").placeholder = b.nombalise;
        }
        let jsonBalise = {
            id: id.substr(2),
            nom: nom,
            value: balise.querySelector("select").value,
            ordre: balise.querySelector(".drag-handler").textContent
        };
        let request = {
            parcours_id: this.sel.value,
            action: "saveBaliseParcours",
            balise: jsonBalise
        };
        const jsonRes = await sendJson(this.url_save, request);
        if (jsonRes.response === "ok"){
            b.nom = nom;
            b.value = balise.querySelector("select").value;
            b.ordre = balise.querySelector(".drag-handler").textContent;
        }
    }

    async saveParcours() {
        let ordre = document.getElementById('ordre_parcours').checked ? 1 : 0;
        let nom = document.getElementById('nom_parcours').value;
        let request = {
            parcours_id: this.sel.value,
            nom: nom,
            ordre: ordre,
            action: "saveParcours"
        };
        await sendJson(this.url_save, request);
        let parcours = this.getParcours();
        parcours.nom = nom;
        parcours.ordre = ordre;
        this.sel.options[this.sel.selectedIndex].textContent = parcours.nom;
    }

    async delBaliseParcours(id) {
        const balise_id = id.substr(2);
        let request = {
            parcours_id: this.sel.value,
            action: "delBaliseParcours",
            balise_id: balise_id
        };
        const jsonRes = await sendJson(this.url_save, request);
        this.last_data_index = 0;
        this.parcours = [];
        await this.load(this.sel.value);
    }

    async addBaliseParcours(id) {
        const balise_id = id.substr(1);
        let request = {
            parcours_id: this.sel.value,
            action: "addBaliseParcours",
            balise_id: balise_id
        };
        const jsonRes = await sendJson(this.url_save, request);
        this.last_data_index = 0;
        this.parcours = [];
        await this.load(this.sel.value);
    }
    
    async addParcours() {
        let request = {
            action: 'addParcours'
        };
        let jsonRes = await sendJson(this.url_save, request);
        await this.load(jsonRes.id);
    }

    async delParcours() {
        let request = {
            parcours_id: this.sel.value,
            action: 'delParcours'
        };
        await sendJson(this.url_save, request);
        this.last_data_index = 0;
        this.parcours = [];
        await this.load();
    }

}

export { co };