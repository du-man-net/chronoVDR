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

/**
 * Description of FormClass
 *
 * @author gleon
 */
class Form {

    private function format($ligne) {
        return $ligne . "\n";
    }

    public function openForm($name, $action = '', $method = 'POST') {
        echo $this->format('<form name="' . $name . '" '
                . 'action="' . $action . '" '
                . 'method="' . $method . '" '
                . 'enctype="multipart/form-data">');
    }

    public function closeForm() {
        echo $this->format('</form>');
    }

    public function label($name, $titre) {
        echo $this->format('<label for="' . $name . '">' . $titre . ' : </label>');
    }

    public function text($name, $value = '', $other = '') {
        $ret = '<input type="text" name="' . $name . '" id="' . $name . '" ';
        if (!empty($value)) {
            $ret .= 'value="' . $value . '" ';
        }
        if (!empty($other)) {
            $ret .= $other;
        }
        echo $this->format($ret . '/>');
    }

    public function textarea($name,$value='',$rows=31,$cols=35,$readonly=true){
        $ret = '<textarea ';
        if($readonly){
            $ret .= 'readonly="true"';
        }
        echo $this->format($ret . 'name="'.$name.'" rows="'.$rows.'" cols="'.$cols.'">');
        echo $this->format($value);  
        echo $this->format('</textarea>');
    }
    
    public function password($name, $value = '', $other = '') {
        $ret = '<input type="password" name="' . $name . '" id="' . $name . '" ';
        if (!empty($value)) {
            $ret .= 'value="' . $value . '" ';
        }
        if (!empty($other)) {
            $ret .= $other;
        }
        echo $this->format($ret . '/>');
    }
    
    public function time($name, $value = '', $other = '') {
        $ret = '<input step="1" type="time" name="' . $name . '" id="' . $name . '" ';
        if (!empty($value)) {
            $ret .= 'value="' . $value . '" ';
        }
        if (!empty($other)) {
            $ret .= $other;
        }
        echo $this->format($ret . '/>');
    }

    public function date($name, $value = '', $other = '') {
        $ret = '<input type="date" name="' . $name . '" id="' . $name . '" ';
        if (!empty($value)) {
            $ret .= 'value="' . $value . '" ';
        }
        if (!empty($other)) {
            $ret .= $other;
        }
        echo $this->format($ret . '/>');
    }

    public function button($name, $value = '', $other='') {
        $ret = '<input type="submit" name="'.$name.'" id="'.$name.'" '; 
        if (!empty($value)) {
            $ret .= 'value="'.$value.'" ';
        }
        if (!empty($other)) {
            $ret .= $other;
        }
        echo $this->format($ret.'/>');
    }

    public function hidden($name, $value = '') {
        echo $this->format('<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $value . '"/>');
    }

    public function checkbox($name, $value = '', $other = '') {
        $ret = '<input type="checkbox" name="' . $name . '" value="' . $value . '" ';
        if (!empty($other)) {
            $ret .= $other;
        }
        echo $this->format($ret . '/>');
    }

    public function openSelect($name, $id = '', $other = '') {
        $ret = '<select id="' . $id . '" name="' . $name . '" ';
        if (!empty($other)) {
            $ret .= $other;
        }
        echo $this->format($ret . '>');
    }

    public function closeSelect() {
        echo $this->format('</select>');
    }

    public function radio($name, $value, $id = '') {
        $ret = '<input type="radio" name="' . $name . '" value="' . $value . '" ';
        if (!empty($id)) {
            $ret .= 'id="' . $id ."'";
        }
        echo $this->format($ret . '/>');
    }
  
    public function option($val, $value = '', $select = false) {
        $ret = '<option value="' . $val .'" ';
        if ($select) {
            $ret .= 'selected="selected"';
        }
        echo $this->format($ret . '>' . $value . '</option>');
    }
    
    public function file($name, $other = '') {
        $ret = '<input type="file" name="'.$name.'" id="'.$name.'" ';
        if (!empty($other)) {
            $ret .= $other;
        }
        echo $this->format($ret . '/>');
    } 
    
}
