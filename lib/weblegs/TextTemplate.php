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

class TextTemplate {
    public $source;
    public $dtd;
    
    /**
     * construct the object
     */
    public function __construct() {
        $this->dtd = "";
    }
    
    /**
     * loads the specified file path
     * @param string $path
     * @param string $rootPath where we can find xslt
     * @return this Object chaining
     */
    public function loadFile($path, $rootPath = null) {
        //make sure file exists
        if(!file_exists($path)){
            throw new Exception("Weblegs.TextTemplate.loadFile(): File not found or not able to access.");
        }
    
        //load up file
        $this->load(file_get_contents($path), $rootPath);            
        return $this;
    }
    
    /**
     * loads the specified string
     * @param string $source
     * @param string $rootPath where we can find xslt
     * @return this Object chaining
     */
    public function load($source, $rootPath = null) {
        if(is_null($rootPath)) {
            $this->$source = $source;
            return $this;
        }
        //see if there is any stylesheets
        else if(strpos($source, "xml-stylesheet") == false) {
            $this->$source = $source;
            return $this;
        }
        
        //find the xsl style sheet path in our document
        preg_match("/xml-stylesheet.*?href=[\"|\'](.*?)[\"|\']/", $source, $matches);
        $xsltPath = $matches[1];
        
        //get dtd
        preg_match("/(<!DOCTYPE.*?>)/", $source, $matches);
        if(count($matches) > 0){
            $this->dtd = $matches[1];
            
            //strip out dtd
            $source = str_replace($this->dtd, "", $source);
        }
        
        //loat xml source
        $xmlDoc = new DomDocument();
        @$xmlDoc->loadHTML($source);
        
        //create a xslt document
        $xsltDoc = new DomDocument();
        $xsltDoc->load($rootPath . $xsltPath);
        
        //create an xslt processor and load style sheet
        $xsltProcess = new XSLTProcessor();
        $xsltProcess->importStylesheet($xsltDoc);
        
        //transform the xml and load up our dom object
        $this->$source = $xsltProcess->transformToXML($xmlDoc);
        
        return $this;
    }
    
    /**
     * performs a string replacement
     * @param string $search
     * @param string $replace
     * @return this Object chaining
     */
    public function replace($search, $replace) {
        $this->$source = str_replace($search, $replace, $this->$source);
        return $this;
    } 
    
    /**
     * gets a sub string
     * @param string $start
     * @param string $end
     * @return string The sub string
     */
    public function getSubString($start, $end) {
        $myStart = 0;
        $myEnd = 0;
        
        if(stripos($this->$source, $start) != false && strripos($this->$source, $end) != false) {
            $myStart = (stripos($this->$source, $start)) + strlen($start);
            $myEnd = strripos($this->$source, $end);
            try {
                return substr($this->$source, $myStart, $myEnd - $myStart);
            }
            catch(Exception $e) {
                throw new Exception("Weblegs.TextTemplate.getSubString: Boundry string mismatch.");
            }
        }
        else {
            throw new Exception("Weblegs.TextTemplate.getSubString: Boundry strings not present in source string.");
        }
    }
    
    /**
     * removes a sub string
     * @param string $start
     * @param string $end
     * @param bool $removeKeys
     * @return this Object chaining
     */
    public function removeSubString($start, $end, $removeKeys = false) {
        try {
            $subString = $this->getSubString($start, $end);
        }
        catch(Exception $e) {
            throw new Exception("Weblegs.TextTemplate.removeSubString(): Boundry string mismatch.");
        }
        
        //remove substring
        $this->replace($subString, "");
        
        //should we remove the keys too?
        if($removeKeys) {
            $this->replace($start, "");
            $this->replace($end, "");
        }
        return $this;
    }
    
    /**
     * returns a string representation of the template
     * @return string Transformed template
     */
    public function toString() {
        return $this->dtd . $this->$source;
    } 
    
    /**
     * saves the toString result to a path
     * @param string $path the path to save to
     */
    public function saveAs($path) {
        if(file_put_contents($path, $this->toString()) == false){
             throw new Exception("Weblegs.TextTemplate.saveAs(): Unable to save file.");
        }
    } 
}
