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

/**
 * Description of HtmlClass
 *
 * @author gleon
 */
class Html {

    private function format($ligne) {
        return $ligne . "\n";
    }

    public function openHtml() {
        $ret = $this->format('<!DOCTYPE html>');
        $ret .= $this->format('<html lang="fr">');
        echo $ret;
    }

    public function closeHtml() {
        echo $this->format('</html>');
    }

    public function openHead($value) {
        echo $this->format('<head>');
    }

    public function closeHead(){
        echo $this->format('</head>');
    }
    
    public function openBody() {
        echo $this->format('<body>');
    }

    public function closeBody() {
        echo $this->format('</body>');
    }

    public function openDiv($id, $class ='', $other = '') {
        $ret = '<div ';
        if (!empty($id)) {
            $ret .= 'id="' . $id . '" ';
        }
        if (!empty($class)) {
            $ret .= 'class="' . $class . '" ';
        }
        if (!empty($other)) {
            $ret .= $other;
        }
        echo $this->format($ret . '>');
    }

    public function closeDiv() {
        echo $this->format('</div>');
    }

    public function openDialog($id, $show = '', $other = '') {
        $ret = '<dialog id="' . $id . '" ';
        if ($show != 'open') {
            $show = "close";
        }
        $ret .= $show . ' ';
        if (!empty($other)) {
            $ret .= $other;
        }
        echo $this->format($ret . '>');
    }

    public function closeDialog() {
        echo $this->format('</dialog>');
    }
    
    public function bold($value){
        return '<b>'.$value.'</b>';
    }
    
    public function openTable($other = '') {
        $ret = '<table ';
        if (!empty($other)) {
            $ret .= $other;
        }
        echo $this->format($ret . '>');
    }

    public function closeTable() {
        echo $this->format('</table>');
    }

    public function openTr() {
        echo $this->format('<tr>');
    }

    public function closeTr() {
        echo $this->format('</tr>');
    }

    public function openTd($class = '', $other = '') {
        $ret = '<td ';
        if (!empty($class)) {
            $ret .= 'class = "' . $class . '" ';
        }
        if (!empty($other)) {
            $ret .= $other;
        }
        echo $ret . '>';
    }

    public function closeTd() {
        echo $this->format('</td>');
    }
}
