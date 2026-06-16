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



export function get_modal_conf(title, message) {
    let nodal_title = document.getElementById('modal_conf_title');
    nodal_title.innerHTML = "";
    let mytitle = document.createTextNode(title);
    nodal_title.appendChild(mytitle);
    let nodal_message = document.getElementById('modal_conf_body');
    nodal_message.innerHTML = "";
    let mymessage = document.createTextNode(message);
    nodal_message.appendChild(mymessage);
    const modal_conf = new bootstrap.Modal(document.getElementById('modal_conf'))
    return modal_conf;
}

export function get_modal_alert(title, message) {
    let nodal_title = document.getElementById('modal_alert_title');
    nodal_title.innerHTML = "";
    let mytitle = document.createTextNode(title);
    nodal_title.appendChild(mytitle);
    let nodal_message = document.getElementById('modal_alert_body');
    nodal_message.innerHTML = "";
    let mymessage = document.createTextNode(message);
    nodal_message.appendChild(mymessage);
    const modal_conf = new bootstrap.Modal(document.getElementById('modal_alert'));
    return modal_conf;
}

export function showOffcanvasProps(etat) {
    let el = document.getElementById('off_props');
    if (etat) {
        let OffcanvasProps = new bootstrap.Offcanvas(el);
        OffcanvasProps.show();
    } else {
        let OffcanvasProps = bootstrap.Offcanvas.getInstance(el); // `getInstance` is the difference
        OffcanvasProps.hide();
    }
}

export function showOffcanvasMenu(etat) {
    let el = document.getElementById('off_menu');
    if (etat) {
        let OffcanvasMenu = new bootstrap.Offcanvas(el);
        OffcanvasMenu.show();
    } else {
        let OffcanvasMenu = bootstrap.Offcanvas.getInstance(el); // `getInstance` is the difference
        OffcanvasMenu.hide();
    }
}

export function showOffcanvasToAdd(etat) {
    let el = document.getElementById('off_ptoadd');
    if (etat) {
        let OffcanvasToAdd = new bootstrap.Offcanvas(el);
        OffcanvasToAdd.show();
    } else {
        let OffcanvasToAdd = bootstrap.Offcanvas.getInstance(el); // `getInstance` is the difference
        OffcanvasToAdd.hide();
    }
}

