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

class TextChunk {
//--> Begin :: Properties
    public $blank;
    public $current;
    public $all;
    
    /**
     * construct the object
     * @param ref string $source
     * @param ref string $start
     * @param ref string $end
     */
    public function __construct(&$source = "", $start = "", $end = "") {
        $this->blank = "";
        $this->current = "";
        $this->all = "";
        
        //get arg count
        $argCount = func_num_args();
        
        //how many args?
        if($argCount == 0) {
            //do nothing
        }
        else if($argCount == 3) {
            $myStart = 0;
            $myEnd = 0;
            
            if(strpos($source, $start) != false && strpos($source, $end) != false) {
                $myStart = (strpos($source, $start)) + strlen($start);
                $myEnd = strpos($source, $end);
                
                try {
                    $this->blank = substr($source, $myStart, $myEnd - $myStart);
                }
                catch(Exception $e) {
                    throw new Exception("Weblegs.TextChunk.constructor(): Boundry string mismatch.");
                }
            }
            else {
                throw new Exception("Weblegs.TextChunk.constructor(): Boundry strings not present in source string.");
            }
        }
    }
    
    /**
     * starts a new chunk
     */
    public function begin() {
        $this->current = $this->blank;
    }
    
    /**
     * ends the current chunk
     */
    public function end() {
        $this->all .= $this->current;
    }
    
    /**
     * replaces text in the chunk
     */
    public function replace($oldText, $newText) {
        $this->current = str_replace($oldText, $newText, $this->current);
        return $this;
    }
    
    /**
     * returns the chunks as a string
     * @return string The chunks
     */
    public function toString() {
        return $this->all;
    }
}
