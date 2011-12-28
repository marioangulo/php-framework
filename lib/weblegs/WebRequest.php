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

require_once("WebRequestFile.php");

class WebRequest {
    public $files;
    public $formArray;
    public $rawFormString;
    public $queryStringArray;
    public $rawQueryString;
    public $maxRequestLength;
    
    /**
     * construct the object
     */
    public function __construct() {
        //set maxRequestLength default - 5mb
        $this->maxRequestLength = 1024 * 5000;
        
        //check content length - if its too large throw an error
        if((int)$this->serverVariables("CONTENT_LENGTH") > (int)$this->maxRequestLength){
            throw new Exception("Weblegs.WebRequest.constructor: Request length too large. maximum request length is set to '". $this->maxRequestLength ."'. (413 Request entity too large)");
        }
        
        //handle uploaded files
        foreach ($_FILES as $key => $value) {
            if($_FILES[$key]["error"] == 0){
                $thisFile = new WebRequestFile();
                $thisFile->formName = $key;
                $thisFile->fileName = $_FILES[$key]["name"];
                $thisFile->filePath = $_FILES[$key]["tmp_name"];
                $thisFile->contentType = $_FILES[$key]["type"];
                $thisFile->contentLength = $_FILES[$key]["size"];        
                $this->files[] = $thisFile;
            }
        }
        
        //get raw post data
        $this->rawFormString = file_get_contents("php://input");
        $this->formArray = array();
        
        if(strlen($this->rawFormString) > 0) {
            //explode by name=value&name=value
            $formDataArr = explode("&", $this->rawFormString);
            foreach($formDataArr as $key => $value) {
                //split name value pairs name=values
                $pair = explode("=", $value);
                if(array_key_exists($pair[0], $this->formArray)) {
                    $this->formArray[$pair[0]] .= ",". Codec::urlDecode($pair[1]);
                }
                else{
                    if(count($pair) > 1) {
                        $this->formArray[$pair[0]] = Codec::urlDecode($pair[1]);
                    }
                }
            }
        }
        else{
            $this->formArray = $_POST;
        }
        
        if(count($this->formArray) > 0){
            $this->rawFormString = "";
            foreach($this->formArray as $key => $value){
                $this->rawFormString .= "&". $key ."=". Codec::urlEncode($value);
            }
            $this->rawFormString = substr($this->rawFormString, 1);
        }
        
        //get raw post data
        $this->rawQueryString = $_SERVER["QUERY_STRING"];
        $this->queryStringArray = array();
        
        if(strlen($this->rawQueryString) > 0) {
            //explode by name=value&name=value
            $queryDataArr = explode("&", $this->rawQueryString);
            foreach($queryDataArr as $key => $value) {
                //split name value pairs name=values
                $pair = explode("=", $value);
                if(array_key_exists($pair[0], $this->queryStringArray)) {
                    $this->queryStringArray[$pair[0]] .= ",". Codec::urlDecode($pair[1]);
                }
                else{
                    if(count($pair) > 1) {
                        $this->queryStringArray[$pair[0]] = Codec::urlDecode($pair[1]);
                    }
                }
            }
        }
    }
    
    /**
     * gets http post input data
     * @param string $key
     * @param string $default
     * @return string|null The requested data
     */
    public function form($key = null, $default = null) {
        if(is_null($key)) {
            //should we use the default?
            if(!is_null($default) && $this->rawFormString == "") {
                return $default;
            }
            return $this->rawFormString;
        }
        else if(isset($this->formArray[$key])) {
            return $this->formArray[$key];
        }
        else {
            return null;
        }
    }
    
    /**
     * gets http get input data
     * @param string $key
     * @param string $default
     * @return string|null The requested data
     */
    public function queryString($key = null, $default = null) {
        if(is_null($key)) {
            return $this->rawQueryString;
        }
        else {
            //should we use the default?
            if(!is_null($default) && !isset($this->queryStringArray[$key])) {
                return $default;
            }
            else {
                if(isset($this->queryStringArray[$key])) {
                    return $this->queryStringArray[$key];
                }
                else {
                    return null;
                }
            }
        }
    }
    
    /**
     * gets http input data by key (http get data priority by default)
     * @param string $key
     * @param string $default
     * @param bool $formFirst
     * @return string|null The requested data
     */
    public function input($key, $default = "", $formFirst = false) {
        //container
        $value = null;
        
        if($formFirst) {
            $value = isset($this->formArray[$key]) ? $this->formArray[$key] : null;
            if(is_null($value)) {
                $value = isset($this->queryStringArray[$key]) ? $this->queryStringArray[$key] : null;
            }
        }
        else {
            $value = isset($this->queryStringArray[$key]) ? $this->queryStringArray[$key] : null;
            if(is_null($value)) {
                $value = isset($this->formArray[$key]) ? $this->formArray[$key] : null;
            }
        }
        
        if(is_null($value)) {
            $value = $default;
        }
        
        return $value;
    }
    
    /**
     * gets http posted file
     * @param string $key
     * @return binary|null The file
     */
    public function file($key) {
        for($i = 0; $i < count($this->files); $i++) {
            if($this->files[$i]->formName == $key){
                return $this->files[$i];
            }
        }
        return null;
    }
    
    /**
     * gets server environment variables by key
     * @param string $key
     * @return string|null The data
     */
    public function serverVariables($key) {
        if(isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        else {
            return null;
        }
    }
    
    /**
     * gets server session data by key
     * @param string $key
     * @return string|null The data
     */
    public function session($key) {
        if(isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        else {
            return null;
        }
    }
    
    /**
     * gets client cookie data by key
     * @param string $key
     * @return binary|null The file
     */
    public function cookies($key) {
        if(isset($_COOKIE[$key])) {
            return $_COOKIE[$key];
        }
        else {
            return null;
        }
    }
    
    /**
     * gets client header data by key
     * @param string $key
     * @return binary|null The file
     */
    public function header($key) {
        //get token ready for cgi variables
        $key = "HTTP_". strtoupper(str_replace("-", "_", $key));
        if(isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        else {
            return null;
        }
    }
}
