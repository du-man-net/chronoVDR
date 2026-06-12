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
        this.admin = {};
    }

    get form_nom() {
        return document.getElementById("admin_nom");
    }
    get form_login() {
        return document.getElementById("admin_login");
    }
    get form_pw() {
        return document.getElementById("admin_pw");
    }
    get form_save() {
        return document.getElementById("admin_save");
    }
    get form_del() {
        return document.getElementById("admin_del");
    }
    get form_add() {
        return document.getElementById("admin_add");
    }
    get sel() {
        return document.getElementById("sel_admin");
    }
    get url() {
        return '../api/admin.php';
    }
    get url_logout() {
        return '../api/logout.php';
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
        this.form_nom.value = admin.nom;
        this.form_login.value = admin.login;
        this.show_admin();
    }

    async add() {
        const option = "?add=1";
        const jsonRes = await loadJson(this.url + option);
        this.list = await jsonRes.liste;
        this.show_list();
        this.select(this.sel.options[this.sel.length - 1].value, true);
    }

    async save() {
        let admin = this.findAdmin(this.selId);
        console.log(admin);

        var option = "?save=" + this.selId;

        if (this.form_login.value !== admin.login)
            option += "&login=" + this.form_login.value;

        if (this.form_nom.value !== admin.nom) {
            option += "&nom=" + this.form_nom.value;
            this.sel.options[this.sel.selectedIndex].textContent = this.form_nom.value;
        }

        if (this.form_pw.value.length > 0)
            option += "&pw=" + encodeURIComponent(this.form_pw.value);

        const jsonRes = await loadJson(this.url + option);
        console.log(jsonRes);
    }

    async del() {
        const option = "?del=" + this.selId;
        const jsonRes = await loadJson(this.url + option);
        this.list = await jsonRes.liste;
        this.show_list();
        this.select(this.sel.options[this.sel.length - 1].value, true);
    }

    show_list() {
        this.clear();
        if (this.list) {
            for (const admin of this.list) {
                if (this.admin.id == 1 || this.admin.id == admin.id) {
                    let opt = document.createElement('option');
                    opt.setAttribute('value', admin.id);
                    opt.textContent = admin.nom;
                    this.sel.appendChild(opt);
                }
            }
        }
    }

    show_admin() {
        const isAdminAccount = this.selId == 1;
        const isAdmin = this.admin.id == 1;
        const isMyAccount = this.admin.id === this.selId;
        this.form_save.disabled = !isAdmin && !isMyAccount;
        this.form_del.disabled = !isAdmin || isMyAccount;
        this.form_add.disabled = !isAdmin;
        this.form_nom.disabled = isAdminAccount; 
        this.form_login.disabled = isAdminAccount;
    }

    async logout() {
        const jsonRes = await loadJson(this.url_logout);
        document.location.href = "../index.html";
    }

    clear() {
        this.sel.options.length = 0;
    }

}



export { admins };