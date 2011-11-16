<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: RowHandler
			public static function RowHandler($Node, $Data) {
				$ID = uniqid();
				$Node->setAttribute("data-bind-id", $ID);
				$Row = F::$Doc->Traverse("//*[@data-bind-id='". $ID ."']")->GetDOMChunk();
				
				if(isset($Data["timestamp_cancelled"])) {
					if($Data["timestamp_cancelled"] != "---"){
						$Row->Root()->SetAttribute("class", "inactive");
						$Row->GetNodesByAttribute("class", "button_update")->SetAttribute("disabled", "disabled");
						$Row->GetNodesByAttribute("class", "button_cancel")->SetAttribute("disabled", "disabled");
					}
				}
				
				//remove data-bind-id
				$Node->removeAttribute("data-bind-id");
			}
		//<-- End Method :: RowHandler
		
		//##################################################################################
		
		//--> Begin Method :: Action_Add
			public static function Action_Add() {
				//validate
				if(F::$Request->Input("fk_group_id") == "0" || F::$Request->Input("fk_group_id") == "") {
					F::$Errors->Add("You must select a group.");
				}
				if(F::$Request->Input("fk_group_id") != "") {
					F::$DB->LoadCommand("duplicate-group-check", F::$PageInput);
					if(F::$DB->GetDataString() != "") {
						F::$Errors->Add("This user has already been added to this group.");
					}
				}
				
				//take action
				if(F::$Errors->Count() == 0) {
					F::$DB->LoadCommand("add-to-group");
					F::$DB->SQLKey("#timestamp_created#", F::$DateTime->Now()->ToString());
					F::$DB->BindKeys(F::$PageInput);
					F::$DB->ExecuteNonQuery();
					
					F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$Request->Input("id"));
				}
			}
		//<-- End Method :: Action_Add
		
		//##################################################################################
		
		//--> Begin Method :: Action_Update
			public static function Action_Update() {
				if(F::$Request->Input("is_default") == "yes") {
					F::$DB->LoadCommand("remove-default-group", F::$PageInput);
					F::$DB->ExecuteNonQuery();
				}
				
				F::$DB->LoadCommand("set-default-group", F::$PageInput);
				F::$DB->ExecuteNonQuery();
				
				F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$Request->Input("id"));
			}
		//<-- End Method :: Action_Update
		
		//##################################################################################
		
		//--> Begin Method :: Action_Cancel
			public static function Action_Cancel() {
				//validate
				if(F::$Request->Input("id") == "1" && F::$Request->Input("fk_membership_id") == "1") {
					F::$Errors->Add("You cannot cancel the root user's membership from the root group.");
				}
				
				//take action
				if(F::$Errors->Count() == 0) {
					F::$DB->LoadCommand("cancel-membership");
					F::$DB->SQLKey("#timestamp_cancelled#", F::$DateTime->Now()->ToString());
					F::$DB->BindKeys(F::$PageInput);
					F::$DB->ExecuteNonQuery();
					
					F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$Request->Input("id"));
				}
			}
		//<-- End Method :: Action_Cancel
	}
//<-- End Class :: Page

//##########################################################################################
?>