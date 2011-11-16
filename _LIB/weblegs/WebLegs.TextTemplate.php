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

//--> Begin Class :: TextTemplate
	class TextTemplate {
		//--> Begin :: Properties
			public $Source;
			public $DTD;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function TextTemplate() {
				$this->DTD = "";
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: LoadFile
			public function LoadFile($Path, $RootPath = null) {
				//make sure file exists
				if(!file_exists($Path)){
					throw new Exception("WebLegs.TextTemplate.LoadFile(): File not found or not able to access.");
				}
			
				//load up file
				$this->Load(file_get_contents($Path), $RootPath);			
				return $this;
			}
		//<-- End Method :: LoadFile
		
		//##################################################################################
		
		//--> Begin Method :: Load
			public function Load($Source, $RootPath = null) {
				if(is_null($RootPath)) {
					$this->Source = $Source;
					return $this;
				}
				//see if there is any stylesheets
				else if(strpos($Source, "xml-stylesheet") == false) {
					$this->Source = $Source;
					return $this;
				}
		
				//find the xsl style sheet path in our document
				preg_match("/xml-stylesheet.*?href=[\"|\'](.*?)[\"|\']/", $Source, $Matches);
				$XSLTPath = $Matches[1];
				
				//get dtd
				preg_match("/(<!DOCTYPE.*?>)/", $Source, $Matches);
				if(count($Matches) > 0){
					$this->DTD = $Matches[1];
					
					//strip out dtd
					$Source = str_replace($this->DTD, "", $Source);
				}
				
				//loat xml source
				$XMLDoc = new DomDocument();
				@$XMLDoc->loadHTML($Source);
				
				//create a xslt document
				$XSLTDoc = new DomDocument();
				$XSLTDoc->load($RootPath . $XSLTPath);
				
				//create an xslt processor and load style sheet
				$XProc = new XSLTProcessor();
				$XProc->importStylesheet($XSLTDoc);
		
				//transform the xml and load up our dom object
				$this->Source = $XProc->transformToXML($XMLDoc);
				
				return $this;
			}
		//<-- End Method :: Load
		
		//##################################################################################
		
		//--> Begin Method :: Replace
			public function Replace() {
				$Args = func_get_args();
				$this->Source = str_replace($Args[0], $Args[1], $this->Source);
				return $this;
			} 
		//<-- End Method :: Replace
		
		//##################################################################################
		
		//--> Begin Method :: GetSubString
			public function GetSubString($Start, $End) {
				$MyStart = 0;
				$MyEnd = 0;
				
				if(stripos($this->Source, $Start) != false && strripos($this->Source, $End) != false) {
					$MyStart = (stripos($this->Source, $Start)) + strlen($Start);
					$MyEnd = strripos($this->Source, $End);
					try {
						return substr($this->Source, $MyStart, $MyEnd - $MyStart);
					}
					catch(Exception $e) {
						throw new Exception("WebLegs.TextTemplate.GetSubString: Boundry string mismatch.");
					}
				}
				else {
					throw new Exception("WebLegs.TextTemplate.GetSubString: Boundry strings not present in source string.");
				}
			}
		//<-- End Method :: GetSubString
		
		//##################################################################################
		
		//--> Begin Method :: RemoveSubString
			public function RemoveSubString($Start, $End, $RemoveKeys = false) {
				try {
					$SubString = $this->GetSubString($Start, $End);
				}
				catch(Exception $e) {
					throw new Exception("WebLegs.TextTemplate.RemoveSubString(): Boundry string mismatch.");
				}
				
				//remove substring
				$this->Replace($SubString, "");
				
				//should we remove the keys too?
				if($RemoveKeys) {
					$this->Replace($Start, "");
					$this->Replace($End, "");
				}
				return $this;
			}
		//<-- End Method :: RemoveSubString
		
		//##################################################################################
		
		//--> Begin Method :: ToString
			public function ToString() {
				return $this->DTD . $this->Source;
			} 
		//<-- End Method :: ToString	
		
		//##################################################################################
		
		//--> Begin Method :: SaveAs
			public function SaveAs($FilePath) {
				if(file_put_contents($FilePath, $this->ToString()) == false){
					 throw new Exception("WebLegs.TextTemplate.SaveAs(): Unable to save file.");
				}
			} 
		//<-- End Method :: ToString
	}
//<-- End Class :: TextTemplate

//##########################################################################################
?>