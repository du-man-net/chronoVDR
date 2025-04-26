
/* 
 * Copyright (C) 2025 Gérard Léon
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



import { Chart } from "chart.js/auto";
import 'chartjs-adapter-luxon';

var tabIdx = new Array();
var last_index = 0;
var flag = 0;
var myChart = {};

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

    var id;
    const cells = document.getElementsByTagName("canvas");
    for (const cell of cells) {
        var title = cell.parentNode.children[1].value;
        var id = cell.id;

        myChart[id] = new Chart(cell, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                        pointRadius: 1,
                        pointBackgroundColor: "rgba(0,0,255,1)",
                        borderWidth: 1,
                        data: [],
                        fill: false
                    }]
            },
            options: {
                scales: {
                    x: {
                        //distribution: 'linear',
                        type: 'time',
                        time: {
                            unit: 'minute'
                        },
                        ticks:{
                          source:'data'
                        },
                        adapters: {
                            date: {
                                zone: 'Europe/Paris'
                            }
                        }
                    }
                },
                layout: {
                    padding: 0
                },
                plugins: {
                    title: {
                        display: true,
                        text: title,
                        position: 'top',
                        align: 'center'
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    putAjaxDatasToGraph();
};

function putAjaxDatasToGraph() {
    var xhttp = new XMLHttpRequest();
    xhttp.open("GET", encodeURI("_graphique.php?idx="+last_index), true);
    xhttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            if (this.responseText.length > 0) {

                const datas = JSON.parse(this.responseText);
                
                //on récupère les drapeaux de l'activité si ils sont transmis (une seule fois)
                if(datas['flag']){
                    flag = datas['flag'];
                }

                //on récupère le dernier index pour minimiser les reqêtes suivantes
                last_index = datas['last_index'];
                const users_datas = datas['datas'];
                
                for (var id in users_datas) {
                    const udatas = users_datas[id];

                    // Create an array of ISO strings

                    udatas.forEach(function (item) {
                        
                        let dt = new Date(item['x']);
                        console.log(id);
                        let labels = myChart[id].data.labels;
                        labels.push(dt);
                        
                        if(check(TIME_PER_LAP)){
                            
                            if (labels.length>1){
                                const datebefore = labels[labels.length-2];
                                console.log(Date.parse(datebefore));
                                console.log(Date.parse(dt));
                                
                                const diff = (Date.parse(dt) - Date.parse(datebefore))/1000;
                                myChart[id].data.datasets[0].data.push(diff);
                            }else{
                                myChart[id].data.datasets[0].data.push(0);
                            }
                            
                        }else{
                            myChart[id].data.datasets[0].data.push(item['y']);
                        }
                        
                    });
                    myChart[id].update();
                }

            }
        }
    };
    xhttp.send();
}

window.addEventListener("message", function (event) {
    if (event.origin === " ") {
        return;
    }
    console.log("refresh");
    putAjaxDatasToGraph();
});

function check(thisflag){
    if ((flag & thisflag) === thisflag) {
        return true;
    }
    return false;
}

