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

import { loadJson, sendJson } from './ajax.js';

document.addEventListener('DOMContentLoaded', () => {
    

    document.getElementById('btn_on').addEventListener('click', (el) => {
        let request = {
            topic: "mbit",
            message: "1:toto"
        };
        let url = "http://" + window.location.hostname + ":8080";
        sendJson(url,request);
    });
    
    document.getElementById('btn_off').addEventListener('click', (el) => {
        let request = {
            topic: "1",
            message: "titi"
        };
        let url = "http://" + window.location.hostname + ":8080";
        sendJson(url,request);
    });
});
