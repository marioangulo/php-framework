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

//--> Begin Class :: Config
		class Config {
		//--> Begin :: Properties
			public $Variables;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function Config() {
				$Variables = array();
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: Get
			public function Get($Key = null) {
				if($Key == null) {
					return $this->Variables;
				}
				else {
					if(isset($this->Variables[$Key])) {
						return $this->Variables[$Key];
					}
				}
				
				return null;
			}
		//<-- End Method :: Get
		
		//##################################################################################
		
		//--> Begin Method :: Set
			public function Set($Key, $Value) {
				$this->Variables[$Key] = $Value;
			}
		//<-- End Method :: Set
	}
//<-- End Class :: Config

//##########################################################################################
?>