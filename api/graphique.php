<?php

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
$t_datas=[];$t_data=[];
        
$last_index = 0;
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
    $point = [];
    
    $point['x'] = $data['temps'];//date('c', strtotime($data['temps'])); 
    
    if(is_null($data['data'])){
        $point['y'] = 0;
    }else{
        $point['y'] = $data['data'];
    }
    $t_data[$data['id_participant']][] = $point;
    
    if($data['id']>$last_index){
        $last_index = $data['id'];
    }
}

$t_datas["last_index"]=$last_index;
$t_datas["datas"] = $t_data;
echo json_encode($t_datas);
exit;


    