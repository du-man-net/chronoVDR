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
import { loadJson } from './ajax.js';

class coTableP {
    constructor() {
        this.parts = document.getElementById("parts");
        this.thead = this.parts.getElementsByTagName('thead')[0];
        this.tbody = this.parts.getElementsByTagName('tbody')[0];
        this.p = 0;
        this.last_data_index = 0;
        this.flag = 0;
        this.liste_co = [];
        this.parcours = [];
        this.toAdd = {};
    }

    get url() {
        return "../api/co.php";
    }
    get lastIndex() {
        return this.last_data_index;
    }
    setFlag(flag) {
        this.flag = flag;
    }

    async load() {
        this.insertCells();
        this.show_legend();

//      Première partie :
        await this.load_closed_parcours();

//      Deuxième partie :
        await this.load_curent_parcours();
    }
 
    async load_closed_parcours() {
        
//      On chage la liste des parcours clos
//      Pour chaque parcours, on l'ajoute à l'affichage et à la iste des parcours gérés
        let option = "?data=datas&idx=" + this.last_data_index;
        let jsonRes = await loadJson(this.url + option);
        const liste_new_co = jsonRes.datas;
        this.last_data_index = await jsonRes.last_index;
        //console.log(new_co);

        if (liste_new_co.length > 0) {
            console.log(liste_new_co);
            console.log("co load_closed_parcours");
            //if (this.liste_co.length === 0) {
                //si la liste des course enregistrée est vide
                //on place tout d'un coup
                //this.liste_co = liste_new_co;
            //} else {
                //on parcours la liste des nouvelles courses
                for (let new_co of liste_new_co) {
                    console.log(new_co);
                    //for (var i = 0; i < new_co.length; i++) {
                    //on cherche si le participant a déja réalisé une course 
                    let idCo = this.liste_co.findIndex(p => p.id_participant === new_co.id_participant);
                    if (idCo > -1) {
                        // si c'est le cas, on ajoute la course dans la liste des courses du participant
                        //on met à jours l'index de la dernière course réalisée
                        this.liste_co[idCo].lstco = this.liste_co[idCo].lstco.concat(new_co.lstco);
                    } else {
                        //sinon, on rajoute la course telle quelle
                        this.liste_co.push(new_co);
                        idCo = this.liste_co.length-1;
                        console.log(this.liste_co.length-1);
                    }
                    this.toAdd[new_co.id_participant]=idCo;
                }
            //}
            this.show_lasts_co();
        }
    }
    async load_curent_parcours() {
//      On chage la liste des parcours en cours ou programmés
//      Pour chaque parcours, on remplis la colomne ic_co en conséquence
        const  option = "?data=curent";
        const jsonRes = await loadJson(this.url + option);
        const liste_curent_co = jsonRes.parcours;

        if (liste_curent_co.length > 0) {
            console.log("co liste_curent_co");
            console.log(liste_curent_co);
            for (var p of this.tbody.rows) {
                const p_idco = p.cells[3];
                const curent_co = liste_curent_co.find(c => c.id_participant === p.id);
                if (curent_co) {
                    if (p.children[0].tagName !== "div") { //ligne qui n'est pas en cours d'édition
                        p_idco.dataset.id_co = curent_co.id_co;
                        p_idco.dataset.idp = curent_co.idp;
                        if (curent_co.etat == 1) { //parcours démarré mais non terminé
                            p_idco.innerHTML = '<h6><span class="badge bg-custom3">' + curent_co.nom + '</span><h6>';
                            p_idco.dataset.etat = 1;
                        } else if (curent_co.etat == 0) { //parcours déclaré mais non démarré
                            p_idco.innerHTML = '<h6><span class="badge bg-custom2">' + curent_co.nom + '</span><h6>';
                            p_idco.dataset.etat = 0;
                        }
                    }
                }
                
            }
        }
    }

    show_legend() {
        let cell = this.thead.rows[0].cells[4];
        if (cell.id !== "td_co_legend") {
            cell.style = "padding:0px;min-width:700px;";
            const legend = document.getElementById("tmpl_co_legend").content.cloneNode(true);
            cell.append(legend);
            cell.id = "td_co_legend";
        }
    }

