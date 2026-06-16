<?php

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

$t_data = [];
$myData = [];
$interfaces = net_get_interfaces();
if ($interfaces){
    foreach($interfaces as $nom=>$interface){
        $data=[];
        $unicasts = $interface['unicast'];
        if (isset($unicasts[1]['address'])) {
            $address = $unicasts[1]['address'];
            $address = substr($address,strpos($address,"<br"));
        }
        if (isset($unicasts[1]['netmask'])) {
            $netmask = $unicasts[1]['netmask'];
            $netmask = substr($netmask,strpos($netmask,"<br"));
        }
        if($nom=="eth0"){
            $type = "filaire";
        }else if($nom=="wlan0"){
            $type = "wifi";
        }
        if(strpos($address,'127')!==0){
            $data["type"] = $type;
            $data["nom"] = $nom;
            $data["adresse"] = $address;
            $data["mask"] = $netmask;
            $myDatas[]= $data;
        }
    }
}
$t_datas["interfaces"]=$myDatas;
echo json_encode($t_datas);