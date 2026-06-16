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

class tableP {
    constructor() {
        this.parts = document.getElementById("parts");
        this.thead = this.parts.getElementsByTagName('thead')[0];
        this.tbody = this.parts.getElementsByTagName('tbody')[0];
        this.p = 0;
        this.last_data_index = 0;
        this.first_idx = 3;
        this.last_idx = 2;
        this.last_p_idx = [];
        this.flag = 0;
        this.res = 0;
        this.span = 0;
    }

    get url() {
        return "../api/tableau.php";
    }

    setFlag(flag) {
        this.flag = flag;
        this.res = (this.check(flags.SHOW_FINAL_TIME) ||
                this.check(flags.SHOW_NUMBER_LAPS) ||
                this.check(flags.SHOW_TOTAL_DATA));
        this.span = (this.check(flags.SHOW_TIME) &
                this.check(flags.SHOW_DATA));
        //console.log("table.flag =" + this.flag);
    }

    get res_idx() {
        return this.last_idx + 1;
    }

    get name() {
        return "tableau";
    }

    index(id) {
        if (!this.last_p_idx[id]) {
            return this.first_idx;
        }
        return this.last_p_idx[id];
    }

    nextIndex(id) {
        if (!this.last_p_idx[id]) {
            this.last_p_idx[id] = this.first_idx;
        } else {
            this.last_p_idx[id]++;
        }
        return this.last_p_idx[id];
    }

    check(flag) {
        return(this.flag & flag) === flag;
    }

    async load() {
        //console.log("load datas tableau");
        let option = "?idx=" + this.last_data_index;
        let jsonRes = await loadJson(this.url + option);
        this.last_data_index = await jsonRes.last_index;
        let users_datas = await jsonRes.datas;

        //on traite les données une par une au fil de l'eau
        this.insertColRes();
        for (const data of users_datas) {
            this.add(data);
        }
    }

    insertCol(idx) {
        for (var i = 0; i < this.tbody.rows.length; i++) {
            let p = this.tbody.rows[i];
            let cell = {};
            if (p.id) {
                cell = p.insertCell(this.res_idx);
            } else {
                cell = p.insertCell(this.res_idx - this.first_idx);
            }
            cell.classList.add("data");
            this.p = this.tbody.rows[i];
        }
        //on insert le header de la colomne
        let cell = this.thead.rows[0].insertCell(this.res_idx);
        cell.classList.add("title");
        if (this.res_idx === this.first_idx) {
            cell.textContent = this.get_start_prefix();
        } else {
            cell.textContent = this.get_prefix() + (this.res_idx - this.first_idx + 1);
        }

        this.last_idx++;
    }

    insertColRes() {
        if (this.res) {
            if (this.tbody.rows[0]) {
                if ((this.tbody.rows[0].cells.length - 1) < this.res_idx) {
                    for (var i = 0; i < this.tbody.rows.length; i++) {
                        let p = this.tbody.rows[i];
                        let cell = {};
                        if (p.id) {
                            cell = p.insertCell(this.res_idx);
                        } else {
                            cell = p.insertCell(this.res_idx - this.first_idx);
                        }
                        cell.classList.add("total");
                    }
                    //on insert le header de la colomne
                    let cell = this.thead.rows[0].insertCell(this.res_idx);
                    cell.id = "colRes";
                    cell.classList.add("total");
                    cell.textContent = this.get_end_prefix();
                }
            }
        }
    }

    add(data) {
        if (this.last_idx < this.nextIndex(data.id)) {
            this.insertCol(this.index(data.id));
        }

        this.p = document.getElementById(data.id.toString());
        if (this.p) {
            let cell = this.p.cells[this.index(data.id)];
            cell.title = data.temps;
            if (this.check(flags.SHOW_TIME)) {
                cell.textContent = this.prepare_time(data);
            } else {
                if (this.check(flags.SHOW_DATA)) {
                    cell.textContent = data.data;
                }
            }
            if (this.span) {
                if (this.check(flags.SHOW_DATA)) {
                    let secondRow = this.p.parentNode.rows[ this.p.rowIndex];
                    cell = secondRow.cells[this.index(this.p.id) - this.first_idx];
                    cell.textContent = data.data;
                }
            }

            let total = 0;
            if (this.res) {
                if (this.check(flags.SHOW_TIME)) {
                    total = this.prepare_time_total();
                } else {
                    if (this.check(flags.SHOW_DATA)) {
                        total = this.prepare_data_total();
                    }
                }
                if (total) {
                    cell = this.p.cells[this.res_idx];
                    cell.textContent = total;
                }
                if (this.span) {
                    if (this.check(flags.SHOW_DATA)) {
                        total = this.prepare_data_total();
                        cell = this.p.cells[this.res_idx];
                        cell.textContent = total;
                    }
                }
            }
        }
    }

