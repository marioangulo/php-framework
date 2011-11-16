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

//--> Begin Overload :: WebResponse
	class WebResponse_Ext extends WebResponse {
		//--> Begin :: Properties
			public $Headers;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin Method :: AddHeader
			public function AddHeader($Name, $Value) {
				//keep track of them in the headers
				$this->Headers[strtolower($Name)] = $Value;
				
				//set http header
				header($Name .": ". $Value);
			}
		//<-- End Method :: AddHeader
	}
//<-- End Class :: WebResponse

//##########################################################################################
?>