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


import { loadJson, sendJson, formSend } from './ajax.js';
class admins {
    constructor() {
        this.list = 0;
        this.isNew = false;
    }
    get form_pw() {
        return document.getElementById("admin_pw");
    }
    get sel() {
        return document.getElementById("sel_admin");
    }
    get url() {
        return '../api/admin.php';
    }
    get selId() {
        return this.sel.options[this.sel.selectedIndex].value;
    }

    findAdmin(id) {
        return this.list.find((a) => a.id === id);
    }

    async load(option = "") {
        let jsonRes = await loadJson(this.url + option);
        this.list = await jsonRes.liste;
        this.show_list();
        this.select(this.sel.value);
    }

    select(id, force = false) {
        if (force) {
            let list = this.sel.options;
            for (var i = 0; i < list.length; i++) {
                if (list[i].value === id) {
                    list[i].selected = "selected";
                }
            }
        }
        let admin = this.findAdmin(id);
    }

    show_list() {
        this.clear();
        //console.log("debut d'ajout des participants");
        if (this.list) {
            for (const admin of this.list) {
                let opt = document.createElement('option');
                opt.setAttribute('value', admin.id);
                opt.textContent = admin.nom;
                this.sel.appendChild(opt);
            }
        }
    }

    clear() {
        this.sel.options.length = 0;
    }

}



export { admins };