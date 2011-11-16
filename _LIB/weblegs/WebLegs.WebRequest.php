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

require_once("WebLegs.WebRequestFile.php");

//--> Begin Class :: WebRequest
	class WebRequest {
		//--> Begin :: Properties
			public $Files;
			public $FormArray;
			public $RawFormString;
			public $QueryStringArray;
			public $RawQueryString;
			public $MaxRequestLength;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function WebRequest() {
				//set MaxRequestLength default - 5mb
				$this->MaxRequestLength = 1024 * 5000;
				
				//check content length - if its too large throw an error
				if((int)$this->ServerVariables("CONTENT_LENGTH") > (int)$this->MaxRequestLength){
					throw new Exception("WebLegs.WebRequest.Constructor: Request length too large. Maximum request length is set to '". $this->MaxRequestLength ."'. (413 Request entity too large)");
				}
			
				//handle uploaded files
				foreach ($_FILES as $Key => $Value) {
					if($_FILES[$Key]["error"] == 0){
						$ThisFile = new WebRequestFile();
						$ThisFile->FormName = $Key;
						$ThisFile->FileName = $_FILES[$Key]["name"];
						$ThisFile->ContentType = $_FILES[$Key]["type"];
						$ThisFile->ContentLength = $_FILES[$Key]["size"];		
						$this->Files[] = $ThisFile;
					}
				}
				
				//get raw post data
				$this->RawFormString = file_get_contents("php://input");
				$this->FormArray = array();
				
				if(strlen($this->RawFormString) > 0) {
					//explode by name=value&name=value
					$FormDataArr = explode("&", $this->RawFormString);
					foreach($FormDataArr as $Key => $Value) {
						//split name value pairs name=values
						$Pair = explode("=", $Value);
						if(array_key_exists($Pair[0], $this->FormArray)) {
							$this->FormArray[$Pair[0]] .= ",". Codec::URLDecode($Pair[1]);
						}
						else{
							if(count($Pair) > 1) {
								$this->FormArray[$Pair[0]] = Codec::URLDecode($Pair[1]);
							}
						}
					}
				}
				else{
					$this->FormArray = $_POST;
				}
				
				if(count($this->FormArray) > 0){
					$this->RawFormString = "";
					foreach($this->FormArray as $Key => $Value){
						$this->RawFormString .= "&". $Key ."=". Codec::URLEncode($Value);
					}
					$this->RawFormString = substr($this->RawFormString, 1);
				}
				
				//get raw post data
				$this->RawQueryString = $_SERVER["QUERY_STRING"];
				$this->QueryStringArray = array();
				
				if(strlen($this->RawQueryString) > 0) {
					//explode by name=value&name=value
					$QueryDataArr = explode("&", $this->RawQueryString);
					foreach($QueryDataArr as $Key => $Value) {
						//split name value pairs name=values
						$Pair = explode("=", $Value);
						if(array_key_exists($Pair[0], $this->QueryStringArray)) {
							$this->QueryStringArray[$Pair[0]] .= ",". Codec::URLDecode($Pair[1]);
						}
						else{
							if(count($Pair) > 1) {
								$this->QueryStringArray[$Pair[0]] = Codec::URLDecode($Pair[1]);
							}
						}
					}
				}
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: Form
			public function Form($Key = null, $Default = null) {
				if(is_null($Key)) {
					//should we use the default?
					if(!is_null($Default) && $this->RawFormString == "") {
						return $Default;
					}
					return $this->RawFormString;
				}
				else if(isset($this->FormArray[$Key])) {
					return $this->FormArray[$Key];
				}
				else {
					return null;
				}
			}
		//<-- End Method :: Form
		
		//##################################################################################
		
		//--> Begin Method :: QueryString
			public function QueryString($Key = null, $Default = null) {
				if(is_null($Key)) {
					return $this->RawQueryString;
				}
				else {
					//should we use the default?
					if(!is_null($Default) && !isset($this->QueryStringArray[$Key])) {
						return $Default;
					}
					else {
						if(isset($this->QueryStringArray[$Key])) {
							return $this->QueryStringArray[$Key];
						}
						else {
							return null;
						}
					}
				}
			}
		//<-- End Method :: QueryString
		
		//##################################################################################
		
		//--> Begin Method :: Input
			public function Input($Key, $Default = "", $FormFirst = false) {
				//container
				$Value = null;
				
				if($FormFirst) {
					$Value = isset($this->FormArray[$Key]) ? $this->FormArray[$Key] : null;
					if(is_null($Value)) {
						$Value = isset($this->QueryStringArray[$Key]) ? $this->QueryStringArray[$Key] : null;
					}
				}
				else {
					$Value = isset($this->QueryStringArray[$Key]) ? $this->QueryStringArray[$Key] : null;
					if(is_null($Value)) {
						$Value = isset($this->FormArray[$Key]) ? $this->FormArray[$Key] : null;
					}
				}
				
				if(is_null($Value)) {
					$Value = $Default;
				}
				
				return $Value;
			}
		//<-- End Method :: Input
		
		//##################################################################################
		
		//--> Begin Method :: File
			public function File($Key) {
				for($i = 0; $i < count($this->Files); $i++) {
					if($this->Files[$i]->FormName == $Key){
						return $this->Files[$i];
					}
				}
				return null;
			}
		//<-- End Method :: File
		
		//##################################################################################
		
		//--> Begin Method :: ServerVariables
			public function ServerVariables($Value) {
				if(isset($_SERVER[$Value])) {
					return $_SERVER[$Value];
				}
				else {
					return null;
				}
			}
		//<-- End Method :: ServerVariables
		
		//##################################################################################
		
		//--> Begin Method :: Session
			public function Session($Key) {
				if(isset($_SESSION[$Key])) {
					return $_SESSION[$Key];
				}
				else {
					return null;
				}
			}
		//<-- End Method :: Session
		
		//##################################################################################
		
		//--> Begin Method :: Cookies
			public function Cookies($Key) {
				if(isset($_COOKIE[$Key])) {
					return $_COOKIE[$Key];
				}
				else {
					return null;
				}
			}
		//<-- End Method :: Cookies
		
		//##################################################################################
		
		//--> Begin Method :: Header
			public function Header($Key) {
				//get token ready for cgi variables
				$Key = "HTTP_". strtoupper(str_replace("-", "_", $Key));				
				if(isset($_SERVER[$Key])) {
					return $_SERVER[$Key];
				}
				else {
					return null;
				}
			}
		//<-- End Method :: Header
	}
//<-- End Class :: WebRequest

//##########################################################################################
?>