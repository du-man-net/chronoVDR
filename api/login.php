<?php
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set("display_errors", 1);
session_start();

header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

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

require_once '../class/AdminClass.php';

$myadmin = new Admin();

$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

// Exemple simple
if($myadmin->auth($username,$password)){
    
    echo json_encode([
        "success" => true,
        "message" => "Connexion réussie"
    ]);

} else {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Identifiants invalides"
    ]);
}