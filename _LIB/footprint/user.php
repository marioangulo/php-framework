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

//--> Begin Class :: User
	class User {
		//--> Begin :: Properties
			//no properties
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function User() {
				//do nothing
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: ContinueOrDenyPermission
			public function ContinueOrDenyPermission($SecurityID) {
				//check if this user has permission
				if(!F::$User->HasPermission(F::$Request->Session("user_id"), $SecurityID)) {
					//get page template
					F::$Doc->LoadFile(F::FilePath("_LIB/footprint/permission-denied.html"), F::$Config->Get("root-path"));
					
					//get the security label
					F::$DB->SQLCommand = "SELECT name FROM user_security WHERE id = '#security_id#'";
					F::$DB->SQLKey("#security_id#", $SecurityID);
					$tmpSecurityLabel = F::$DB->GetDataString();
					
					//set some binders
					F::$DOMBinders["mail-to-email"] = F::$Config->Get("admin-email");
					F::$DOMBinders["mail-to-href"] = "mailto:". F::$Config->Get("admin-email");
					F::$DOMBinders["message"] = "You do not have access to '". $tmpSecurityLabel ."'.";
					
					//log user history
					F::$User->LogHistory("User was denied access to '". $tmpSecurityLabel ."'. security.id='". $SecurityID ."'.");
					
					//do data binding
					F::$Doc->BindResources(F::$Doc);
					F::$Doc->FinalBind();
					
					//close db
					F::$DB->Close();
					
					//finalize request
					F::$Response->Finalize(F::$Doc->ToString());
				}
				else if(!F::$User->HasValidIP(F::$Request->Session("user_id"))) {
					//get page template
					F::$Doc->LoadFile(F::FilePath("_GLOBAL/permission-denied.html"), F::$Config->Get("root-path"));
					
					//set some binders
					F::$DOMBinders["mail-to-email"] = F::$Config->Get("admin-email");
					F::$DOMBinders["mail-to-href"] = "mailto:". F::$Config->Get("admin-email");
					F::$DOMBinders["message"] = "Your IP address could not be validated.";
					
					//log user history
					F::$User->LogHistory("User was denied access because their IP address could not be validated.");
					
					//do data binding
					F::$Doc->BindResources(F::$Doc);
					F::$Doc->FinalBind();
					
					//close db
					F::$DB->Close();
					
					//finalize request
					F::$Response->Finalize(F::$Doc->ToString());
				}
			}
		//<-- End Method :: ContinueOrDenyPermission
		
		//##################################################################################
		
		//--> Begin Method :: RequireSession
			public function RequireSession() {
				//check if they have a cookie
				if(F::$Request->Cookies("session_id") == null) {
					//close the db
					F::$DB->Close();
					
					//redirect to logout
					$ReturnURL = "";
					$ThisPage = "";
					if(F::$Request->ServerVariables("SCRIPT_NAME") == null) {
						$ThisPage = F::$Request->ServerVariables("PATH_INFO");
					}
					else {
						$ThisPage = F::$Request->ServerVariables("SCRIPT_NAME");
					}
					$ThisQuery = F::$Request->ServerVariables("QUERY_STRING");
					$ReturnURL = Codec::URLEncode($ThisPage . ($ThisQuery != "" ? "?". $ThisQuery : ""));
					F::$Response->Redirect(F::URL("login/index.html?return=". $ReturnURL));
					F::$Response->Finalize();
				}
				else {
					//get session data
					$UserID = "";
						if(F::$Request->Cookies("user_id") != null) {
							$UserID = F::$Request->Cookies("user_id");
						}
					$Username = "";
						if(F::$Request->Cookies("username") != null) {
							$Username = F::$Request->Cookies("username");
						}
					$Timezone = "";
						if(F::$Request->Cookies("timezone") != null) {
							$Timezone = F::$Request->Cookies("timezone");
						}
					$TimezoneOffset = "";
						if(F::$Request->Cookies("timezone_offset") != null) {
							$TimezoneOffset = F::$Request->Cookies("timezone_offset");
						}
					$CookieSessionID =  "";
						if(F::$Request->Cookies("session_id") != null) {
							$CookieSessionID = F::$Request->Cookies("session_id");
						}
					
					//get session id
					$dbSessionID = F::$User->GetSessionID($UserID);
					
					//compare database and cookie session
					if($dbSessionID == $CookieSessionID) {
						//make sure we still have a session
						F::$Response->Session("user_id", $UserID);
						F::$Response->Session("username", $Username);
						F::$Response->Session("timezone", $Timezone);
						F::$Response->Session("timezone_offset", $TimezoneOffset);
						F::$Response->Session("session_id", $CookieSessionID);
					}
					else {
						//close the db
						F::$DB->Close();
						
						//redirect to logout
						$ReturnURL = "";
						$ThisPage = "";
						if(F::$Request->ServerVariables("SCRIPT_NAME") == null) {
							$ThisPage = F::$Request->ServerVariables("PATH_INFO");
						}
						else {
							$ThisPage = F::$Request->ServerVariables("SCRIPT_NAME");
						}
						$ThisQuery = F::$Request->ServerVariables("QUERY_STRING");
						$ReturnURL = Codec::URLEncode($ThisPage . ($ThisQuery != "" ? "?". $ThisQuery : ""));
						F::$Response->Redirect(F::URL("login/index.html?return=". $ReturnURL));
						F::$Response->Finalize();
					}
				}
			}
		//<-- End Method :: RequireSession
		
		//##################################################################################
		
		//--> Begin Method :: RefreshSession
			public function RefreshSession() {
				//check if they have a cookie
				if(F::$Request->Cookies("session_id") == null) {
					//do nothing
				}
				else {
					//get session data
					$UserID = "";
						if(F::$Request->Cookies("user_id") != null) {
							$UserID = F::$Request->Cookies("user_id");
						}
					$Username = "";
						if(F::$Request->Cookies("username") != null) {
							$Username = F::$Request->Cookies("username");
						}
					$Timezone = "";
						if(F::$Request->Cookies("timezone") != null) {
							$Timezone = F::$Request->Cookies("timezone");
						}
					$TimezoneOffset = "";
						if(F::$Request->Cookies("timezone_offset") != null) {
							$TimezoneOffset = F::$Request->Cookies("timezone_offset");
						}
					$CookieSessionID =  "";
						if(F::$Request->Cookies("session_id") != null) {
							$CookieSessionID = F::$Request->Cookies("session_id");
						}
					
					//get session id
					$dbSessionID = F::$User->GetSessionID($UserID);
					
					//compare database and cookie session
					if($dbSessionID == $CookieSessionID) {
						//make sure we still have a session
						F::$Response->Session("user_id", $UserID);
						F::$Response->Session("username", $Username);
						F::$Response->Session("timezone", $Timezone);
						F::$Response->Session("timezone_offset", $TimezoneOffset);
						F::$Response->Session("session_id", $CookieSessionID);
					}
				}
			}
		//<-- End Method :: RefreshSession
		
		//##################################################################################
		
		//--> Begin Method :: GetSessionID
			public function GetSessionID($UserID) {
				F::$DB->SQLCommand = "
					SELECT session_id 
					FROM user 
					WHERE id = '#user_id#' 
				";
				F::$DB->SQLKey("#user_id#", $UserID);
				$dtrUserSession = F::$DB->GetDataRow();
				if($dtrUserSession != null) {
					return $dtrUserSession["session_id"];
				}
				else{
					return null;
				}
			}
		//<-- End Method :: GetSessionID
		
		//##################################################################################
		
		//--> Begin Method :: ValidateIP
			public function ValidateIP() {
				//get ip data
				F::$DB->SQLCommand = "
					SELECT IF(ISNULL(ip_access.id), 'no', 'yes') AS is_valid_ip
					FROM ip_access 
					WHERE 
						ip_address = '#ip_address#' 
						AND is_active = 'yes'
					LIMIT 1
				";
				F::$DB->SQLKey("#ip_address#", F::$Request->ServerVariables("REMOTE_ADDR"));
				if(F::$DB->GetDataString() == "yes") {
					return true;
				}
				else {
					return false;
				}
			}
		//<-- End Method :: ValidateIP
		
		//##################################################################################
		
		//--> Begin Method :: HasValidIP
			public function HasValidIP($UserID) {
				if(F::$User->IsRoot($UserID)) {
					return true;
				}
				else {
					//may skip ip check?
					if(F::$User->HasPermission($UserID, "1000")) {
						return true;
					}
					else {
						return F::$User->ValidateIP();
					}
				}
			}
		//<-- End Method :: HasValidIP
		
		//##################################################################################
		
		//--> Begin Method :: LogHistory
			public function LogHistory($Details, $UserID = null) {
				if($UserID == null){
					$UserID = (F::$Request->Session("user_id") == null ? "" : F::$Request->Session("user_id"));
				}
				
				$ThisPage = "";
				if(F::$Request->ServerVariables("SCRIPT_NAME") == null) {
					$ThisPage = F::$Request->ServerVariables("PATH_INFO");
				}
				else {
					$ThisPage = F::$Request->ServerVariables("SCRIPT_NAME");
				}
				$ThisQuery = F::$Request->ServerVariables("QUERY_STRING");
				$tmpURL = $ThisPage . ($ThisQuery != "" ? "?". $ThisQuery : "");
				
				F::$DB->SQLCommand = "
					INSERT INTO user_history 
					SET 
						fk_user_id = IF(LENGTH('#user_id#') > 0, '#user_id#', '0'), 
						details = '#details#', 
						timestamp_created = '#timestamp_created#', 
						ip_address = '#ip_address#', 
						url = '#url#'
				";
				F::$DB->SQLKey("#user_id#", $UserID);
				F::$DB->SQLKey("#ip_address#", F::$Request->ServerVariables("REMOTE_ADDR"));
				F::$DB->SQLKey("#details#", $Details);
				F::$DB->SQLKey("#timestamp_created#", F::$DateTime->Now()->ToString());
				F::$DB->SQLKey("#url#", $tmpURL);
				F::$DB->ExecuteNonQuery();
			}
		//<-- End Method :: LogHistory
		
		//##################################################################################
		
		//--> Begin Method :: LogAction
			public function LogAction($PivotTable, $PivotID, $Action, $UserID = null) {
				if($UserID == null){
					$UserID = (F::$Request->Session("user_id") == null ? "" : F::$Request->Session("user_id"));
				}
				F::$DB->SQLCommand = "
					INSERT INTO user_action_log 
					SET 
						fk_user_id = IF(LENGTH('#user_id#') > 0, '#user_id#', '0'), 
						fk_pivot_table = '#fk_pivot_table#', 
						fk_pivot_id = '#fk_pivot_id#', 
						action = '#action#', 
						timestamp_created = '#timestamp_created#' 
				";
				F::$DB->SQLKey("#user_id#", $UserID);
				F::$DB->SQLKey("#fk_pivot_table#", $PivotTable);
				F::$DB->SQLKey("#fk_pivot_id#", $PivotID);
				F::$DB->SQLKey("#action#", $Action);
				F::$DB->SQLKey("#timestamp_created#", F::$DateTime->Now()->ToString());
				F::$DB->ExecuteNonQuery();
			}
		//<-- End Method :: LogAction
		
		//##################################################################################
		
		//--> Begin Method :: IsRoot
			public function IsRoot($UserID) {
				//is this the root user
				F::$DB->SQLCommand = "
					SELECT
						IF(ISNULL(user_membership.id), 'no', 'yes') AS is_root
					FROM
						user_membership
						LEFT JOIN user_group ON user_membership.fk_group_id = user_group.id
						LEFT JOIN user_permission ON user_permission.fk_group_id = user_group.id
					WHERE
						user_membership.fk_user_id = '#fk_user_id#'
						AND user_membership.fk_group_id = '1'
						AND user_membership.timestamp_cancelled = '0000-00-00 00:00:00'
					LIMIT 1
				";
				F::$DB->SQLKey("#fk_user_id#", $UserID);
				if(F::$DB->GetDataString() == "yes") {
					return true;
				}
				else {
					return false;
				}
			}
		//<-- End Method :: IsRoot
		
		//##################################################################################
		
		//--> Begin Method :: HasPermission
			public function HasPermission($UserID, $SecurityID) {
				if(F::$User->IsRoot($UserID)) {
					return true;
				}
				
				//lookup group permissions
				F::$DB->SQLCommand = "
					SELECT
						user_group.id AS group_id,
						user_group.name,
						user_permission.permit 
					FROM 
						user_membership 
						LEFT JOIN user_group ON user_membership.fk_group_id = user_group.id 
						LEFT JOIN user_permission ON user_permission.fk_group_id = user_group.id 
					WHERE 
						user_membership.fk_user_id = '#fk_user_id#' 
						AND user_permission.fk_security_id = '#fk_security_id#'
						AND user_membership.timestamp_cancelled = '0000-00-00 00:00:00'
				";
				F::$DB->SQLKey("#fk_user_id#", $UserID);
				F::$DB->SQLKey("#fk_security_id#", $SecurityID);
				$tblGroupPermission = F::$DB->GetDataTable();
				
				//check group permissions
				$GroupHasPermission = false;
				for($i = 0 ; $i < count($tblGroupPermission); $i++) {
					//check for group permission
					if($tblGroupPermission[$i]["permit"] == "yes") {
						$GroupHasPermission = true;
					}
				}
				
				//lookup user permissions
				F::$DB->SQLCommand = "
					SELECT
						user.id AS user_id,
						user_permission.permit
					FROM user
						LEFT JOIN user_permission ON user_permission.fk_user_id = user.id
					WHERE
						user.id = '#user_id#'
						AND user_permission.fk_security_id = '#fk_security_id#'
				";
				F::$DB->SQLKey("#user_id#", $UserID);
				F::$DB->SQLKey("#fk_security_id#", $SecurityID);
				$tblUserPermission = F::$DB->GetDataTable();
			
				//did we find a granular security setting
				if(count($tblUserPermission) > 0) {
					$UserHasPermission = false;
					for($i = 0 ; $i < count($tblUserPermission); $i++) {
						if($tblUserPermission[$i]["permit"] == "yes") {
							$UserHasPermission = true;
						}
					}
					return $UserHasPermission;
				}
				else {
					//permission not found for user return group
					return $GroupHasPermission;
				}
			}
		//<-- End Method :: HasPermission
		
		//##################################################################################
		
		//--> Begin Method :: IsMemberOfGroup
			public function IsMemberOfGroup($UserID, $GroupID) {
				//get user groups
				F::$DB->SQLCommand = "
					SELECT
						IF(ISNULL(user_membership.id), 'no', 'yes') AS is_member_of_group
					FROM 
						user_membership 
						LEFT JOIN user_group ON user_membership.fk_group_id = user_group.id 
						LEFT JOIN user_permission ON user_permission.fk_group_id = user_group.id 
					WHERE 
						user_membership.fk_user_id = '#fk_user_id#' 
						AND user_membership.fk_group_id = '#fk_group_id#'
						AND user_membership.timestamp_cancelled = '0000-00-00 00:00:00' 
					LIMIT 1
				";
				F::$DB->SQLKey("#fk_user_id#", $UserID);
				F::$DB->SQLKey("#fk_group_id#", $GroupID);
				if(F::$DB->GetDataString() == "yes") {
					return true;
				}
				else {
					return false;
				}
			}
		//<-- End Method :: IsMemberOfGroup
		
		//##################################################################################
		
		//--> Begin Method :: GetDefaultGroupID
			public function GetDefaultGroupID($UserID) {
				F::$DB->SQLCommand = "
					SELECT fk_group_id
					FROM user_membership 
					WHERE 
						fk_user_id = '#fk_user_id#' 
						AND is_default = 'yes'
						AND timestamp_cancelled = '0000-00-00 00:00:00'
					LIMIT 1 
				";
				F::$DB->SQLKey("#fk_user_id#", $UserID);
				$dtrUserSession = F::$DB->GetDataRow();
				if($dtrUserSession != null) {
					return $dtrUserSession["fk_group_id"];
				}
				else{
					return null;
				}
			}
		//<-- End Method :: GetDefaultGroupID

		//##################################################################################
		
		//--> Begin Method :: GetGroupIDs
			public function GetGroupIDs($UserID) {
				F::$DB->SQLCommand = "
					SELECT user_group.id AS group_id 
					FROM 
						user_membership 
						LEFT JOIN user_group ON user_membership.fk_group_id = user_group.id 
					WHERE 
						user_membership.fk_user_id = '#fk_user_id#' 
						AND user_membership.timestamp_cancelled = '0000-00-00 00:00:00'
				";
				F::$DB->SQLKey("#fk_user_id#", $UserID);
				$tmpDataIDs = F::$DB->GetDataString(",");
				if($tmpDataIDs == "") {
					$tmpDataIDs = "0";
				}
				return $tmpDataIDs;
			}
		//<-- End Method :: GetGroupIDs
		
		//##################################################################################
		
		//--> Begin Method :: GetGroupUserIDs
			public function GetGroupUserIDs($GroupID) {
				F::$DB->SQLCommand = "
					SELECT user_membership.fk_user_id
					FROM user_membership
					WHERE 
						user_membership.fk_group_id = '#fk_group_id#' 
						AND user_membership.timestamp_cancelled = '0000-00-00 00:00:00'
				";
				F::$DB->SQLKey("#fk_group_id#", $GroupID);
				$tmpDataIDs = F::$DB->GetDataString(",");
				if($tmpDataIDs == "") {
					$tmpDataIDs = "0";
				}
				return $tmpDataIDs;
			}
		//<-- End Method :: GetGroupUserIDs
		
		//##################################################################################
		
		//--> Begin Method :: Login
			public function Login($ID, $Username, $Timezone, $CookieMinutes) {
				//session_id
					//get SessionID
					$SessionID = F::$User->CreateSessionID();
					
					//update session_id in db
					F::$DB->SQLCommand = "
						UPDATE user
						SET session_id = '#session_id#'
						WHERE id = '#id#'
					";
					F::$DB->SQLKey("#id#", $ID);
					F::$DB->SQLKey("#session_id#", $SessionID);
					F::$DB->ExecuteNonQuery();
				//end session_id
				
				//give session
				F::$Response->Session("user_id", $ID);
				F::$Response->Session("username", $Username);
				F::$Response->Session("timezone", $Timezone);
				F::$Response->Session("timezone_offset", F::$System->ConvertTZOut(F::$DateTime->Now())->ToString("tzo"));
				F::$Response->Session("session_id", $SessionID);
				
				//give cookie
				F::$Response->Cookies("user_id", $ID, $CookieMinutes, "/", F::$Config->Get("cookie-domain"));
				F::$Response->Cookies("username", $Username, $CookieMinutes, "/", F::$Config->Get("cookie-domain"));
				F::$Response->Cookies("timezone", $Timezone, $CookieMinutes, "/", F::$Config->Get("cookie-domain"));
				F::$Response->Cookies("timezone_offset", F::$System->ConvertTZOut(F::$DateTime->Now())->ToString("tzo"), $CookieMinutes, "/", F::$Config->Get("cookie-domain"));
				F::$Response->Cookies("session_id", $SessionID, $CookieMinutes, "/", F::$Config->Get("cookie-domain"));
			}
		//<-- End Method :: Login
		
		//##################################################################################
		
		//--> Begin Method :: Logout
			public function Logout() {
				F::$Response->ClearSession();
				F::$Response->ClearCookies(F::$Config->Get("root-url"), F::$Config->Get("cookie-domain"));
			}
		//<-- End Method :: Logout
		
		//##################################################################################
		
		//--> Begin Method :: CreateSessionID
			public function CreateSessionID() {
				return Codec::MD5Encrypt(rand(9999, 99999999));
			}
		//<-- End Method :: CreateSessionID
		
		//##################################################################################
		
		//--> Begin Method :: IsLoggedIn
			public function IsLoggedIn() {
				//check if they have a cookie
				if(F::$Request->Cookies("session_id") == null) {
					return false;
				}
				else {
					//get session data
					$UserID = "";
						if(F::$Request->Cookies("user_id") != null) {
							$UserID = F::$Request->Cookies("user_id");
						}
					$Username = "";
						if(F::$Request->Cookies("username") != null) {
							$Username = F::$Request->Cookies("username");
						}
					$Timezone = "";
						if(F::$Request->Cookies("timezone") != null) {
							$Timezone = F::$Request->Cookies("timezone");
						}
					$TimezoneOffset = "";
						if(F::$Request->Cookies("timezone") != null) {
							$TimezoneOffset = F::$Request->Cookies("timezone_offset");
						}
					$CookieSessionID =  "";
						if(F::$Request->Cookies("session_id") != null) {
							$CookieSessionID = F::$Request->Cookies("session_id");
						}
					
					//get session id
					$dbSessionID = F::$User->GetSessionID($UserID);
					
					//compare database and cookie session
					if($dbSessionID == $CookieSessionID) {
						return true;
					}
					else {
						return false;
					}
				}
			}
		//<-- End Method :: IsLoggedIn
		
		//##################################################################################
		
		//--> Begin Method :: HasSession
			public function HasSession() {
				//check if they have a cookie
				if(F::$Request->Cookies("session_id") == null) {
					return false;
				}
				else {
					return true;
				}
			}
		//<-- End Method :: HasSession
		
		//##################################################################################
		
		//--> Begin Method :: IsEmailAvailable
			public function IsEmailAvailable($Email, $UserID) {
				F::$DB->SQLCommand = "
					SELECT username
					FROM user
					WHERE
						email = '#email#'
						AND NOT user.id = IF('#user_id#' = '', '0', '#user_id#')
				";	
				F::$DB->SQLKey("#email#", $Email);
				F::$DB->SQLKey("#user_id#", $UserID);
				if(F::$DB->GetDataString() == "") {
					return true;
				}
				else {
					return false;
				}
			}
		//<-- End Method :: IsEmailAvailable
		
		//##################################################################################
		
		//--> Begin Method :: IsUsernameAvailable
			public function IsUsernameAvailable($Username, $UserID) {
				F::$DB->SQLCommand = "
					SELECT username
					FROM user
					WHERE
						username = '#username#'
						AND NOT user.id = IF('#user_id#' = '', '0', '#user_id#')
				";
				F::$DB->SQLKey("#username#", $Username);
				F::$DB->SQLKey("#user_id#", $UserID);
				if(F::$DB->GetDataString() == "") {
					return true;
				}
				else {
					return false;
				}
			}
		//<-- End Method :: IsUsernameAvailable
	}
//<-- End Class :: User

//##########################################################################################
?>