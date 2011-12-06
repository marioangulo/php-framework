<?php

/*
 * This file is part of the Weblegs package.
 * (C) Weblegs, Inc. <software@weblegs.com>
 *
 * This program is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with this program.
 * If not, see <http://www.gnu.org/licenses/>.
 */

class Alert {
    public $alerts;
    
    /**
     * construct the object
     */
    public function __construct() {
        $this->alerts = array();
    }
    
    /**
     * add an item to the array
     * @param mixed $value the data for the alert
     */
    public function add($value) {
        $this->alerts[] = $value;
    }
    
    /**
     * @return int Item count
     */
    public function count() {
        return count($this->alerts);
    }
    
    /**
     * @param int $index the desired item index
     * @return string|null Array item
     */
    public function item($index) {
        if(array_key_exists($index, $this->alerts)) {
            return $this->alerts[$index];
        }
        return;
    }
    
    /**
     * @return string Array as JSON text
     */
    public function toJSON() {
        return json_encode($this->alerts);
    }
    
    /**
     * @return array Raw object array 
     */
    public function toArray() {
        //return array
        return $this->alerts;
    }
}
