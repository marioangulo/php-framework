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

//--> Begin Class :: Admin
	class Admin {
		//--> Begin :: Properties
			//no properties
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function Admin() {
				//do nothing
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: GetUserID
			public function GetUserID($AdminID) {
				F::$DB->SQLCommand = "
					SELECT fk_user_id
					FROM admin
					WHERE id = '#id#'
					LIMIT 1
				";
				F::$DB->SQLKey("#id#", $AdminID);
				return F::$DB->GetDataString();
			}
		//<-- End Method :: GetUserID
		
		//##################################################################################
		
		//--> Begin Method :: GetAdminID
			public function GetAdminID($UserID) {
				F::$DB->SQLCommand = "
					SELECT id
					FROM admin
					WHERE fk_user_id = '#fk_user_id#'
					LIMIT 1
				";
				F::$DB->SQLKey("#fk_user_id#", $UserID);
				return F::$DB->GetDataString();
			}
		//<-- End Method :: GetUserID
	}
//<-- End Class :: Admin

//##########################################################################################
?>