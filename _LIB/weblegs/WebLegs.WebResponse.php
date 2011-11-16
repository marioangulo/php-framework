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

//--> Begin Class :: WebResponse
	class WebResponse {
		//--> Begin :: Properties
			public $RedirectURL;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function WebResponse() {
				$this->RedirectURL = null;
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: Finalize
			public function Finalize($Data = "") {
				//is there a redirect url?
				if(!is_null($this->RedirectURL) && $this->RedirectURL != "") {
					$this->Redirect($this->RedirectURL);
				}
				else {
					//write final data and end
					$this->Write($Data);
					$this->End();
				}
			}
		//<-- End Method :: Finalize
		
		//##################################################################################
		
		//--> Begin Method :: Write
			public function Write($Value) {
				print($Value);
			}
		//<-- End Method :: Write
		
		//##################################################################################
		
		//--> Begin Method :: Redirect
			public function Redirect($URL) {
				//set redirect header
				header("Location: ". $URL);
				$this->End();
			}
		//<-- End Method :: Redirect
		
		//##################################################################################
		
		//--> Begin Method :: End
			public function End() {
				//stop the execution of php
				exit();
			}
		//<-- End Method :: End
		
		//##################################################################################
		
		//--> Begin Method :: AddHeader
			public function AddHeader($Name, $Value) {
				//set http header
				header($Name .": ". $Value);
			}
		//<-- End Method :: AddHeader
		
		//##################################################################################
		
		//--> Begin Method :: Session
			public function Session($Name, $Value) {
				$_SESSION[$Name] = $Value;
				return;
			}
		//<-- End Method :: Session
		
		//##################################################################################
		
		//--> Begin Method :: Cookies
			public function Cookies($Key, $Value = "", $Minutes = 0, $Path = "/", $Domain = null, $Secure = false) {
				//calculate minutes
				if($Minutes != 0) {
					//unix timestamp X 60 seconds X $Minutes
					$Expires = time() + 60 * $Minutes;		
				}
				else{
					$Expires = 0;
				}
				
				//set domain
				if(is_null($Domain)) {
					$Domain = $_SERVER['HTTP_HOST'];
				}
				
				//if we are ssl make cookies require ssl
				if($Secure == false && $_SERVER['SERVER_PORT'] == "443") {
					$Secure = true;
				}
				
				//lets set the cookie
				setcookie($Key, $Value, $Expires, $Path, $Domain, $Secure);
			}
		//<-- End Method :: Cookies
		
		//##################################################################################
		
		//--> Begin Method :: ClearCookies
			public function ClearCookies($Path = "/", $Domain = "") {
				//set default value
				if($Domain == ""){
					$Domain = $_SERVER["SERVER_NAME"];
				}
				
				//loop through each cookie and expire
				foreach($_COOKIE as $Key => $Value) {
					//set cookie to expire yesterday
					setcookie($Key, null, time() - 1440, $Path, $Domain);
				}
			}
		//<-- End Method :: ClearCookies
		
		//##################################################################################
		
		//--> Begin Method :: ClearSession
			public function ClearSession() {
				//accomodate for incosistant behaviour
				session_unset();
				session_destroy();
				$_SESSION = array();
			}
		//<-- End Method :: ClearSession
	}
//<-- End Class :: WebResponse

//##########################################################################################
?>