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

//--> Begin Class :: Alert
	class Alert {
		//--> Begin :: Properties
			public $Alerts;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function Alert() {
				$this->Alerts = array();
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: Add
			public function Add($Value) {
				//set alert
				$this->Alerts[] = $Value;
			}
		//<-- End Method :: Add
		
		//##################################################################################
		
		//--> Begin Method :: Count
			public function Count() {
				//return current count
				return count($this->Alerts);
			}
		//<-- End Method :: Count
		
		//##################################################################################
		
		//--> Begin Method :: Item
			public function Item($Index) {
				if(array_key_exists($Index, $this->Alerts)) {
					return $this->Alerts[$Index];
				}
				return "";
			}
		//<-- End Method :: Item
		
		//##################################################################################
		
		//--> Begin Method :: ToJSON
			public function ToJSON() {
				return json_encode($this->Alerts);
			}
		//<-- End Method :: ToJSON
		
		//##################################################################################
		
		//--> Begin Method :: ToArray
			public function ToArray() {
				//return array
				return $this->Alerts;
			}
		//<-- End Method :: ToArray
	}
//<-- End Class :: Alert

//##########################################################################################
?>