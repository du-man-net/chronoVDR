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
import { loadJson, sendJson, formSend } from './ajax.js';
class participants {
    constructor() {
        this.parts = document.getElementById("parts");
        this.thead = this.parts.getElementsByTagName('thead')[0];
        this.tbody = this.parts.getElementsByTagName('tbody')[0];
        this.last_val = 0;
        this.id; //racine du 
        this.edit = false;
        this.flag = 0;
        this.p = 0;
        this.vue = 0;
    }

    async set_vue(c) {
        this.vue = c;

        if (this.vue.span === 1) {
            for (var i = 0; i < this.tbody.rows.length; i++) {
                let p = this.tbody.rows[i];
                if (p.id) {
                    p.cells[0].rowSpan = 2;
                    let newRow = document.createElement('tr');
                    //la super astuce pour créer une ligne sup dans un span
                    let insert = false;
                    if (p.nextSibling) {
                        if (p.nextSibling.id) {
                            insert = true;
                        }
                    } else {
                        insert = true;
                    }
                    if (insert)
                        p.parentNode.insertBefore(newRow, p.nextSibling);
                    p.cells[1].rowSpan = 2;
                    p.cells[2].rowSpan = 2;
                }
            }
        } else {
            for (var i = 0; i < this.tbody.rows.length; i++) {
                let p = this.tbody.rows[i];
                if (p.id) {
                    p.cells[0].rowSpan = 1;
                    let remove = false;
                    if (p.nextSibling) {
                        if (!p.nextSibling.id) {
                            remove = true;
                        }
                    }
                    if (remove)
                        p.nextSibling.remove();
                    p.cells[1].rowSpan = 1;
                    p.cells[2].rowSpan = 1;
                }
            }
        }
    }

    setFlag(flag) {
        this.flag = flag;
    }

    get nom() {
        return this.p.firstchild.textContent;
    }

    get url() {
        return '../api/participants.php';
    }

    get urlRidMaj() {
        return '../api/rid_maj.php';
    }

    get urlCoMaj() {
        return '../api/co.php';
    }

    get urlRidAbort() {
        return '../api/rid_abort.php';
    }

    get participantsToAdd() {
        let pToAdd = [];
        document.getElementsByName("check_ptoadd").forEach(e => {
            if (e.checked === true)
                pToAdd.push(e.value);
        });
        return pToAdd;
    }

    get participantsToDel() {
        let pToDel = [];
        document.getElementsByName("check_participants").forEach(e => {
            if (e.checked === true)
                pToDel.push(e.value);
        });
        return pToDel;
    }

    get equipeToAdd() {
        let eToAdd = [];
        document.getElementsByName("check_participants").forEach(e => {
            if (e.checked === true)
                eToAdd.push(e.value);
        });
        return eToAdd;
    }

    get equipeToDel() {
        let eToDel = [];
        document.getElementsByName("check_associations").forEach(e => {
            if (e.checked === true)
                eToDel.push(e.value);
        });
        return eToDel;
    }

    stop_edit(el) {
        el.id = "";
    }

