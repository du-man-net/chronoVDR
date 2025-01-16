<?php

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

require_once 'class/db.php';

$result = $mysqli->query("SELECT update_time FROM activites WHERE etat='2'");
$ep = $result->fetch_assoc();
$last_modifiy =$ep['update_time'];

echo strtotime($last_modifiy);

close_db($mysqli);