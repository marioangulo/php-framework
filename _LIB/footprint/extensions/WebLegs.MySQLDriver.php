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

//--> Begin Overload :: MySQLDriver
	class MySQLDriver_Ext extends MySQLDriver {
		//--> Begin :: Properties
			public $Commands;
			public $Files;
			public $SQLKeys;
		//<-- End :: Properties
		
		//####################################################################################
		
		//--> Begin :: Constructor
			public function __construct() { 
				parent::__construct(); 
				
				$this->Commands = array();
				$this->Files = array();
			}
		//<-- End :: Constructor
		
		//####################################################################################
		
		//--> Begin Method :: LoadCommand
			public function LoadCommand($Command, $Data = null) {
				$KeyBindingData = F::$SQLKeyBinders;
				if(isset($Data)) {
					$KeyBindingData = array_merge(F::$SQLKeyBinders, $Data);
				}
				
				$FilePath = F::FilePath(F::$PageNamespace .".sql.xml");
				F::Log("<sql-command name=\"". $Command ."\" file=\"". F::$PageNamespace .".sql.xml\">");
				
				//check to see if the file has already been loaded - if it has, skip loading file
				if(array_search($FilePath, $this->Files) === false){
					//make sure file exists
					if(!file_exists($FilePath)) {
						throw new Exception("Footprint.SQL.GetCommand(): '". $FilePath ."' was not found or is inaccessible.");
					}
					
					//get file as string
						//create the container
						$SQLFile = "";
						if(!is_readable($FilePath)){
							throw new Exception("Footprint.SQL.GetCommand(): '". $FilePath ."' was not found or is inaccessible.");
						}
						else{
							$SQLFile = file_get_contents($FilePath);
						}
						
						//add FilePath to files
						$this->Files[] = $FilePath;
					//end get file as string
					
					//get commands
						preg_match_all("/<command.*?id=[\"|\'](.*?)[\"|\'].*?>(.*?)<\/command>/is", $SQLFile, $Matches, PREG_PATTERN_ORDER | PCRE_MULTILINE);
						for($i = 0; $i < count($Matches[1]); $i++) {
							$CommandID = $Matches[1][$i];
							$CommandContents = $Matches[2][$i];
							
							if(array_key_exists($FilePath ."#". $CommandID, $this->Commands)){
								throw new Exception("Footprint.SQL.GetCommand(): Duplicate command '". $CommandID ."' found in file '". $FilePath ."'.");
							}
							else{
								$this->Commands[$FilePath ."#". $CommandID] = $CommandContents;	
							}
							
						}
					//end get commands
				}
				
				//check to see if command exists
				if(array_key_exists($FilePath ."#". $Command, $this->Commands)) {
					$this->SQLCommand = $this->Commands[$FilePath ."#". $Command];
					$this->BindKeys($KeyBindingData);
				}
				else{
					throw new Exception("Footprint.SQL.GetCommand(): Command '". $Command ."' not found in file '". $FilePath ."'.");
				}
				
				//allow chaining from here
				return $this;
			}
		//<-- End Method :: LoadCommand
		
		//##################################################################################
		
		//--> Begin Method :: ExecuteNonQuery
			public function ExecuteNonQuery() {
				$this->ClearKeys();
				
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
		
		//--> Begin Method :: ExecuteQuery
			public function ExecuteQuery() {
				$this->ClearKeys();
				
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
		
		//####################################################################################
		
		//--> Begin Method :: BindKeys
			public function BindKeys($Data) {
				//find all the keys
				preg_match_all("/#(.*?)#/i", F::$DB->SQLCommand, $this->SQLKeys, PREG_PATTERN_ORDER | PCRE_MULTILINE);
				
				//bind data
				for($i = 0; $i < count($this->SQLKeys[1]) ; $i++) {
					if(isset($Data[$this->SQLKeys[1][$i]])) {
						F::$DB->SQLKey("#". $this->SQLKeys[1][$i] ."#", $Data[$this->SQLKeys[1][$i]]);
					}
				}
			}
		//<-- End Method :: BindKeys
		
		//####################################################################################
		
		//--> Begin Method :: ClearKeys
			public function ClearKeys() {
				//bind data
				for($i = 0; $i < count($this->SQLKeys[1]) ; $i++) {
					F::$DB->SQLKey("#". $this->SQLKeys[1][$i] ."#", "");
				}
				
				//lets see it
				F::Log(F::$DB->SQLCommand);
				F::Log("</sql-command>");
			}
		//<-- End Method :: ClearKeys
	}
//<-- End Class :: MySQLDriver

//##########################################################################################
?>