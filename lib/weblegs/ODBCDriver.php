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

class ODBCDriver {
    public $myConnection;
    public $sqlCommand;
    public $connectionString;
    public $host;
    public $username;
    public $password;
    public $schema;
    public $resultSet;
    public $resultCount;
    
    /**
     * construct the object
     */
    public function __construct($connectionString = "") {
        $this->myConnection = null;
        $this->sqlCommand = "";
        $this->connectionString = $connectionString;
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
        //if the connection is null go ahead and open
        if(is_null($this->myConnection)){
            if(strlen($this->connectionString) == 0) {
                $this->connectionString += "server=". $this->host .";";
                $this->connectionString += "uid=". $this->username .";";
                $this->connectionString += "pwd=". $this->password .";";
                $this->connectionString += "database=". $this->schema .";";
            }
            $this->myConnection = odbc_connect($this->connectionString, $this->username, $this->password);
        }
    }
    
    /**
     * closes the db connection
     */
    public function close() {
        //make sure we havent already closed
        if(!is_null($this->myConnection)){
            odbc_close($this->myConnection);
        }
        
        //set to null so when the destructor is executed we dont get errors
        $this->myConnection = null;
    }
    
    /**
     * escapes the string to prevent injection
     * @param string $value the value to escape
     * @return string Transformed input
     */
    public function escape($value) {
        return str_replace("'", "''", str_replace("\\", "\\\\", $value));
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
        //we dont need to keep track of this
        $tmpResultSet = "";
        if($tmpResultSet = odbc_exec($this->myConnection, $this->sqlCommand)) {
            //we're good
            //return the affected row count - this could have 
            //diffrent behavior based upon the database used
            return odbc_num_rows($tmpResultSet);
        }
        else{
            throw new Exception("Weblegs.ODBCDriver.executeNonQuery(): Failed to execute query. Error: ". odbc_error());
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
            for($i = 0 ; $i < odbc_num_fields($this->resultSet) ; $i++) {
                
                //add it up
                $myResult .= $fieldsEnclosedBy . odbc_field_name($this->resultSet, $j) . $fieldsEnclosedBy;
                if($i + 1 != odbc_num_fields($this->resultSet)) {
                    $myResult .= $fieldsSeperatedBy;
                }
            }
            $myResult .= $rowSeperatedBy;
        }
        
        //handle data rows
        for($i = 0 ; $i < $this->resultCount ; $i++) {
            //get the next row
            $dataRow = odbc_fetch_array($this->resultSet, MYSQL_ASSOC);
            
            for($j = 0 ; $j < odbc_num_fields($this->resultSet) ; $j++) {
                
                //get the data value
                $myData = $dataRow[odbc_field_name($this->resultSet, $j)];
                
                //escape the data
                if($fieldsEnclosedBy != "") {
                    $myData = str_replace($fieldsEnclosedBy, $fieldsEnclosedBy . $fieldsEnclosedBy, $myData);
                }
                
                //add it up
                $myResult .= $fieldsEnclosedBy . $myData . $fieldsEnclosedBy;
                if($j + 1 != odbc_num_fields($this->resultSet)) {
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
        $resultSet = odbc_exec($this->myConnection, "SELECT LAST_INSERT_ID();");
        $row = odbc_fetch_array($resultSet);
        return $row["LAST_INSERT_ID()"];
    }
    
    /**
     * gets the found row count from the last executed query
     * @return string The count of how many rows where found
     */
    public function getFoundRows() {
        $resultSet = odbc_exec($this->myConnection, "SELECT FOUND_ROWS();");
        $row = odbc_fetch_array($resultSet);
        return $row["FOUND_ROWS()"];
    }
    
    /**
     * gets the data as a hash array
     * @return array The data as an array of key/values 
     */
    public function getDataRow() {
        //execute the query
        $this->executeQuery();
        
        //get row data
        $row = odbc_fetch_array($this->resultSet);
        
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
        while($myRow = odbc_fetch_array($this->resultSet)) {
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
            for($i = 0 ; $i < odbc_num_fields($this->resultSet) ; $i++) {
                //add it up
                $headerRow[] = odbc_field_name($this->resultSet, $i);
            }
            $resultArray[] = $headerRow;
        }
        
        //handle data rows
        for($i = 0 ; $i < $this->resultCount ; $i++) {
            $resultArray[] = odbc_fetch_array($this->resultSet, MYSQL_NUM);
        }
        
        return $resultArray;
    }
    
    /**
     * executes a data query
     */
    public function executeQuery() {
        if(!is_null($this->resultSet)) {
            //free the last result set
            odbc_free_result($this->resultSet);
        }
        
        //get the new results
        if($this->resultSet = odbc_exec($this->myConnection, $this->sqlCommand)) {
            //we're good
            //return the affected row count - this could have 
            //diffrent behavior based upon the database used
            $this->resultCount = odbc_num_rows($this->resultSet);
        }
        else{
            throw new Exception("Weblegs.ODBCDriver.executeQuery(): Failed to execute query. Error: ". odbc_error($this->myConnection));
        }
        
        return $this->resultCount;
    }
}
