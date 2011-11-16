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

//--> Begin Class :: Account
	class Account {
		//--> Begin :: Properties
			//no properties
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function Account() {
				//do nothing
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: GetUserID
			public function GetUserID($AccountID) {
				F::$DB->SQLCommand = "
					SELECT fk_user_id
					FROM account
					WHERE id = '#id#'
					LIMIT 1
				";
				F::$DB->SQLKey("#id#", $AccountID);
				return F::$DB->GetDataString();
			}
		//<-- End Method :: GetUserID
		
		//##################################################################################
		
		//--> Begin Method :: GetAccountID
			public function GetAccountID($UserID) {
				F::$DB->SQLCommand = "
					SELECT id
					FROM account
					WHERE fk_user_id = '#fk_user_id#'
					LIMIT 1
				";
				F::$DB->SQLKey("#fk_user_id#", $UserID);
				return F::$DB->GetDataString();
			}
		//<-- End Method :: GetUserID
	}
//<-- End Class :: Account

//##########################################################################################
?>