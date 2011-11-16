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

//--> Begin Overload :: DateTimeDriver
	class DateTimeDriver_Ext extends DateTimeDriver {
		//--> Begin Method :: ToSQLString
			//notes: this gives us a shortcut to a mysql/db ready timestamp 
			public function ToSQLString($Format = "yyyy-MM-dd HH:mm:ss") {
				$Output = $this->ToString($Format);
				
				//make the min value nothing
				if($this->ToString("yyyy") == "1901") {
					$Output = preg_replace("/(\d)/", "0", $Output);
				}
				
				return $Output;
			}
		//<-- End Method :: ToSQLString
		
		//##################################################################################
		
		//--> Begin Method :: Now
			function Now() {
				return new DateTimeDriver_Ext();
			}
		//<-- End Method :: Now
	}
//<-- End Class :: DateTimeDriver

//##########################################################################################
?>