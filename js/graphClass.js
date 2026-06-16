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
import { Chart, registerables  } from 'chart.js';
import 'chartjs-adapter-luxon';
Chart.register(...registerables);

import { loadJson } from './ajax.js';


class chartsP {
    constructor() {
        this.parts = document.getElementById("parts");
        this.thead = this.parts.getElementsByTagName('thead')[0];
        this.tbody = this.parts.getElementsByTagName('tbody')[0];
        this.last_data_index = 0;
        this.charts = [];
        this.flag = 0;
    }

    setFlag(flag) {
        this.flag = flag;
    }

    get lastIndex() {
        return this.last_data_index;
    }

    get name() {
        return "graphique";
    }

    get span() {
        return false;
    }

    get url() {
        return "../api/graphique.php";
    }

    check(flag) {
        return(this.flag & flag) === flag;
    }

    createChart(canvas) {

        return new Chart(canvas, {
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
                responsive: false,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        //distribution: 'linear',
                        type: 'time',
                        time: {
                            unit: 'minute'
                        },
                        ticks: {
                            source: 'data'
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
                        display: false//,
//                        text: title,
//                        position: 'top',
//                        align: 'center'
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    insertCharts(id) {
        let r = document.getElementById(id.toString());
        if (r) {
            let cell = r.cells[3];
            if (cell) {
                if (cell.children[0])
                    cell.children[0].remove();
            } else {
                cell = r.insertCell(3);
            }
            let tmpl_charts = document.getElementById("tmpl_charts").content.cloneNode(true);
            cell.append(tmpl_charts);
            let canvas = cell.querySelector("canvas");
            this.charts[r.id] = this.createChart(canvas);
            //this.charts[r.id].resize();
        }
    }

    insertSpinner(r) {
        let cell = r.insertCell(3);
        let tmpl_spinner = document.getElementById("tmpl_spinner").content.cloneNode(true);
        cell.append(tmpl_spinner);
        return cell.children[1];
    }
 
    addData(id, item) {
        if (this.charts[id]) {
            let dt = new Date(item['x']);
            let labels = this.charts[id].data.labels;
            labels.push(dt.toISOString());
            
            const datas = this.charts[id].data.datasets[0];

            if (this.check(flags.TIME_PER_LAP)) {
                if (labels.length > 1) {
                    const datebefore = labels[labels.length - 2];
                    const diffy = (Date.parse(dt) - Date.parse(datebefore)) / 1000;
                    datas.data.push(diffy);
                } else {
                    datas.data.push(0);
                }
            } else {
                datas.data.push(item['y']);
            }
            const diffx = Date.parse(labels[labels.length - 1]) - Date.parse(labels[0]);
            //this.charts[id].canvas.parentNode.style.width  = diffx/2+"px";
            this.charts[id].resize();
        }
    }

    async load() {
        //console.log("load datas graph");
        for (var i = 0; i < this.tbody.rows.length; i++) {
            let r = this.tbody.rows[i];
            if (!r.cells[3]) {
                this.insertSpinner(r);
            }
        }

        let option = "?idx=" + this.lastIndex;
        let jsonRes = await loadJson(this.url + option);

        this.last_data_index = await jsonRes.last_index;
        let users_datas = await jsonRes.datas;

        for (var id in users_datas) {
            if (!this.charts[id])
                this.insertCharts(id);
            const udatas = users_datas[id];
            udatas.forEach(function (item) {
                this.addData(id, item);
            }, this);
            if (this.charts[id])
                await this.charts[id].update('none');
            await this.sleep(5);
        }
        this.tbody.querySelectorAll('.spinner').forEach(e => e.remove());
    }

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async clear() {
        //console.log("clear graph");
        this.p = 0;
        this.last_data_index = 0;
        this.charts = [];

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

}

export { chartsP };