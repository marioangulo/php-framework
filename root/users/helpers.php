<?php
//##########################################################################################

//--> Begin Class :: Helpers
	class H {
		//--> Begin Method :: Event_BeforeBinding
			public static function Event_BeforeBinding() {
				if(F::$PageNamespace != "root/users/index") {
					//get details
					F::$DB->SQLCommand = "
						SELECT 
							username, 
							'root/users/add-edit.html?id=#id#' AS details_url 
						FROM user 
						WHERE id = '#id#'
					";
					F::$DB->BindKeys(F::$PageInput);
					$tmpUserDetails = F::$DB->GetDataRow();
					F::$DOMBinders["username"] = $tmpUserDetails["username"];
					F::$DOMBinders["details_url"] = $tmpUserDetails["details_url"];
					
					//tab links
					if(F::$Request->Input("id") != "") {
						F::$DOMBinders["tab_details_url"] = "root/users/add-edit.html?id=". F::$Request->Input("id");
						F::$DOMBinders["tab_groups_url"] = "root/users/groups.html?id=". F::$Request->Input("id");
						F::$DOMBinders["tab_permissions_url"] = "root/users/permissions.html?id=". F::$Request->Input("id");
						F::$DOMBinders["tab_history_url"] = "root/users/history.html?id=". F::$Request->Input("id");
					}
					else {
						F::$Doc->Traverse("//*[@id='tab-root/users/groups']//a")->SetAttribute("class", "disabled");
						F::$Doc->Traverse("//*[@id='tab-root/users/permissions']//a")->SetAttribute("class", "disabled");
						F::$Doc->Traverse("//*[@id='tab-root/users/history']//a")->SetAttribute("class", "disabled");
					}
				}
			}
		//<-- End Method :: Event_BeforeBinding
		
		//##################################################################################
		
		//--> Begin Method :: GroupDD
			public static function GroupDD($Node) {
				//create a list menu
				$DropDown = new WebFormMenu("tmp", 1, 0);
				
				//contextual
				if(F::$PageNamespace == "root/users/index") {
					$DropDown->AddOption("--- any ---", "");
					$DropDown->AddOption("--- none ---", "0");
				}
				else if(F::$PageNamespace == "root/users/add-edit") {
					$DropDown->AddOption("--- none ---", "0");
				}
				else if(F::$PageNamespace == "root/users/groups") {
					$DropDown->AddOption("--- Select a Group ---", "");
				}
				
				//get categories
				F::$DB->SQLCommand = "
					SELECT id, name
					FROM user_group_category
					WHERE is_active = 'yes'
					ORDER BY name
				";
				$tblCategories = F::$DB->GetDataTable();
				for($i = 0 ; $i < count($tblCategories) ; $i++) {
					$DropDown->AddOptionGroup($tblCategories[$i]["name"]);
					
					//get category items
					F::$DB->SQLCommand = "
						SELECT id, name
						FROM user_group
						WHERE
							is_active = 'yes'
							AND fk_category_id = '#fk_category_id#'
						ORDER BY name
					";
					F::$DB->SQLKey("#fk_category_id#", $tblCategories[$i]["id"]);
					$tblItems = F::$DB->GetDataTable();
					for($j = 0 ; $j < count($tblItems) ; $j++) {
						$DropDown->AddOption($tblItems[$j]["name"], $tblItems[$j]["id"]);
					}
				}
				
				//populate the options
				F::$Doc->SetInnerHTML($Node, $DropDown->GetOptionTags());
			}
		//<-- End Method :: GroupDD
		
		####################################################################################
		
		//--> Begin Method :: TimezoneDD
			public static function TimezoneDD($Node) {
				//populate the options
				F::$Doc->SetInnerHTML($Node, F::$System->TimeZoneDD(false, false));
			}
		//<-- End Method :: TimezoneDD
	}
//<-- End Class :: Helpers

############################################################################################
?>