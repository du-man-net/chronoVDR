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


export async function loadJson(url) {
    let response = await fetch(encodeURI(url));

    if (!response.ok === 200) {
        throw new Error(response.status);
    }
    const result = await response.json();
    return result;
}

// Send POST request
export async function sendJson(url, jsonObj) {
    const response = await fetch(encodeURI(url), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json' // Specifies the format of the request body
        },
        body: JSON.stringify(jsonObj) // Convert the JavaScript object to a JSON string
    });
    //console.log(response);
    return response.json();
}

// Send POST request
export async function formSend(url, formData) {
    const response = await fetch(encodeURI(url), {
        method: "POST",
        body: formData
    });
    //console.log(response);
    const jsonRes = await response.json();
    return jsonRes;
}

export async function isAuth() {
    try {
        const response = await fetch(encodeURI('../api/profile.php'), {
            credentials: 'include'
        });

        const jsonRes = await response.json();
        return jsonRes;

    } catch (err) {
        return {"logged":false};

    }

}

export async function login(username, password) {
    const response = await fetch('../api/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'include', // important pour les cookies de session
        body: JSON.stringify({
            username,
            password
        })
    });
    const jsonRes = await response.json();
    return jsonRes;

}