    show_lasts_co() {
        //on parcours la liste des nouvelles courses
        //on garde l'index de la dernière pour chaque participant
        //et on la met à jour.
        console.log(this.toAdd);
        for (let idp in this.toAdd) {
            console.log(idp);
            const idco = this.toAdd[idp];
            console.log(idco);
            const newCo = this.liste_co[idco];
            
            let p = document.getElementById(idp);
            p.cells[3].innerHTML = "";
            p.cells[3].dataset.etat = "";
            p.cells[3].dataset.idco = "";
            p.cells[3].dataset.idp = 0;
            p.cells[4].innerHTML = "";

            let co_banner = document.getElementById("tmpl_co").content.cloneNode(true);
            p.cells[4].append(co_banner);
            this.showCo(newCo);
        }
        this.toAdd={};
    }

    upParcours(id_participant) {
        let id_co = this.liste_co.findIndex(p => p.id_participant == id_participant);
        if (id_co > -1) {
            let liste_co_p = this.liste_co[id_co];
            let p = document.getElementById(id_participant);
            const table = p.querySelector("tbody");
            let co_idx = Number(table.children[0].children[0].textContent)-1;
            console.log(co_idx);
            if (co_idx < liste_co_p.lstco.length - 1) {
                co_idx = co_idx + 1;
                this.clearCo(id_participant);
                console.log(co_idx);
                this.showCo(liste_co_p,co_idx);
            }
        }
    }

    downParcours(id_participant) {
        
        let id_co = this.liste_co.findIndex(p => p.id_participant == id_participant);
        if (id_co > -1) {
            console.log("down");
            let liste_co_p = this.liste_co[id_co];
            let p = document.getElementById(id_participant);
            const table = p.querySelector("tbody");
            let co_idx = table.children[0].children[0].textContent-1;
            if (co_idx > 0) {
                co_idx = co_idx - 1;
                this.clearCo(id_participant);
                this.showCo(liste_co_p,co_idx);
            }
        }
    }

    /****************************************
     //function d'affichage de un parcours
     ****************************************/
    showCo(liste_co_p, index = -1 ) {
        console.log(liste_co_p);
        if(index<0) index = liste_co_p.lstco.length-1;
        const co = liste_co_p.lstco[index];
        console.log(index);
        console.log(co);
        let p = document.getElementById(liste_co_p.id_participant);
        const table = p.querySelector(".co");
        const tr0 = table.children[0].children[0];
        const tr1 = table.children[0].children[1];
        const co_rep = p.querySelector(".co_n");
        const co_left = p.querySelector(".co_left");
        const co_right = p.querySelector(".co_right");
        const name = p.querySelector(".name");
        const start = p.querySelector(".start");

        //affichage des repères numérique
        co_rep.textContent = (index+1); //+ "/" + liste_co_p.lstco.length;
        co_right.disabled = index === 0;
        co_left.disabled = index + 1 === liste_co_p.lstco.length;

        //on retrouve les informations de parcours depuis la liste
        var parcours = this.parcours.find(c => c.id === co.id_parcours);
        if (parcours) {
            let t_start = new Date(co.t_start);
            var heure;
            var pts = 0;
            var td1, td2;

            //on met a jour les informations de parcours
            name.textContent = parcours.nom;
            start.textContent = this.formatDateTime(t_start);

            //on parcours les balises dans le sens des balises trouvées par le coureur
            for (var idx = 0; idx < co.balises.length; idx++) {
                const bScanned = co.balises[idx];
                const fb = this.foundBalise(bScanned, parcours, idx);

                td1 = tr0.insertCell();
                td1.textContent = fb.nom;
                if (fb.succeed === 0) {
                    td1.classList.add('false');
                } else if (fb.succeed === 1) {
                    td1.classList.add('mid');
                } else if (fb.succeed === 2) {
                    td1.classList.add('good');
                }
                pts = pts + parseInt(fb.value);
                let h = new Date(bScanned.temps);
                heure = this.formatTime((h - t_start) / 1000);
                td2 = tr1.insertCell();
                td2.textContent = heure;
            }
            //on cherche les balises oubliées
            for (var idx = 0; idx < parcours.balises.length; idx++) {
                const bToFind = parcours.balises[idx];
                const fb = co.balises.find(b => b.tag === bToFind.tag);
                if (!fb) {
                    td1 = tr0.insertCell();
                    if (bToFind.nom === "")
                        td1.textContent = bToFind.nombalise;
                    else
                        td1.textContent = bToFind.nom;
                    td1.classList.add('forget');
                    td2 = tr1.insertCell();
                    pts = pts - 2;
                }
            }

            td1 = tr0.insertCell();
            td1.classList.add('pts');
            td1.textContent = pts + " pts";
            let h = new Date(co.t_end);
            heure = this.formatTime((h - t_start) / 1000);
            td2 = tr1.insertCell();
            td2.classList.add('tps');
            td2.textContent = heure;

        }
    }

