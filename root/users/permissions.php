<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: GetPermissions
			public static function GetPermissions($Node, $Data) {
				F::$DB->LoadCommand("get-user-group-ids", F::$PageInput);
				$tmpGroupIDs = F::$DB->GetDataString(",");
				if($tmpGroupIDs == "") { $tmpGroupIDs = "0"; }
				F::$Doc->SetInnerHTML($Node, self::GetPermissionBranch($tmpGroupIDs, ""));
			}
		//<-- End Method :: GetPermissions
		
		//##################################################################################
		
		//--> Begin Method :: GetPermissionBranch
			public static function GetPermissionBranch($GroupIDs, $SecurityID) {
				$tmpSecurityOptions = "";
				F::$DB->LoadCommand("get-permission-branch");
				F::$DB->SQLKey("#group_ids#", $GroupIDs);
				F::$DB->SQLKey("#dk_id_parent#", $SecurityID);
				F::$DB->BindKeys(F::$PageInput);
				$tblData = F::$DB->GetDataTable();
				for($i = 0 ; $i < count($tblData) ; $i++) {
					$GroupHasPermission = ($tblData[$i]["group_permit"] == "yes" ? true : false);
					
					$tmpSecurityOptions .= "<li>";
					$tmpSecurityOptions .= "<span class=\"". ($GroupHasPermission ? "group_permitted" : "group_restricted") ."\">";
					$tmpSecurityOptions .= WebForm::HiddenField("fk_security_id", $tblData[$i]["fk_security_id"]);
					$tmpSecurityOptions .= WebForm::DropDown("permission_". $tblData[$i]["fk_security_id"], $tblData[$i]["permit"], 1, "|yes|no", "", 0, "");
					$tmpSecurityOptions .= " ". $tblData[$i]["name"] ." ";
					$tmpSecurityOptions .= "</span>";
					$tmpSecurityOptions .= self::GetPermissionBranch($GroupIDs, $tblData[$i]["fk_security_id"]);
					$tmpSecurityOptions .= "</li>";
				}
				if($tmpSecurityOptions == "") {
					return "";
				}
				else {
					return "<ul class=\"security\">". $tmpSecurityOptions ."</ul>";
				}
			}
		//<-- End Method :: GetPermissionBranch
		
		//##################################################################################
		
		//--> Begin Method :: Action_Update
			public static function Action_Update() {
				F::$DB->LoadCommand("delete-user-permissions", F::$PageInput);
				F::$DB->ExecuteNonQuery();
				
				$arrSecurityIDs = explode(",", F::$Request->Input("fk_security_id"));
				for($i = 0 ; $i < count($arrSecurityIDs) ; $i++) {
					$ThisPermission = F::$Request->Input("permission_". $arrSecurityIDs[$i]);
					if($ThisPermission == "") {
						//skip insert
					}
					else {
						F::$DB->LoadCommand("add-user-permission");
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
				F::$DB->LoadCommand("delete-user-permissions", F::$PageInput);
				F::$DB->ExecuteNonQuery();
				
				F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$Request->Input("id"));
			}
		//<-- End Method :: Action_DeleteAll
	}
//<-- End Class :: Page

//##########################################################################################
?>
