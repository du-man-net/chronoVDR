
<?php
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set("display_errors", 1);
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


require_once '../class/ActiviteClass.php';

$myactivite = new Activite();

$last_index = 0;
$myDatas = [];$t_datas = [];

if (isset($_GET['idx'])) {
    $last_index = $_GET['idx'];
}

if ($last_index==0){
    $datas = $myactivite->get_all_datas();
    $t_datas["flag"]=$myactivite->get_flag();
}else{
    $datas = $myactivite->get_last_datas($last_index);
}

foreach ($datas as $data) {
    $myData = [];
    $myData['id'] = $data['id_participant'];
    $myData['temps'] = $data['temps'];
    $myData['data'] = $data['data'];
    $myDatas[]=$myData;
    if($data['id']>$last_index){
        $last_index = $data['id'];
    }
}

$t_datas["last_index"]=$last_index;
$t_datas["datas"]=$myDatas;
echo json_encode($t_datas);
