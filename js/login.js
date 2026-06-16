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


import { admins } from "./indexClass.js";
import { login, isAuth } from './ajax.js';

let myAdmins = new admins();

isAuth().then((a) => {
    if (a.logged) {
        document.location.href = "master.html";
        myActivites.organisateur = a.login;
    }
});

document.addEventListener('DOMContentLoaded', () => {
    
    for(var i = 1; i < 31 ; i++){
        const option  = document.createElement('option');
        option.value = i;
        option.textContent = i;
        document.getElementById('sel_idmat').appendChild(option);
    }
    document.getElementById('adminForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const username = myAdmins.findAdmin(myAdmins.sel.value).login;
        const password = myAdmins.form_pw.value;
        const data = await login(username, password);
        document.getElementById('result').innerText = data.message;
        if (data.success) {
            document.location.href = "master.html";
        }

    });

    document.getElementById('btn_login_eleve').addEventListener('click',e =>{
        e.preventDefault();
        document.location.href = "eleve.html?idmat=" + document.getElementById('sel_idmat').value;
    });
    


    myAdmins.load();

});