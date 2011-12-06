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

class MySQLDriver {
    //basic properties
    public $myConnection;
    public $sqlCommand;
    public $host;
    public $username;
    public $password;
    public $schema;
    
    //php specific properties
    public $resultSet;
    public $resultCount;
    
    /**
     * construct the object
     */
    public function __construct() {
        //basic properties
        $this->myConnection = null;
        $this->sqlCommand = "";
        
        //php specific properties
        $this->resultSet;
        $this->resultCount = 0;
        $this->host = "";
        $this->username = "";
        $this->password = "";
        $this->schema = "";
    }
    
    /**
     * destruct the object
     */
    public function __destruct() {
        $this->close();
    }
    
    /**
     * opens the db connection
     */
    public function open() {
        //this is for stored procedures
        if(!defined('MYSQL_MULTI_RESULTS')) {
            define("MYSQL_MULTI_RESULTS", "131072");
        }
        
        //if the connection is closed then go ahead and reopen
        if(is_null($this->myConnection)){
            //open the connection
            $this->myConnection = mysql_connect($this->host, $this->username, $this->password, false, MYSQL_MULTI_RESULTS);
            
            //select the database
            if($this->schema != "") {
                mysql_select_db($this->schema, $this->myConnection);
            }
        }
    }
    
    /**
     * closes the db connection
     */
    public function close() {
        //make sure we havent already closed
        if(!is_null($this->resultSet) && !is_null($this->myConnection)){
            if(!is_null($this->resultSet)) {
                //free result set
                mysql_free_result($this->resultSet);
            }
                    
            //close the link
            mysql_close($this->myConnection);
        }
        
        //set to null so when the destructor is executed we dont get errors
        $this->resultSet = null;
        $this->myConnection = null;
    }
    
    /**
     * escapes the string to prevent injection
     * @param string $value the value to escape
     * @return string Transformed input
     */
    public function escape($value) {
        return mysql_real_escape_string($value, $this->myConnection);
    }
    
    /**
     * replaces the sql key in the current command
     * @param string $key the key name
     * @param string $value the key value
     */
    public function sqlKey($key, $value) {
        $this->sqlCommand = str_replace($key, $this->escape($value), $this->sqlCommand);
    }
    
    /**
     * executes a non query
     */
    public function executeNonQuery() {
        if(mysql_query($this->sqlCommand, $this->myConnection)) {
            //we're good
            //return the affected row count
            return mysql_affected_rows($this->myConnection);
        }
        else{
            throw new Exception("Weblegs.MySQLDriver.executeNonQuery(): Failed to execute query. Error: ". mysql_error());
        }
    }
    
    /**
     * gets the data as a string
     * @param string $rowSeperatedBy the text to seperate rows with
     * @param string $fieldsSeperatedBy the text to seperate fields with
     * @param string $fieldsEnclosedBy the text to enclose fields with
     * @param bool $returnHeaders if we should return the headers as the first row
     * @return string The data as a string
     */
    public function getDataString($rowSeperatedBy = "", $fieldsSeperatedBy = "", $fieldsEnclosedBy = "", $returnHeaders = "") {
        //execute the query
        $this->executeQuery();
        
        //create the result container
        $myResult = "";
        
        //handle header row
        if($returnHeaders) {
            for($i = 0 ; $i < mysql_num_fields($this->resultSet) ; $i++) {
                //get the meta data
                $columnData = mysql_fetch_field($this->resultSet, $i);
                
                //add it up
                $myResult .= $fieldsEnclosedBy . $columnData->name . $fieldsEnclosedBy;
                if($i + 1 != mysql_num_fields($this->resultSet)) {
                    $myResult .= $fieldsSeperatedBy;
                }
            }
            $myResult .= $rowSeperatedBy;
        }
        
        //handle data rows
        for($i = 0 ; $i < $this->resultCount ; $i++) {
            //get the next row
            $dataRow = mysql_fetch_array($this->resultSet, MYSQL_ASSOC);
            
            for($j = 0 ; $j < mysql_num_fields($this->resultSet) ; $j++) {
                //get the meta data
                $columnData = mysql_fetch_field($this->resultSet, $j);
                
                //get the data value
                $myData = $dataRow[$columnData->name];
                
                //escape the data
                if($fieldsEnclosedBy != "") {
                    $myData = str_replace($fieldsEnclosedBy, $fieldsEnclosedBy . $fieldsEnclosedBy, $myData);
                }
                
                //add it up
                $myResult .= $fieldsEnclosedBy . $myData . $fieldsEnclosedBy;
                if($j + 1 != mysql_num_fields($this->resultSet)) {
                    $myResult .= $fieldsSeperatedBy;
                }
            }
            if($i + 1 != $this->resultCount) {
                $myResult .= $rowSeperatedBy;
            }
        }
        
        return $myResult;
    }
    
    /**
     * gets the id of the last record inserted
     * @return string The id of the last record
     */
    public function getLastInsertID() {
        return mysql_insert_id($this->myConnection);
    }
    
    /**
     * gets the found row count from the last executed query
     * @return string The count of how many rows where found
     */
    public function getFoundRows() {
        $resultSet = mysql_query("SELECT FOUND_ROWS() AS total_count;", $this->myConnection);
        $dtrPreCount = mysql_fetch_array($resultSet, MYSQL_ASSOC);
        //free the result
        mysql_free_result($resultSet);
        return $dtrPreCount["total_count"];
    }
    
    /**
     * gets the data as a hash array
     * @return array The data as an array of key/values 
     */
    public function getDataRow() {
        //execute the query
        $this->executeQuery();
        
        //get row data
        $row = mysql_fetch_array($this->resultSet, MYSQL_ASSOC);
        
        //if Row is false return null
        if($row === false){
            return null;
        }
        //return data
        else{
            return $row;
        }
    }
    
    /**
     * gets the data as an array
     * @return array The data as an array of hashes
     */
    public function getDataTable() {
        //create the array container
        $resultArray = array();
        
        //execute the query
        $this->executeQuery();
        
        //build the array
        while($myRow = mysql_fetch_array($this->resultSet, MYSQL_ASSOC)) {
            $resultArray[] = $myRow;
        }
        
        return $resultArray;
    }
    
    /**
     * gets the data as an array
     * @return array The data as a raw php array
     */
    public function getDataArray($returnHeaders = false) {
        //create the array container
        $resultArray = array();
        
        //execute the query
        $this->executeQuery();
        
        //handle header row
        if($returnHeaders) {
            //container for the header row
            $headerRow = "";
            for($i = 0 ; $i < mysql_num_fields($this->resultSet) ; $i++) {
                //get the meta data
                $columnData = mysql_fetch_field($this->resultSet, $i);
                
                //add it up
                $headerRow[] = $columnData->name;
            }
            $resultArray[] = $headerRow;
        }
        
        //handle data rows
        for($i = 0 ; $i < $this->resultCount ; $i++) {
            $resultArray[] = mysql_fetch_array($this->resultSet, MYSQL_NUM);
        }
        
        return $resultArray;
    }
    
    /**
     * executes a data query
     */
    public function executeQuery() {
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
            throw new Exception("Weblegs.MySQLDriver.executeQuery(): Failed to execute query. Error: ". mysql_error());
        }
        
        return $this->resultCount;
    }
}
