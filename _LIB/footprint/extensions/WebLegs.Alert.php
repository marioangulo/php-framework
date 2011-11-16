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

//--> Begin Overload :: Alert
	class Alert_Ext extends Alert {
		//--> Begin Method :: Add
			public function Add($Arg1 = null, $Arg2 = null) {
				if(isset($Arg1) && isset($Arg2)) {
					$this->Alerts[] = array($Arg1, $Arg2);
				}
				else {
					$this->Alerts[] = $Arg1;
				}
			}
		//<-- End Method :: Add
	}
//<-- End Class :: Alert

//##########################################################################################
?>