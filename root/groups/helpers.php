<?php
//##########################################################################################

//--> Begin Class :: Helpers
	class H {
		//--> Begin Method :: Event_BeforeBinding
			public static function Event_BeforeBinding() {
				if(F::$PageNamespace != "root/groups/index") {
					//get details
					F::$DB->SQLCommand = "
						SELECT 
							name AS groupname, 
							'root/groups/add-edit.html?id=#id#' AS details_url 
						FROM user_group 
						WHERE id = '#id#'
					";
					F::$DB->BindKeys(F::$PageInput);
					$tmpGroupDetails = F::$DB->GetDataRow();
					F::$DOMBinders["groupname"] = $tmpGroupDetails["groupname"];
					F::$DOMBinders["details_url"] = $tmpGroupDetails["details_url"];
					
					//tab links
					if(F::$Request->Input("id") != "") {
						F::$DOMBinders["tab_details_url"] = "root/groups/add-edit.html?id=". F::$Request->Input("id");
						F::$DOMBinders["tab_permissions_url"] = "root/groups/permissions.html?id=". F::$Request->Input("id");
					}
					else {
						F::$Doc->Traverse("//*[@id='tab-root/groups/permissions']//a")->SetAttribute("class", "disabled");
					}
				}
			}
		//<-- End Method :: Event_BeforeBinding
		
		//##################################################################################
		
		//--> Begin Method :: CategoryDD
			public static function CategoryDD($Node) {
				//create a list menu
				$DropDown = new WebFormMenu("tmp", 1, 0);
				
				//contextual
				if(F::$PageNamespace == "root/groups/index") {
					$DropDown->AddOption("--- any ---", "");
					$DropDown->AddOption("--- none ---", "0");
				}
				else if(F::$PageNamespace == "root/groups/add-edit") {
					$DropDown->AddOption("--- none ---", "0");
				}
				
				//get categories
				F::$DB->SQLCommand = "
					SELECT id, name
					FROM user_group_category
					WHERE is_active = 'yes'
					ORDER BY name
				";
				$tblData = F::$DB->GetDataTable();
				
				//get items for this category
				for($i = 0 ; $i < count($tblData) ; $i++) {
					$DropDown->AddOption($tblData[$i]["name"], $tblData[$i]["id"]);
				}
				
				//populate the options
				F::$Doc->SetInnerHTML($Node, $DropDown->GetOptionTags());
			}
		//<-- End Method :: CategoryDD
	}
//<-- End Class :: Helpers

############################################################################################
?>