<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: Action_AddUser
			public static function Action_AddUser() {
				//validate
				if(F::$Request->Input("fk_user_id") == "0" || F::$Request->Input("fk_user_id") == "") {
					F::$Errors->Add("You must select a user.");
				}
				else {
					F::$DB->LoadCommand("duplicate-permission-check", F::$PageInput);
					if(F::$DB->GetDataString() != "") {
						F::$Errors->Add("A permission has already been added for that user.");
					}
				}
				
				//take action
				if(F::$Errors->Count() == 0) {
					F::$DB->LoadCommand("add-user", F::$PageInput);
					F::$DB->ExecuteNonQuery();
					
					F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$Request->Input("id"));
				}
			}
		//<-- End Method :: Action_AddUser
		
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