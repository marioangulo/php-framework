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

class DateTimeDriver_Ext extends DateTimeDriver {
    /**
     * this gives us a shortcut to a mysql/db ready timestamp 
     * @param string $format
     * @return string Transformed input
     */
    public function toSQLString($format = "yyyy-MM-dd HH:mm:ss") {
        $output = $this->toString($format);
        
        //make the min value nothing
        if($this->toString("yyyy") == "1901") {
            $output = preg_replace("/(\d)/", "0", $output);
        }
        
        return $output;
    }
    
    /**
     * this gives you an extended date time driver
     * @return DateTimeDriver_Ext The object
     */
    function now() {
        return new DateTimeDriver_Ext();
    }
}
