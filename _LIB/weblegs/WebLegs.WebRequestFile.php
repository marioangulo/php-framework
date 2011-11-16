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

//--> Begin Class :: WebRequestFile
	class WebRequestFile {
		//--> Begin :: Properties
			public $FormName;
			public $FileName;
			public $ContentType;
			public $ContentLength;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function WebRequestFile() {}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: SaveAs
			public function SaveAs($FilePath) {
				move_uploaded_file($_FILES[$this->FormName]["tmp_name"], $FilePath);
			}
		//<-- End Method :: Add
	}
//<-- End Class :: WebRequestFile

//##########################################################################################
?>