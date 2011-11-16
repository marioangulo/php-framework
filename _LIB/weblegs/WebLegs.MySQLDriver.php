<?php
//##########################################################################################

/*
Copyright (C) 2005-2011 WebLegs, Inc.
This program is free software: you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation, either
version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.
If not, see <http://www.gnu.org/licenses/>.
*/

//##########################################################################################

//--> Begin Class :: MySQLDriver
	class MySQLDriver {
		//--> Begin :: Properties
			//basic properties
			public $MyConnection;
			public $SQLCommand;
			public $Host;
			public $Username;
			public $Password;
			public $Schema;
			
			//php specific properties
			public $ResultSet;
			public $ResultCount;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function MySQLDriver() {
				//basic properties
				$this->MyConnection = null;
				$this->SQLCommand = "";
				
				//php specific properties
				$this->ResultSet;
				$this->ResultCount = 0;
				$this->Host = "";
				$this->Username = "";
				$this->Password = "";
				$this->Schema = "";
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin :: Destructor
			public function __destruct() {
				$this->Close();
			}
		//<-- End :: Destructor
				
		//##################################################################################
		
		//--> Begin Method :: Open
			public function Open() {
				//this is for stored procedures
				if(!defined('MYSQL_MULTI_RESULTS')) {
					define("MYSQL_MULTI_RESULTS","131072");
				}
				
				//if the connection is closed then go ahead and reopen
				if(is_null($this->MyConnection)){
					//open the connection
					$this->MyConnection = mysql_connect($this->Host, $this->Username, $this->Password, false, MYSQL_MULTI_RESULTS);
					
					//select the database
					if($this->Schema != "") {
						mysql_select_db($this->Schema, $this->MyConnection);
					}
				}
			}
		//<-- End Method :: Open
		
		//##################################################################################
		
		//--> Begin Method :: Close
			public function Close() {
				//make sure we havent already closed
				if(!is_null($this->ResultSet) && !is_null($this->MyConnection)){
					if(!is_null($this->ResultSet)) {
						//free result set
						mysql_free_result($this->ResultSet);
					}
							
					//close the link
					mysql_close($this->MyConnection);
				}
				
				//set to null so when the destructor is executed we dont get errors
				$this->ResultSet = null;
				$this->MyConnection = null;
			}
		//<-- End Method :: Close
		
		//##################################################################################
		
		//--> Begin Method :: Escape
			public function Escape($Value) {
				return mysql_real_escape_string($Value, $this->MyConnection);
			}
		//<-- End Method :: Escape
		
		//##################################################################################
		
		//--> Begin Method :: SQLKey
			public function SQLKey($Key, $Value) {
				$this->SQLCommand = str_replace($Key, $this->Escape($Value), $this->SQLCommand);
			}
		//<-- End Method :: SQLKey
		
		//##################################################################################
		
		//--> Begin Method :: ExecuteNonQuery
			public function ExecuteNonQuery() {
				if(mysql_query($this->SQLCommand, $this->MyConnection)) {
					//we're good
					//return the affected row count
					return mysql_affected_rows($this->MyConnection);
				}
				else{
					throw new Exception("WebLegs.MySQLDriver.ExecuteNonQuery(): Failed to execute query. Error: ". mysql_error());
				}
			}
		//<-- End Method :: ExecuteNonQuery
		
		//##################################################################################
		
		//--> Begin Method :: GetDataString
			public function GetDataString($RowSeperatedBy = "", $FieldsSeperatedBy = "", $FieldsEnclosedBy = "", $ReturnHeaders = "") {
				//execute the query
				$this->ExecuteQuery();
				
				//create the result container
				$MyResult = "";
				
				//handle header row
				if($ReturnHeaders) {
					for($i = 0 ; $i < mysql_num_fields($this->ResultSet) ; $i++) {
						//get the meta data
						$ColumnData = mysql_fetch_field($this->ResultSet, $i);
						
						//add it up
						$MyResult .= $FieldsEnclosedBy . $ColumnData->name . $FieldsEnclosedBy;
						if($i + 1 != mysql_num_fields($this->ResultSet)) {
							$MyResult .= $FieldsSeperatedBy;
						}
					}
					$MyResult .= $RowSeperatedBy;
				}
				
				//handle data rows
				for($i = 0 ; $i < $this->ResultCount ; $i++) {
					//get the next row
					$DataRow = mysql_fetch_array($this->ResultSet, MYSQL_ASSOC);
					
					for($j = 0 ; $j < mysql_num_fields($this->ResultSet) ; $j++) {
						//get the meta data
						$ColumnData = mysql_fetch_field($this->ResultSet, $j);
						
						//get the data value
						$MyData = $DataRow[$ColumnData->name];
						
						//escape the data
						if($FieldsEnclosedBy != "") {
							$MyData = str_replace($FieldsEnclosedBy, $FieldsEnclosedBy . $FieldsEnclosedBy, $MyData);
						}
						
						//add it up
						$MyResult .= $FieldsEnclosedBy . $MyData . $FieldsEnclosedBy;
						if($j + 1 != mysql_num_fields($this->ResultSet)) {
							$MyResult .= $FieldsSeperatedBy;
						}
					}
					if($i + 1 != $this->ResultCount) {
						$MyResult .= $RowSeperatedBy;
					}
				}
				
				return $MyResult;
			}
		//<-- End Method :: GetDataString
		
		//##################################################################################
		
		//--> Begin Method :: GetLastInsertID
			public function GetLastInsertID() {
				return mysql_insert_id($this->MyConnection);
			}
		//<-- End Method :: GetLastInsertID
		
		//##################################################################################
		
		//--> Begin Method :: GetFoundRows
			public function GetFoundRows() {
				$ResultSet = mysql_query("SELECT FOUND_ROWS() AS total_count;", $this->MyConnection);
				$dtrPreCount = mysql_fetch_array($ResultSet, MYSQL_ASSOC);
				//free the result
				mysql_free_result($ResultSet);
				return $dtrPreCount["total_count"];
			}
		//<-- End Method :: GetFoundRows
		
		//##################################################################################
		
		//--> Begin Method :: GetDataRow
			public function GetDataRow() {
				//execute the query
				$this->ExecuteQuery();
				
				//get row data
				$Row = mysql_fetch_array($this->ResultSet, MYSQL_ASSOC);
				
				//if Row is false return null
				if($Row === false){
					return null;
				}
				//return data
				else{
					return $Row;
				}
			}
		//<-- End Method :: GetDataRow
		
		//##################################################################################
		
		//--> Begin Method :: GetDataTable
			public function GetDataTable() {
				//create the array container
				$ResultArray = array();
				
				//execute the query
				$this->ExecuteQuery();
				
				//build the array
				while($MyRow = mysql_fetch_array($this->ResultSet, MYSQL_ASSOC)) {
					$ResultArray[] = $MyRow;
				}
				
				return $ResultArray;
			}
		//<-- End Method :: GetDataTable
		
		//##################################################################################
		
		//--> Begin Method :: GetDataArray
			public function GetDataArray($ReturnHeaders = false) {
				//create the array container
				$ResultArray = array();
				
				//execute the query
				$this->ExecuteQuery();
				
				//handle header row
				if($ReturnHeaders) {
					//container for the header row
					$HeaderRow = "";
					for($i = 0 ; $i < mysql_num_fields($this->ResultSet) ; $i++) {
						//get the meta data
						$ColumnData = mysql_fetch_field($this->ResultSet, $i);
						
						//add it up
						$HeaderRow[] = $ColumnData->name;
					}
					$ResultArray[] = $HeaderRow;
				}
				
				//handle data rows
				for($i = 0 ; $i < $this->ResultCount ; $i++) {
					$ResultArray[] = mysql_fetch_array($this->ResultSet, MYSQL_NUM);
				}
				
				return $ResultArray;
			}
		//<-- End Method :: GetDataArray
	
		//##################################################################################
		
		//--> Begin Method :: ExecuteQuery
			public function ExecuteQuery() {
				if(!is_null($this->ResultSet)) {
					//free the last result set
					mysql_free_result($this->ResultSet);
				}
				
				//get the new results
				if($this->ResultSet = mysql_query($this->SQLCommand, $this->MyConnection)) {
					//we're good
					$this->ResultCount = mysql_num_rows($this->ResultSet);
				}
				else{
					throw new Exception("WebLegs.MySQLDriver.ExecuteQuery(): Failed to execute query. Error: ". mysql_error());
				}
				
				return $this->ResultCount;
			}
		//<-- End Method :: ExecuteQuery
	}
//<-- End Class :: MySQLDriver

//##########################################################################################
?>