    prepare_time(data) {
        let dt = 0;
        if (this.index(data.id) === this.first_idx) {
            //on affiche la date telle quelle
            dt = new Date(data.temps);
            data.t = this.formatDateTime(dt);

            //dans les autres, ça dépend de l'activité
        } else {
            if (this.check(flags.HOUR_PER_LAP)) {
                dt = new Date(data.temps);
                data.t = this.formatDateTime(dt);

            } else if (this.check(flags.TIME_PER_LAP)) {
                dt = Date.parse(data.temps);
                let cellBefore = this.p.cells[this.index(data.id) - 1];
                let dateBefore = Date.parse(cellBefore.title);
                data.t = this.formatTime((dt - dateBefore) / 1000);

            } else if (this.check(flags.TIME_SINCE_START)) {
                dt = Date.parse(data.temps);
                let firstCell = this.p.cells[this.first_idx];
                let firstDate = Date.parse(firstCell.title);
                data.t = this.formatTime((dt - firstDate) / 1000);
            }
        }
        return data.t;
    }

    prepare_time_total()
    {
        if (this.check(flags.SHOW_FINAL_TIME)) {

            let firstcell = this.p.cells[this.first_idx];
            const firstDate = Date.parse(firstcell.title);

            let lastcell = this.p.cells[this.index(this.p.id)];
            const lastDate = Date.parse(lastcell.title);

            return this.formatTime((lastDate - firstDate) / 1000);

        } else if (this.check(flags.SHOW_NUMBER_LAPS)) {
            return (this.index(this.p.id) - this.first_idx);
        }
        return false;
    }

    prepare_data_total(row) {
        if (this.check(SHOW_TOTAL_DATA)) {
            let firstIndex = this.first_idx;
            if (this.span) {
                firstIndex = 0;
            }
            let total = 0;
            for (var i = firstIndex; i < this.last_idx; i++) {
                let numb = row.cells[i].innerText;
                //on filtre les espaces insécable (cellules vides)
                if (numb.charCodeAt(0) !== 160) {
                    total += parseFloat(numb);
                }
            }
            return total;
        }
        return false;
    }

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async clear() {
        //console.log("clear tab");
        this.p = 0;
        this.last_data_index = 0;
        this.first_idx = 3;
        this.last_idx = 2;
        this.last_p_idx = [];

        for (var i = 0; i < this.tbody.rows.length; i++) {
            let r = this.tbody.rows[i];
            while (r.cells.length > 3) {
                if(this.span)
                    if(r.nextElementSibling)
                        r.nextElementSibling.deleteCell(-1);
                r.deleteCell(-1);
            }
        }
        while (this.thead.rows[0].cells.length > 3) {
            this.thead.rows[0].deleteCell(-1);
        }
        await this.sleep(10);
    }
//****************************************************************
// Calcul de l'entête des colonnes
//****************************************************************
    get_start_prefix() {
        if (this.flag & flags.SHOW_START)
            return "Départ";
        else {
            return this.get_prefix() + "1";
        }
    }

//****************************************************************
// Calcul de l'entête des colonnes
//****************************************************************
    get_prefix() {
        if (this.flag & flags.DATA_PER_TEST)
            return "data";
        if (this.flag & flags.TIME_PER_LAP)
            return "temps";
        if (this.flag & flags.TIME_SINCE_START)
            return "temps";
        if (this.flag & flags.HOUR_PER_LAP)
            return "heure";
    }

//****************************************************************
// Calcul de l'entête de la dernirèe colonne
//****************************************************************
    get_end_prefix() {
        if (this.flag & flags.SHOW_FINAL_TIME)
            return "Temps";
        if (this.flag & flags.SHOW_NUMBER_LAPS)
            return "Nb tours";
        if (this.flag & flags.SHOW_TOTAL_DATA)
            return "Total";
        return "unknow";
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


export { tableP };