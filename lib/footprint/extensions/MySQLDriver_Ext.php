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

class MySQLDriver_Ext extends MySQLDriver {
    public $commands;
    public $files;
    public $sqlKeys;
    public $keyBinders;
    
    /**
     * construct the object
     */
    public function __construct() { 
        parent::__construct(); 
        
        $this->commands = array();
        $this->files = array();
        $this->keybinders = array();
    }
    
    /**
     * loads an sql command in this namespace
     * @param string $command
     * @param string $data
     * @return this Object chaining
     */
    public function loadCommand($command, $data = null) {
        $keybindingData = $this->keybinders;
        
        if(isset($data)) {
            $keybindingData = array_merge($this->keybinders, $data);
        }
        
        $filePath = F::filePath(F::$engineNamespace .".sql.xml");
        F::sysLog("<sql-command name=\"". $command ."\" file=\"". F::$engineNamespace .".sql.xml\">");
        
        //check to see if the file has already been loaded - if it has, skip loading file
        if(array_search($filePath, $this->files) === false){
            //make sure file exists
            if(!file_exists($filePath)) {
                throw new Exception("Footprint.MySQLDriver_Ext.loadCommand(): '". $filePath ."' was not found or is inaccessible.");
            }
            
            //get file as string
                //create the container
                $sqlFile = "";
                if(!is_readable($filePath)){
                    throw new Exception("Footprint.MySQLDriver_Ext.loadCommand(): '". $filePath ."' was not found or is inaccessible.");
                }
                else{
                    $sqlFile = file_get_contents($filePath);
                }
                
                //add FilePath to files
                $this->files[] = $filePath;
            //end get file as string
            
            //get commands
                preg_match_all("/<command.*?id=[\"|\'](.*?)[\"|\'].*?>(.*?)<\/command>/is", $sqlFile, $matches, PREG_PATTERN_ORDER | PCRE_MULTILINE);
                for($i = 0; $i < count($matches[1]); $i++) {
                    $commandID = $matches[1][$i];
                    $commandContents = $matches[2][$i];
                    
                    if(array_key_exists($filePath ."#". $commandID, $this->commands)){
                        throw new Exception("Footprint.MySQLDriver_Ext.loadCommand(): Duplicate command '". $commandID ."' found in file '". $filePath ."'.");
                    }
                    else{
                        $this->commands[$filePath ."#". $commandID] = $commandContents;    
                    }
                    
                }
            //end get commands
        }
        
        //check to see if command exists
        if(array_key_exists($filePath ."#". $command, $this->commands)) {
            $this->sqlCommand = $this->commands[$filePath ."#". $command];
            $this->bindKeys($keybindingData);
        }
        else{
            throw new Exception("Footprint.MySQLDriver_Ext.loadCommand(): command '". $command ."' not found in file '". $filePath ."'.");
        }
        
        //allow chaining from here
        return $this;
    }
    
    /**
     * executes a non query
     */
    public function executeNonQuery() {
        $this->clearKeys();
        
        if(mysql_query($this->sqlCommand, $this->myConnection)) {
            //we're good
            //return the affected row count
            return mysql_affected_rows($this->myConnection);
        }
        else{
            throw new Exception("Footprint.MySQLDriver_Ext.executeNonQuery(): Failed to execute query. Error: ". mysql_error());
        }
    }
    
    /**
     * executes a query
     * @return int the count of the results
     */
    public function executeQuery() {
        $this->clearKeys();
        
        if(!is_null($this->resultSet)) {
            //free the last result set
            mysql_free_result($this->resultSet);
        }
        
        //get the new results
        if($this->resultSet = mysql_query($this->sqlCommand, $this->myConnection)) {
            //we're good
            $this->resultCount = mysql_num_rows($this->resultSet);
        }
        else{
            throw new Exception("Footprint.MySQLDriver_Ext.executeQuery(): Failed to execute query. Error: ". mysql_error());
        }
        
        return $this->resultCount;
    }
    
    /**
     * binds data hash keys to sql text keys
     * @param array $data
     * @return this Object chaining
     */
    public function bindKeys($data) {
        //merge with internal keys
        $data = array_merge($data, $this->keyBinders);
        
        //find all the keys
        preg_match_all("/#(.*?)#/i", $this->sqlCommand, $this->sqlKeys, PREG_PATTERN_ORDER | PCRE_MULTILINE);
        
        //bind data
        for($i = 0; $i < count($this->sqlKeys[1]) ; $i++) {
            if(isset($data[$this->sqlKeys[1][$i]])) {
                $this->sqlKey("#". $this->sqlKeys[1][$i] ."#", $data[$this->sqlKeys[1][$i]]);
            }
        }
    }
    
    /**
     * clears any remaining text keys in the sql command before execution
     */
    public function clearKeys() {
        //bind data
        for($i = 0; $i < count($this->sqlKeys[1]) ; $i++) {
            $this->sqlKey("#". $this->sqlKeys[1][$i] ."#", "");
        }
        
        //lets see it
        F::sysLog($this->sqlCommand);
        F::sysLog("</sql-command>");
    }
}
