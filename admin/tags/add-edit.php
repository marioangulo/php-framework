<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: Validate
			public static function Validate() {
				if(F::$Request->Input("fk_pivot_table") == "" || F::$Request->Input("fk_pivot_table") == "0") {
					F::$Errors->Add("fk_pivot_table", "select a pivot");
				}
				if(F::$Request->Input("name") == "") {
					F::$Errors->Add("name", "required");
				}
				else {
					F::$DB->LoadCommand("duplicate-name-check", F::$PageInput);
					if(F::$DB->GetDataString() != "") {
						F::$Errors->Add("name", "name not available");
					}
				}
			}
		//<-- End Method :: Validate
		
		//##################################################################################
		
		//--> Begin Method :: Action_CreateNew
			public static function Action_CreateNew() {
				//validate
				self::Validate();
				
				//take action
				if(F::$Errors->Count() == 0) {
					F::$DB->LoadCommand("create-new", F::$PageInput);
					F::$DB->ExecuteNonQuery();
					
					F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$DB->GetLastInsertID());
				}
			}
		//<-- End Method :: CreateNew
		
		//##################################################################################
		
		//--> Begin Method :: Action_Update
			public static function Action_Update() {
				//validate
				self::Validate();
				
				//take action
				if(F::$Errors->Count() == 0) {
					F::$DB->LoadCommand("update", F::$PageInput);
					F::$DB->ExecuteNonQuery();
					
					F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$Request->Input("id"));
				}
			}
		//<-- End Method :: Action_Update
		
		//##################################################################################
		
		//--> Begin Method :: Action_Delete
			public static function Action_Delete() {
				//take action
				if(F::$Errors->Count() == 0) {
					F::$DB->LoadCommand("delete", F::$PageInput);
					F::$DB->ExecuteNonQuery();
					
					F::$Response->RedirectURL = F::URL("admin/tags/index.html");
				}
			}
		//<-- End Method :: Action_Delete
	}
//<-- End Class :: Page

//##########################################################################################
?>