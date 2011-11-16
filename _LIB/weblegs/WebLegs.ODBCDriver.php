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

//--> Begin Class :: ODBCDriver
	class ODBCDriver {
		//--> Begin :: Properties
			public $MyConnection;
			public $SQLCommand;
			public $ConnectionString;
			public $Host;
			public $Username;
			public $Password;
			public $Schema;
			public $ResultSet;
			public $ResultCount;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function ODBCDriver($ConnectionString = "") {
				$this->MyConnection = null;
				$this->SQLCommand = "";
				$this->ConnectionString = $ConnectionString;
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
				//if the connection is null go ahead and open
				if(is_null($this->MyConnection)){
					if(strlen($this->ConnectionString) == 0) {
						$this->ConnectionString += "server=". $this->Host .";";
						$this->ConnectionString += "uid=". $this->Username .";";
						$this->ConnectionString += "pwd=". $this->Password .";";
						$this->ConnectionString += "database=". $this->Schema .";";
					}
					$this->MyConnection = odbc_connect($this->ConnectionString, $this->Username, $this->Password);
				}
			}
		//<-- End Method :: Open
		
		//##################################################################################
		
		//--> Begin Method :: Close
			public function Close() {
				//make sure we havent already closed
				if(!is_null($this->MyConnection)){
					odbc_close($this->MyConnection);
				}
				
				//set to null so when the destructor is executed we dont get errors
				$this->MyConnection = null;
			}
		//<-- End Method :: Close
		
		
		//##################################################################################
		
		//--> Begin Method :: Escape
			public function Escape($Value) {
				return str_replace("'", "''", str_replace("\\", "\\\\", $Value));
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
				//we dont need to keep track of this
				$TmpResultSet = "";
				if($TmpResultSet = odbc_exec($this->MyConnection, $this->SQLCommand)) {
					//we're good
					//return the affected row count - this could have 
					//diffrent behavior based upon the database used
					return odbc_num_rows($TmpResultSet);
				}
				else{
					throw new Exception("WebLegs.ODBCDriver.ExecuteNonQuery(): Failed to execute query. Error: ". odbc_error());
				}
			}
		//<-- End Method :: ExecuteNonQuery
		
		//##################################################################################
		
		//--> Begin Method :: GetDataString
			public function GetDataString($RowSeperatedBy, $FieldsSeperatedBy, $FieldsEnclosedBy, $ReturnHeaders) {
				//execute the query
				$this->ExecuteQuery();
				
				//create the result container
				$MyResult = "";
				
				//handle header row
				if($ReturnHeaders) {
					for($i = 0 ; $i < odbc_num_fields($this->ResultSet) ; $i++) {
						
						//add it up
						$MyResult .= $FieldsEnclosedBy . odbc_field_name($this->ResultSet, $j) . $FieldsEnclosedBy;
						if($i + 1 != odbc_num_fields($this->ResultSet)) {
							$MyResult .= $FieldsSeperatedBy;
						}
					}
					$MyResult .= $RowSeperatedBy;
				}
				
				//handle data rows
				for($i = 0 ; $i < $this->ResultCount ; $i++) {
					//get the next row
					$DataRow = odbc_fetch_array($this->ResultSet, MYSQL_ASSOC);
					
					for($j = 0 ; $j < odbc_num_fields($this->ResultSet) ; $j++) {
						
						//get the data value
						$MyData = $DataRow[odbc_field_name($this->ResultSet, $j)];
						
						//escape the data
						if($FieldsEnclosedBy != "") {
							$MyData = str_replace($FieldsEnclosedBy, $FieldsEnclosedBy . $FieldsEnclosedBy, $MyData);
						}
						
						//add it up
						$MyResult .= $FieldsEnclosedBy . $MyData . $FieldsEnclosedBy;
						if($j + 1 != odbc_num_fields($this->ResultSet)) {
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
				$ResultSet = odbc_exec($this->MyConnection, "SELECT LAST_INSERT_ID();");
				$Row = odbc_fetch_array($ResultSet);
				return $Row["LAST_INSERT_ID()"];
			}
		//<-- End Method :: GetLastInsertID
		
		//##################################################################################
		
		//--> Begin Method :: GetFoundRows
			public function GetFoundRows() {
				$ResultSet = odbc_exec($this->MyConnection, "SELECT FOUND_ROWS();");
				$Row = odbc_fetch_array($ResultSet);
				return $Row["FOUND_ROWS()"];
			}
		//<-- End Method :: GetFoundRows
		
		//##################################################################################
		
		//--> Begin Method :: GetDataRow
			public function GetDataRow() {
				//execute the query
				$this->ExecuteQuery();
				
				//get row data
				$Row = odbc_fetch_array($this->ResultSet);
				
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
				while($MyRow = odbc_fetch_array($this->ResultSet)) {
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
					for($i = 0 ; $i < odbc_num_fields($this->ResultSet) ; $i++) {
						//add it up
						$HeaderRow[] = odbc_field_name($this->ResultSet, $i);
					}
					$ResultArray[] = $HeaderRow;
				}
				
				//handle data rows
				for($i = 0 ; $i < $this->ResultCount ; $i++) {
					$ResultArray[] = odbc_fetch_array($this->ResultSet, MYSQL_NUM);
				}
				
				return $ResultArray;
			}
		//<-- End Method :: GetDataArray
		
		//##################################################################################
		
		//--> Begin Method :: ExecuteQuery
			public function ExecuteQuery() {
				if(!is_null($this->ResultSet)) {
					//free the last result set
					odbc_free_result($this->ResultSet);
				}
				
				//get the new results
				if($this->ResultSet = odbc_exec($this->MyConnection, $this->SQLCommand)) {
					//we're good
					//return the affected row count - this could have 
					//diffrent behavior based upon the database used
					$this->ResultCount = odbc_num_rows($this->ResultSet);
				}
				else{
					throw new Exception("WebLegs.ODBCDriver.ExecuteQuery(): Failed to execute query. Error: ". odbc_error($this->MyConnection));
				}
				
				return $this->ResultCount;
			}
		//<-- End Method :: ExecuteQuery
	}
//<-- End Class :: ODBCDriver

//##########################################################################################
?>