    foundBalise(trouvee, parcours, idx) {
        var res = {};
        if (parcours.ordre > 0) {
            const balise = parcours.balises[idx];
            if (balise) {
                //console.log("balise");
                if (trouvee.tag === balise.tag) {
                    res.nom = trouvee.nom;
                    res.value = balise.value;
                    res.temps = trouvee.temps;
                    res.succeed = 2;
                }
            }
            //console.log(res);
            if (!res.succeed) {
                var found = parcours.balises.find(b => b.tag === trouvee.tag);
                if (found) {
                    //console.log("not res but found");
                    res.nom = trouvee.nom;
                    res.value = -1;
                    res.temps = trouvee.temps;
                    res.succeed = 1;
                } else {
                    console.log("not res andn not found");
                    res.nom = trouvee.nom;
                    res.value = -2;
                    res.temps = trouvee.temps;
                    res.succeed = 0;
                }
            }
        } else {
            var found = parcours.balises.find(b => b.tag === trouvee.tag);
            //console.log(parcours);
            if (found) {
                //console.log("not ordre and found");
                res.nom = trouvee.nom;
                res.value = found.value;
                res.temps = trouvee.temps;
                res.succeed = 2;
            } else {
                //console.log("not ordre and not found");
                //console.log(trouvee);
                res.nom = trouvee.nom;
                res.value = -2;
                res.temps = trouvee.temps;
                res.succeed = 0;
            }
        }
        return res;
    }

    insertCells() {
        let cell = this.thead.rows[0].cells[4];
        if (!cell) {
            cell = this.thead.rows[0].cells[3];
            cell = this.thead.rows[0].insertCell(3);
            cell.innerHTML = '<button type="button" class="btn btn-secondary btn-sm" id="refresh">\n\
                                <i class="bi bi-arrow-counterclockwise" style="font-size: 1.1rem;"></i>\n\
                                </button>';
            cell.classList.add("idco_titre");
            document.getElementById('refresh').addEventListener('click', (e)=>{
                e.preventDefault();
                this.refresh();
            });

            cell = this.thead.rows[0].insertCell(4);
            cell.id !== "td_co_legend";

            for (var row of this.tbody.rows) {
                if (row.id) {
                    let cell = row.cells[3];
                    if (!cell) {
                        cell = row.insertCell(3);
                        cell.classList.add('idco');
                        cell.dataset.etat = "";
                    }
                    cell = row.cells[5];
                    if (cell) {
                        if (cell.children[0])
                            cell.children[0].remove();
                    } else {
                        cell = row.insertCell(4);
                        cell.style = "padding:0px;";
                    }
                }
            }
        } else {
            //on ajoutee les cases quis manque???
        }
    }

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    clearCo(id_participant) {
        let p = document.getElementById(id_participant);
        const table = p.querySelector("tbody");
        if(table){
            while (table.children[0].cells.length > 2) {
                table.children[0].deleteCell(-1);
                table.children[1].deleteCell(-1);
            }
        }
    }
    
    async refresh(){
        await this.clear();
        await this.load();
    }
    
    async clear() {
        //console.log("clear coTable");
        this.p = 0;
        this.last_data_index = 0;

        for (var i = 0; i < this.tbody.rows.length; i++) {
            let r = this.tbody.rows[i];
            while (r.cells.length > 3) {
                r.deleteCell(-1);
            }
        }
        while (this.thead.rows[0].cells.length > 3) {
            this.thead.rows[0].deleteCell(-1);
        }

        await this.sleep(10);
    }

    formatDateTime(dt, showH = true) { // This is to display 12 hour format like you asked
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

    formatTime(time) { // This is to display 12 hour format like you asked
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
}


export { coTableP };