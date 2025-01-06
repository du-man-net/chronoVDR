
/* 
 * Copyright (C) 2024 gleon
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



//import moment from 'moment';
//moment.locale('fr');
//import "chartjs-adapter-moment";
import { Chart } from "chart.js/auto";
//import * as Chartjs from "chart.js";
//const controllers = Object.values(Chartjs).filter(
//        (chart) => chart.id !== undefined
//);
//Chart.register(...controllers);
//import { DateTime } from "luxon";
import 'chartjs-adapter-luxon';
//import "chartjs-adapter-date-fns";
//import { DateTime } from "luxon";
//luxon.Settings.defaultLocale = "fr";




var myChart = {};
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
                        pointRadius: 4,
                        pointBackgroundColor: "rgba(0,0,255,1)",
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
    xhttp.open("GET", encodeURI("_course_temps_limite.php"), true);
    xhttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            if (this.responseText.length > 0) {

                var users_datas = JSON.parse(this.responseText);

                for (const users_data of users_datas) {
                    var id = users_data['id'];
                    var datas = users_data['datas'];
                    console.log(datas);
                    //var myChart = document.getElementById(id);

                    // Create an array of ISO strings
                    let datetimes_isos = [];
                    let datatemp = [];
                    datas.forEach(function (item) {
                        datetimes_isos.push(new Date(item['x']));
                        datatemp.push(item['y']);
                    });
                    
                    myChart[id].data.labels = datetimes_isos;
                    myChart[id].data.datasets[0].data = datatemp;
                    myChart[id].update();
                }

            }
        }
    };
    xhttp.send();
}

window.addEventListener("message", function (event) {
    if (event.origin == " ") {
        return;
    }
    putAjaxDatasToGraph();
});


