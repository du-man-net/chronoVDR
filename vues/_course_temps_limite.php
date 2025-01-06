<?php

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

require_once '../class/ActiviteClass.php';

$myactivite = new Activite($mysqli);

$participants = $myactivite->get_participants();
$t_datas=[];
if (!empty($participants)) {
    foreach ($participants as $participant) {
        $i=0;
        $dt_start = null;
        $dt = null;
        $point = [];$points=[];$t_data=[];
        $datas = $myactivite->get_datas($participant['id_participant']);
        foreach ($datas as $data) {
            
            $point['x'] = date('c', strtotime($data['temps'])); 

            if(is_null($data['data'])){
                $point['y'] = 0;
            }else{
                $point['y'] = $data['data'];
            }
            $points[]=$point;
            $i++;
        }
        $t_data['id'] = $participant['id_participant'];
        $t_data['datas'] = $points;
        $t_datas[] = $t_data;
    }
}
//echo json_encode(array('datas'=>$data1,'temps'=>$temps));
echo json_encode($t_datas);