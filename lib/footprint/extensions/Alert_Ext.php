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

class Alert_Ext extends Alert {
    /**
     * we overloaded the add method so we can more symantic error notifications
     * @param string $arg1
     * @param string $arg2
     */
    public function add($arg1 = null, $arg2 = null) {
        if(isset($arg1) && isset($arg2)) {
            $this->alerts[] = array($arg1, $arg2);
        }
        else {
            $this->alerts[] = $arg1;
        }
    }
}
