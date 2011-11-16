<?php
//##########################################################################################

/*
Copyright (C) 2005-2010 WebLegs, Inc.
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

//--> Begin Class :: WebFormMenu
	class WebFormMenu {
		//--> Begin :: Properties
			public $Name;
			public $Size;
			public $SelectMultiple;
			public $Attributes;
			public $SelectedValues;
			public $Options;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: WebFormMenu
			public function WebFormMenu($Name = "", $Size = 1, $SelectMultiple = false) {
				$this->Name = $Name;
				$this->Size = $Size;
				$this->SelectMultiple = $SelectMultiple;
				$this->Attributes = array();
				$this->SelectedValues = array();
				$this->Options = array();
			}
		//<-- End :: WebFormMenu
		
		//##################################################################################
		
		//--> Begin Method :: AddAttribute
			public function AddAttribute($Name, $Value) {
				$this->Attributes[$Name] = $Value;
			}
		//<-- End Method :: AddAttribute
		
		//##################################################################################
		
		//--> Begin Method :: AddOption
			public function AddOption($Label, $Value, $Custom = "") {
				$this->Options[] = array(
					"label" 	=> $Label,
					"value" 	=> $Value,
					"custom" 	=> $Custom,
					"groupflag" => false
				);
			}
		//<-- End Method :: AddOption
		
	
		//##################################################################################
		
		//--> Begin Method :: AddOptionGroup
			public function AddOptionGroup($Label, $Custom = "") {
				$this->Options[] = array(
					"label" 	=> $Label,
					"value" 	=> "",
					"custom" 	=> $Custom,
					"groupflag" => true
				);
			}
		//<-- End Method :: AddOptionGroup
	
		//##################################################################################
		
		//--> Begin Method :: AddSelectedValue
			public function AddSelectedValue($Value) {
				$this->SelectedValues[] = $Value;
			}
		//<-- End Method :: AddSelectedValue
		
		//##################################################################################
		
		//--> Begin Method :: GetOptionTags
			public function GetOptionTags() {
				//main container
				$tmpOptionTags = "";
				
				//last group reference
				$LastGroupReference = null;
				
				//build options
				for($i = 0 ; $i < count($this->Options); $i++) {
					//check for groups
					if($this->Options[$i]["groupflag"] == true) {
						//was there a group before this
						if(is_null($LastGroupReference)) {
							$LastGroupReference = $i;
						}
						else {
							$tmpOptionTags .= "</optgroup>";
							$LastGroupReference = $i;
						}
						$tmpOptionTags .= "<optgroup label=\"". Codec::HTMLEncode($this->Options[$i]["label"]) ."\" ". $this->Options[$i]["custom"] .">";
					}
					//normal option
					else {
						$IsSelected = in_array($this->Options[$i]["value"], $this->SelectedValues);
						$tmpOptionTags .= "<option value=\"". Codec::HTMLEncode($this->Options[$i]["value"]) ."\"". ($IsSelected == true ? " selected=\"selected\"" : "") ." ". $this->Options[$i]["custom"] .">". Codec::HTMLEncode($this->Options[$i]["label"]) ."</option>";
					}
				}
				//should end a group
				if(!is_null($LastGroupReference)) {
					$tmpOptionTags .= "</optgroup>";
				}
				return $tmpOptionTags;
			}
		//<-- End Method :: GetOptionTags
		
		//##################################################################################
		
		//--> Begin Method :: ToString
			public function ToString() {
				$tmpDropDown = "";
				
				//start the beginning select tag
				$tmpDropDown .= "<select name=\"". $this->Name ."\" size=\"". $this->Size ."\"". ($this->SelectMultiple ? " multiple=\"multiple\"" : "");
		
				//add any custom attributes
				foreach($this->Attributes as $Key => $Value) {
					$tmpDropDown .= " ". $Key ."=\"". $Value ."\"";
				}
				
				//finish the begining select tag
				$tmpDropDown .= ">";
				
				//add the options
				$tmpDropDown .= $this->GetOptionTags();
				
				//finish building the select tag
				$tmpDropDown .= "</select>";
				
				return $tmpDropDown;
			}
		//<-- End Method :: ToString
	}
//<-- End Class :: WebFormMenu

//##########################################################################################
?>