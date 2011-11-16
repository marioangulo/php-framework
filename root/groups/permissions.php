<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: GetPermissions
			public static function GetPermissions($Node, $Data) {
				//replace permissions
				F::$Doc->SetInnerHTML($Node, self::GetPermissionBranch(""));
			}
		//<-- End Method :: GetPermissions
		
		//##################################################################################
		
		//--> Begin Method :: Action_UpdatePermissions
			public static function GetPermissionBranch($SecurityID) {
				//get branch of security hierarchy
				F::$DB->LoadCommand("get-permission-branch");
				F::$DB->SQLKey("#dk_id_parent#", $SecurityID);
				F::$DB->BindKeys(F::$PageInput);
				$tblData = F::$DB->GetDataTable();
				
				//loop through this branch
				$tmpSecurityOptions = "";
				for($i = 0 ; $i < count($tblData) ; $i++) {
					$tmpSecurityOptions .= "<li>";
					$tmpSecurityOptions .= WebForm::HiddenField("fk_security_id", $tblData[$i]["fk_security_id"]);
					$tmpSecurityOptions .= WebForm::DropDown("permission_". $tblData[$i]["fk_security_id"], $tblData[$i]["permit"], 1, "|yes|no", "", 0, "");
					$tmpSecurityOptions .= " ". $tblData[$i]["name"] ." ";
					$tmpSecurityOptions .= self::GetPermissionBranch($tblData[$i]["fk_security_id"]);
					$tmpSecurityOptions .= "</li>";
				}
				
				//return
				if($tmpSecurityOptions == "") {
					return "";
				}
				else {
					return "<ul class=\"security\">". $tmpSecurityOptions ."</ul>";
				}
			}
		//<-- End Method :: Action_UpdatePermissions
		
		//##################################################################################
		
		//--> Begin Method :: Action_Update
			public static function Action_Update() {
				F::$DB->LoadCommand("delete-group-permissions", F::$PageInput);
				F::$DB->ExecuteNonQuery();
				
				//break apart security ids
				$arrSecurityIDs = explode(",", F::$Request->Input("fk_security_id"));
				
				//loop through permissions and insert where appropriate
				for($i = 0 ; $i < count($arrSecurityIDs) ; $i++) {
					$ThisPermission = F::$Request->Input("permission_". $arrSecurityIDs[$i]);
					if($ThisPermission == "") {
						//do nothing
					}
					else {
						F::$DB->LoadCommand("add-group-permission");
						F::$DB->SQLKey("#fk_security_id#", $arrSecurityIDs[$i]);
						F::$DB->SQLKey("#permit#", $ThisPermission);
						F::$DB->BindKeys(F::$PageInput);
						F::$DB->ExecuteNonQuery();
					}
				}
				
				F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$Request->Input("id"));
			}
		//<-- End Method :: Action_Update
		
		//##################################################################################
		
		//--> Begin Method :: Action_DeleteAll
			public static function Action_DeleteAll() {
				F::$DB->LoadCommand("delete-group-permissions", F::$PageInput);
				F::$DB->ExecuteNonQuery();
				
				F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$Request->Input("id"));
			}
		//<-- End Method :: Action_DeleteAll
	}
//<-- End Class :: Page

//##########################################################################################
?>
