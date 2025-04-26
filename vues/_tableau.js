/* 
 * Copyright (C) 2025 gleon
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

//tabelau d'index
var tabIdx = new Array();
var last_index = 0;
var flag = 0;

const BY_RFID = 0x1;
const BY_IDMAT = 0x2;

const SHOW_TIME = 0x4 ;
const SHOW_DATA = 0x8 ;
const SHOW_START = 0x10 ;

const TIME_SINCE_START = 0x20;
const HOUR_PER_LAP = 0x40;
const TIME_PER_LAP = 0x80;
//const DATA_PER_TEST = 0x100;

const IS_LIMIT_TIME = 0x200;
const IS_LIMIT_LAP = 0x400;
const IS_LIMIT = 0x800;

const SHOW_FINAL_TIME = 0x1000;
const SHOW_NUMBER_LAPS = 0x2000;
const SHOW_TOTAL_DATA = 0x4000;

window.addEventListener("resize", function () {
    const parentIframe = window.parent.frames;
    if (parentIframe && parentIframe.ajusteIframe) {
        parentIframe.ajusteIframe();
    }
});

window.onload = function () {
    putAjaxDatasToTab();
};

window.addEventListener("message", function (event) {
    if (event.origin === " ") {
        return;
    }
    putAjaxDatasToTab();
});

function putAjaxDatasToTab(){

    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", encodeURI("_tableau.php?idx="+last_index), true);
    xhttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            if (this.responseText.length > 0) {
                
                //console.log(this.responseText);
                const users_datas = JSON.parse(this.responseText);

                //on récupère les drapeaux de l'activité si ils sont transmis (une seule fois)
                if(users_datas['flag']){
                    flag = users_datas['flag'];
                }

                //on récupère le dernier index pour minimiser les reqêtes suivantes
                last_index = users_datas['last_index'];
                
                for (const users_data of users_datas['datas']) {
                    addDataToLine(users_data['id'],users_data['temps'],users_data['data']);
                }
            }
        }
    };
    xhttp.send();
}

function addDataToLine(id,dt,value){
    if(!tabIdx[id]){
        tabIdx[id]=0;
    }
    tabIdx[id]++;
    
    //on essaye de récupérer la ligne de temps 
    if(check(SHOW_TIME)){
        var row = document.getElementById(id+"0");
        if(row){
            //on essaye de récupérer la cellule 
            let cell = row.cells[tabIdx[id]];

            //on vérifie si la  cellule est dans la colonne total
            var coll_resultats = false;
            if (cell){
                if(cell.classList.contains('total')){
                    coll_resultats = true;
                }
            }
            //si la cellule, n'exsite oas ou est dans la conne total
            if(!cell || coll_resultats ){
                //on créer une ccolonne suplémentaire
                addCellToAllLine(coll_resultats);
                cell = row.cells[tabIdx[id]];
            }
            //on formate les données
            //dans la première colomne
            if(tabIdx[id]===1){
                //on affiche la date telle quelle
                cell.title = dt;
                dt = new Date(dt);
                cell.innerHTML = formatDateTime(dt);

            //dans les autres, ça dépend de l'activité
            }else{
                if(check(HOUR_PER_LAP)){
                    cell.title = dt;
                    dt = new Date(dt);
                    cell.innerHTML = formatDateTime(dt);

                }else if(check(TIME_PER_LAP)){
                    cell.title = dt;
                    dt = Date.parse(dt);
                    const cellbefore = row.cells[tabIdx[id]-1];
                    const datebefore = Date.parse(cellbefore.title);
                    const diff = (dt - datebefore)/1000;
                    cell.innerHTML = formatTime(diff);

                }else if(check(TIME_SINCE_START)){
                    cell.title = dt;
                    dt = Date.parse(dt);
                    const firstcell = row.cells[1];
                    const firstdate = Date.parse(firstcell.title);
                    const diff = (dt - firstdate)/1000;
                    cell.innerHTML = formatTime(diff);
                }
            }
            
            if(check(SHOW_FINAL_TIME)){
                let lastcell = row.cells.length-1;
                cell = row.cells[lastcell];
                const firstcell = row.cells[1];
                const firstdate = Date.parse(firstcell.title);
                const diff = (dt - firstdate)/1000;
                cell.innerHTML = formatTime(diff);
                
            }else if(check(SHOW_NUMBER_LAPS)){
                let lastcell = row.cells.length-1;
                cell = row.cells[lastcell];
                cell.innerHTML = tabIdx[id];
            }
        }
    }
    if(check(SHOW_DATA)){
        //on essaye de récupérer la ligne de data 
        row = document.getElementById(id+"1");
        if(row){
            //on essaye de récupérer la cellule
            var cell = row.cells[tabIdx[id]];

            //on vérifie si la  cellule est dans la colonne total
            if (cell){
                if(!cell.classList.contains('total')){
                    cell.innerHTML = value;
                }
            }
            if(check(SHOW_TOTAL_DATA)){
                let lastcell = row.cells.length-1;
                cell = row.cells[lastcell];
                var total = 0; 
                for(var i=0;i<row.cells.length-1;i++){
                    let numb = row.cells[i].innerText;
                    //on filtre les espaces insécable (cellules vides)
                    if (numb.charCodeAt(0)!==160){
                        total += parseFloat(numb);
                    }
                }
                cell.innerHTML = total;
            }
        }
    }

}

function addCellToAllLine(coll_resultats){
    const table = document.getElementById("results");
    
    const row = table.rows[0];
    var numb = row.cells.length;
    if (coll_resultats){
        numb--;
    }
    var cell = row.insertCell(numb);
    cell.classList.add('titre_time');
    cell.innerHTML  = "temps" + numb;

    for (let i = 1; i < table.rows.length; i++) {
        cell = table.rows[i].insertCell(numb);
        cell.classList.add('data');
    }
}

function check(thisflag){
    if ((flag & thisflag) === thisflag) {
        return true;
    }
    return false;
}

function formatDateTime(dt) { // This is to display 12 hour format like you asked
    const y = dt.getFullYear();
    const M = dt.getMonth();
    const d = dt.getDate();
    var h = dt.getHours();
    var m = dt.getMinutes();
    var s = dt.getSeconds();
    h = h < 10 ? '0'+ h : h;
    m = m < 10 ? '0'+ m : m;
    s = s < 10 ? '0'+ s : s;
    var strTime = h + ':' + m + ':' + s;
    //var strTime = d + "/" + M + "/" + y + " " + h + ':' + m + ':' + s;
    return strTime;
}

function formatTime(time) { // This is to display 12 hour format like you asked
    var d = Math.floor(time / 86400);
    var h = Math.floor((time % 86400) / 3600);
    var m = Math.floor((time % 3600) / 60);
    var s = time % 60;
    h = h < 10 ? '0'+ h : h;
    m = m < 10 ? '0'+ m : m;
    s = s < 10 ? '0'+ s : s;
    if(d>0){
        return d + "j " + h+":"+m+":"+s;
    }else if(h>0){
        return h+":"+m+":"+s;
    }else{
        return m+":"+s;
    }
}