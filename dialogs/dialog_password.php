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

/*
 * ------------------------------------------------------
 * Fenètre d''authentification par mot de passe pour l'activité
 * ------------------------------------------------------
 */
$myhtml->openDiv('password');
    $myhtml->openDialog('propriete_password', $_POST["show_password"]);
        $myhtml->openDiv('','proprieteclose');
            $myform->button('cancel_password', " X ",'data-val="return"');
        $myhtml->closeDiv();
        $myhtml->openDiv('','titre_propriete');
            echo 'Mot de passe admin';
        $myhtml->closeDiv();
        $myhtml->openDiv('','propriete');
            $myform->label('auth',$message_password,$format_password);
            $myform->password('auth');
        $myhtml->closeDiv(); 
        $myhtml->openDiv('','proprietebtn');
            $myform->button('valider_PW', "Valider");
        $myhtml->closeDiv();   
    $myhtml->closeDialog();
$myhtml->closeDiv();  