    async editCoId(el) {
        if (!this.edit) {
            if (el.dataset.etat < 1) {
                this.edit = true;
                this.p = el.parentElement;
                const span = el.querySelector("span");
                if (span)
                    this.last_val = span.textContent;
                else
                    this.last_val = "";
                el.innerHTML = "";
                const sel_idCo = document.getElementById("tmpl_sel_idCo").content.cloneNode(true);
                const idCo_menu = sel_idCo.querySelector("#sel_idCo_menu");

                let item = document.createElement('li');
                item.innerHTML = '<a class="dropdown-item" href="#" data-idp="-1">effacer</a>';
                idCo_menu.append(item);
                item = document.createElement('li');
                item.innerHTML = '<hr class="dropdown-divider">';
                idCo_menu.append(item);

                for (const parcours of this.vue.parcours) {
                    item = document.createElement('li');
                    item.innerHTML = '<a class="dropdown-item" href="#" data-idp="' + parcours.id + '">' + parcours.nom + '</a>';
                    if (parcours.nom === this.last_val) {
                        item.children[0].classList.add('active');
                    }
                    idCo_menu.append(item);
                }
                el.append(sel_idCo);

                const sel_idco_btn = document.getElementById('sel_idCo_btn');
                const dropdown = new bootstrap.Dropdown(sel_idco_btn);
                const sel_idCo_menu = document.getElementById('sel_idCo_menu');

                sel_idCo_menu.addEventListener('click', async (e) => {
                    const item = e.target.closest('.dropdown-item');
                    if (!item)
                        return;
                    e.preventDefault();
                    let td = item.closest('td');
                    let id_co = td.dataset.id_co;
                    let idp = item.dataset.idp;

                    dropdown.toggle();
                    let option = "?id_participant=" + this.p.id;
                    if (id_co)
                        option += "&id_co=" + id_co;
                    if (idp)
                        option += "&idp=" + idp;
                    const jsonRes = await loadJson(this.urlCoMaj + option);

                    var parcours = this.vue.parcours.find(p => p.id === jsonRes.idp);
                    if (parcours)
                        td.innerHTML = '<h6><span class="badge bg-custom2">' + parcours.nom + '</span><h6>';
                    else
                        td.innerHTML = "";
                    td.dataset.id_co = jsonRes.id_co;
                    td.dataset.id_p = jsonRes.idp;
                    td.dataset.etat = 0;
                    
                    this.edit = false;

                }, this);

                sel_idco_btn.addEventListener('hide.bs.dropdown', () => {
                    this.edit = false;
                    el.innerHTML = '<h6><span class="badge bg-custom2">' + this.last_val + '</span><h6>';
                }, this);

                dropdown.toggle();
            }
        }
    }

    async editIdMat(el) {
        if (!this.edit) {
            this.edit = true;
            this.p = el.parentElement;
            this.last_val = el.innerHTML;
            el.innerHTML = "";
            let sel_idmat = document.getElementById("tmpl_sel_idmat").content.cloneNode(true);

            const option = "?idmat_used=" + this.p.id;
            const jsonRes = await loadJson(this.url + option);
            const list_idmat = await jsonRes.idmat;

            let item = document.createElement('div');
            item.innerHTML = '<a class="dropdown-item" href="#" data-value="">effacer</a>';
            sel_idmat.querySelector("#col0").append(item);

            var id = 0;
            for (var j = 0; j < 3; j++) {
                const col = sel_idmat.querySelector("#col" + j);
                for (var i = 1; i < 11; i++) {
                    id++;
                    const idmat = list_idmat.find((p) => p == id);
                    if (!idmat) {
                        let item = document.createElement('div');
                        item.innerHTML = '<a class="dropdown-item" href="#" data-value="' + id + '">' + id + '</a>';
                        col.append(item);
                    }
                }
            }
            el.append(sel_idmat);

            const sel_idmat_btn = document.getElementById('sel_idmat_btn');
            const dropdown = new bootstrap.Dropdown(sel_idmat_btn);
            const sel_idmat_menu = document.getElementById('sel_idmat_menu');

            sel_idmat_menu.addEventListener('click', async (e) => {
                const item = e.target.closest('.dropdown-item');
                if (!item)
                    return;
                e.preventDefault();
                let val = item.dataset.value;

                dropdown.toggle();
                let option = "?id_participant=" + this.p.id + "&id_mat=" + val;
                await loadJson(this.urlRidMaj + option);
                el.innerHTML = '<h6><span class="badge bg-custom">' + val + '</span></h6>';
                this.edit = false;
            }, this);

            sel_idmat_btn.addEventListener('hide.bs.dropdown', () => {
                this.edit = false;
                el.innerHTML = this.last_val;
            }, this);

            dropdown.toggle();
        }
    }

