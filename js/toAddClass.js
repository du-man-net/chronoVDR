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

import { loadJson } from './ajax.js';

class tableToAdd {
    constructor() {
        this.toAdd = document.getElementById("toadd").getElementsByTagName('tbody')[0];
        this.url = '../api/participants.php';
        this.sel_classe = document.getElementById("selclasseParticipants");
    }

    set toAddCheked(val) {
        document.getElementById("tout_ptoadd").checked = val;
    }

    async loadClasse() {
        let jsonRes = await loadJson(this.url + '?lst_toadd=');
        let classes = await jsonRes.classes;
        await this.vider();
        this.setClasses(classes);
        return this.sel_classe.options[this.sel_classe.selectedIndex].value;
    }

    async load(classe=false) {
        if(classe){
            let option = '?lst_toadd=' + classe;
            let jsonRes = await loadJson(this.url + option);
            let participants = await jsonRes.participants;
            this.vider();
            if (participants) {
                participants.forEach((p) => this.insertRow(p));
            }
            this.toAddCheked = false;
        }
    }

    async vider() {
        //document.getElementById("selclasseParticipants").inerHTML="";
        while (this.toAdd.rows.length > 1) {
            this.toAdd.rows[this.toAdd.rows.length - 1].remove();
        }
    }

    create_checkBox(id) {
        let input = document.createElement('input');
        input.type = "checkbox";
        input.name = "check_ptoadd";
        input.value = id;
        return input;
    }

    insertRow(p) {
        let row = this.toAdd.insertRow();
        let cell = row.insertCell();
        cell.appendChild(this.create_checkBox(p.id));
        cell = row.insertCell();
        cell.textContent = p.nom;
        cell.classList.add("nom_toadd");
        cell = row.insertCell();
        cell.textContent = p.prenom;
        cell.classList.add("nom_toadd");
        return row;
    }

    setClasses(list) {
        this.sel_classe.options.length = 0;
        if (list) {
            if (list.length) {
                list.forEach(a => {
                    let new_opt = document.createElement('option');
                    new_opt.setAttribute('value', a);
                    new_opt.innerHTML = a;
                    this.sel_classe.appendChild(new_opt);
                });
            }
        }
    }

    toutCocher(val = - 1) {
        let tout_ptoadd = document.getElementById('tout_ptoadd');
        if (val > -1)
            tout_ptoadd.checked = val;
        document.getElementsByName("check_ptoadd").forEach(chk => {
            chk.checked = tout_ptoadd.checked;
        });
    }
}


export { tableToAdd };