<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: GroupDD
			public static function GroupDD($Node) {
				//create a list menu
				$DropDown = new WebFormMenu("tmp", 1, 0);
				
				//default option
				$DropDown->AddOption("--- Select a Group ---", "");
				
				//get categories
				F::$DB->SQLCommand = "
					SELECT id, name
					FROM user_group_category
					WHERE is_active = 'yes'
					ORDER BY name
				";
				$tblCategories = F::$DB->GetDataTable();
				
				//get items for this category
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
					
					//build options
					for($j = 0 ; $j < count($tblItems) ; $j++) {
						$DropDown->AddOption($tblItems[$j]["name"], $tblItems[$j]["id"]);
					}
				}
				
				//populate the options
				F::$Doc->SetInnerHTML($Node, $DropDown->GetOptionTags());
			}
		//<-- End Method :: GroupDD
		
		//##################################################################################
		
		//--> Begin Method :: Action_Add
			public static function Action_Add() {
				//validate
				if(F::$Request->Input("fk_group_id") == "0" || F::$Request->Input("fk_group_id") == "") {
					F::$Errors->Add("You must select a group.");
				}
				else {
					F::$DB->LoadCommand("duplicate-permission-check", F::$PageInput);
					if(F::$DB->GetDataString() != "") {
						F::$Errors->Add("A permission has already been added for that group.");
					}
				}
				
				//take action
				if(F::$Errors->Count() == 0) {
					F::$DB->LoadCommand("add-group", F::$PageInput);
					F::$DB->ExecuteNonQuery();
					
					F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$Request->Input("id"));
				}
			}
		//<-- End Method :: Action_Add
		
		//##################################################################################
		
		//--> Begin Method :: Action_Update
			public static function Action_Update() {
				F::$DB->LoadCommand("update", F::$PageInput);
				F::$DB->ExecuteNonQuery();
				
				F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$Request->Input("id"));
			}
		//<-- End Method :: Action_Update
		
		//##################################################################################
		
		//--> Begin Method :: Action_Delete
			public static function Action_Delete() {
				F::$DB->LoadCommand("delete", F::$PageInput);
				F::$DB->ExecuteNonQuery();
				
				F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$Request->Input("id"));
			}
		//<-- End Method :: Action_Delete
	}
//<-- End Class :: Page

//##########################################################################################
?>