    async editRid(el) {
        el = el.closest("td");
        let jsonRes = "";
        if (!this.edit) {
            this.edit = true;
            this.p = el.parentElement;
            this.last_val = el.innerHTML;
            el.innerHTML = "";
            el.append(document.getElementById("tmpl_spinner").content.cloneNode(true));
            let option = "?id_participant=" + this.p.id + "&rid=toscan";
            jsonRes = await loadJson(this.urlRidMaj + option);
        } else {
            if (this.p === el.closest('tr')) {
                let option = "?action=";
                jsonRes = await loadJson(this.urlRidAbort + option);
                option = "?id_participant=" + this.p.id + "&id_mat=delete";
                jsonRes = await loadJson(this.urlRidMaj + option);
            } else {
                let option = "?action=cancel";
                jsonRes = await loadJson(this.urlRidAbort + option);
            }
        }
        this.setNewRid(jsonRes);
    }

    setNewRid(val) {
        let cell = this.p.cells[2];
        if (val.action == "set") {
            cell.innerHTML = '<h6><span class="badge bg-custom">' + val.newid + '</span></h6>';
        } else if (val.action == "delete") {
            cell.innerHTML = "";
            this.last_val = "";
        } else if (val.action == "cancel") {
            cell.innerHTML = this.last_val;
            cell.blur();
        }
        this.edit = false;
        if (val.action == "set") {
            this.edit_next_rid();
        }
    }

    edit_next_rid() {
        if (this.p) {
            let nextP = this.p.nextSibling;
            if (nextP) {
                this.editRid(nextP.cells[2]);
            }
        }
    }

    async load(option = '') {
        let jsonRes = await loadJson(this.url + option);
        let participants = await jsonRes.participants;
        this.clear();
        //console.log("debut d'ajout des participants");
        if (participants) {
            for (const participant of participants) {
                await this.add(participant);
            }
    }
    //console.log("fin d'ajout des participants");
    }

    check(flag) {
        return(this.flag & flag) === flag;
    }

    async add(p) {
        this.id = p.id;
        this.p = this.tbody.insertRow();
        this.p.id = this.id;
        let cell = this.p.insertCell();
        cell.innerHTML = p.nom;
        cell.classList.add("nom");
        cell = this.p.insertCell();
        cell.appendChild(this.create_checkBox(p.assoc));
        cell.classList.add("e");

        cell = this.p.insertCell();
        cell.innerHTML = '<h6><span class="badge bg-custom">' + p.ref_id + '</span><h6>';
        //cell.textContent = p.ref_id;

        if (this.check(flags.SHOW_PARCOURS)) {
            cell.classList.add("idmat");
        } else if (this.check(flags.BY_IDMAT)) {
            cell.classList.add("idmat");
        } else if (this.check(flags.BY_RFID)) {
            cell.classList.add("rid");
        }
    }

    clear() {
        if (this.vue)
            this.vue.clear();
        while (this.tbody.lastChild) {
            this.tbody.removeChild(this.tbody.lastChild);
        }
        //console.log("fin nettoyage");
    }

    create_checkBox(assoc) {
        let input = document.createElement('input');
        input.type = "checkbox";
        if (assoc === 1) {
            input.name = "check_associations";
        } else {
            input.name = "check_participants";
        }
        input.value = this.id;
        return input;
    }

    async addSelected() {
        let participantsToAdd = this.participantsToAdd;
        if (participantsToAdd) {
            let option = '?ptoadd=' + participantsToAdd.join(',');
            await this.load(option);
            await this.vue.load();
        }
    }

    async deleteSelected() {
        let participantsToDel = this.participantsToDel;
        if (participantsToDel) {
            let option = '?ptodel=' + participantsToDel.join(',');
            await this.load(option);
            await this.vue.load();
        }
    }

    async addDeleteEquipe() {

        let option = '';
        let equipeToAdd = this.equipeToAdd;
        if (equipeToAdd.length > 0)
            option = 'etoadd=' + equipeToAdd.join(',');
        let equipeToDel = this.equipeToDel;
        if (equipeToDel.length > 0) {
            if (option.length)
                option += "&";
            option += 'etodel=' + equipeToDel.join(',');
        }
        if (option.length)
            option = "?" + option;
        await this.load(option);
    }

}


export { participants };